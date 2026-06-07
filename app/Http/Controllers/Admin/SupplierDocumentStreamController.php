<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\SupplierDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Stream supplier-document files for admin preview when the configured disk is
 * local (no temporaryUrl support). For S3/cloud disks we use signed URLs
 * directly in the Blade and this controller is bypassed.
 */
class SupplierDocumentStreamController
{
    public function __invoke(SupplierDocument $supplierDocument): StreamedResponse|RedirectResponse
    {
        abort_unless(auth('web')->check(), 403);

        if (
            ! $supplierDocument->archivo_path
            || str_contains($supplierDocument->archivo_path, '..')
            || ! str_starts_with($supplierDocument->archivo_path, 'supplier-documents/')
        ) {
            abort(403);
        }

        $disk = config('filesystems.supplier_documents_disk', 's3');

        if (! Storage::disk($disk)->exists($supplierDocument->archivo_path)) {
            abort(404, 'Archivo no encontrado.');
        }

        $mimeType = Storage::disk($disk)->mimeType($supplierDocument->archivo_path) ?: 'application/octet-stream';

        return Storage::disk($disk)->response(
            $supplierDocument->archivo_path,
            $supplierDocument->archivo_nombre,
            ['Content-Type' => $mimeType]
        );
    }
}
