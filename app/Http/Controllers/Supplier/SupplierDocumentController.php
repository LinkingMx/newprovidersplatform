<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Requests\Supplier\SupplierDocumentUploadRequest;
use App\Models\DocumentState;
use App\Models\SupplierDocument;
use App\Policies\SupplierDocumentPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierDocumentController
{
    private function disk(): string
    {
        return config('filesystems.supplier_documents_disk', 's3');
    }

    public function upload(SupplierDocumentUploadRequest $request, SupplierDocument $supplierDocument): RedirectResponse
    {
        $file = $request->file('archivo');
        $supplier = auth('supplier')->user();

        // Delete previous file if re-uploading (rejected document)
        if ($supplierDocument->archivo_path && Storage::disk($this->disk())->exists($supplierDocument->archivo_path)) {
            Storage::disk($this->disk())->delete($supplierDocument->archivo_path);
        }

        // Store file privately
        $path = $file->store("supplier-documents/{$supplier->id}", $this->disk());

        // Set state to "En Revisión" so admin can review the uploaded file
        $supplierDocument->update([
            'archivo_path' => $path,
            'archivo_nombre' => $file->getClientOriginalName(),
            'uploaded_at' => now(),
            'document_state_id' => DocumentState::EN_REVISION,
            'notas' => null,
        ]);

        return redirect()->route('dashboard')
            ->with('message', 'Documento subido correctamente.');
    }

    public function preview(SupplierDocument $supplierDocument): StreamedResponse|RedirectResponse
    {
        $supplier = auth('supplier')->user();

        if (! $supplier || ! app(SupplierDocumentPolicy::class)->download($supplier, $supplierDocument)) {
            abort(403);
        }

        if (
            ! $supplierDocument->archivo_path
            || str_contains($supplierDocument->archivo_path, '..')
            || ! str_starts_with($supplierDocument->archivo_path, 'supplier-documents/')
        ) {
            abort(403);
        }

        if (! Storage::disk($this->disk())->exists($supplierDocument->archivo_path)) {
            return redirect()->route('dashboard')
                ->with('error', 'El archivo no se encontró.');
        }

        $mimeType = Storage::disk($this->disk())->mimeType($supplierDocument->archivo_path);

        return Storage::disk($this->disk())->response(
            $supplierDocument->archivo_path,
            $supplierDocument->archivo_nombre,
            ['Content-Type' => $mimeType]
        );
    }

    public function download(SupplierDocument $supplierDocument): StreamedResponse|RedirectResponse
    {
        $supplier = auth('supplier')->user();

        if (! $supplier || ! app(SupplierDocumentPolicy::class)->download($supplier, $supplierDocument)) {
            abort(403);
        }

        // Validate path is safe (no traversal) and within expected directory
        if (
            ! $supplierDocument->archivo_path
            || str_contains($supplierDocument->archivo_path, '..')
            || ! str_starts_with($supplierDocument->archivo_path, 'supplier-documents/')
        ) {
            abort(403);
        }

        // Check file exists
        if (! Storage::disk($this->disk())->exists($supplierDocument->archivo_path)) {
            return redirect()->route('dashboard')
                ->with('error', 'El archivo no se encontró.');
        }

        return Storage::disk($this->disk())->download(
            $supplierDocument->archivo_path,
            $supplierDocument->archivo_nombre
        );
    }

    public function destroy(SupplierDocument $supplierDocument): RedirectResponse
    {
        $supplier = auth('supplier')->user();

        if (! $supplier || ! app(SupplierDocumentPolicy::class)->deleteFile($supplier, $supplierDocument)) {
            abort(403);
        }

        // Delete file from disk
        if ($supplierDocument->archivo_path && Storage::disk($this->disk())->exists($supplierDocument->archivo_path)) {
            Storage::disk($this->disk())->delete($supplierDocument->archivo_path);
        }

        // Reset to Pendiente so supplier can re-upload
        $supplierDocument->update([
            'archivo_path' => null,
            'archivo_nombre' => null,
            'uploaded_at' => null,
            'document_state_id' => DocumentState::PENDIENTE,
            'notas' => null,
        ]);

        return redirect()->route('dashboard')
            ->with('message', 'Documento eliminado. Puedes subir uno nuevo.');
    }
}
