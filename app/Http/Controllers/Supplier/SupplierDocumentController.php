<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Requests\Supplier\SupplierDocumentUploadRequest;
use App\Models\SupplierDocument;
use App\Policies\SupplierDocumentPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierDocumentController
{
    public function upload(SupplierDocumentUploadRequest $request, SupplierDocument $supplierDocument): RedirectResponse
    {
        $file = $request->file('archivo');
        $supplier = auth('supplier')->user();

        // Delete previous file if re-uploading (rejected document)
        if ($supplierDocument->archivo_path && Storage::disk('local')->exists($supplierDocument->archivo_path)) {
            Storage::disk('local')->delete($supplierDocument->archivo_path);
        }

        // Store file privately
        $path = $file->store("supplier-documents/{$supplier->id}", 'local');

        // Reset state to "Pendiente" (ID 1) and update file info
        $supplierDocument->update([
            'archivo_path' => $path,
            'archivo_nombre' => $file->getClientOriginalName(),
            'uploaded_at' => now(),
            'document_state_id' => 1,
            'notas' => null,
        ]);

        return redirect()->route('dashboard')
            ->with('message', 'Documento subido correctamente.');
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
        if (! Storage::disk('local')->exists($supplierDocument->archivo_path)) {
            return redirect()->route('dashboard')
                ->with('error', 'El archivo no se encontró.');
        }

        return Storage::disk('local')->download(
            $supplierDocument->archivo_path,
            $supplierDocument->archivo_nombre
        );
    }
}
