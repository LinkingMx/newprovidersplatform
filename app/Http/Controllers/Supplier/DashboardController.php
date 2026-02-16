<?php

declare(strict_types=1);

namespace App\Http\Controllers\Supplier;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController
{
    public function __invoke(Request $request): Response
    {
        $supplier = $request->user('supplier')->load('branches');

        $documents = $supplier->documents()
            ->with(['documentType', 'documentState'])
            ->get()
            ->map(fn ($doc) => [
                'id' => $doc->id,
                'document_type' => [
                    'id' => $doc->documentType->id,
                    'nombre' => $doc->documentType->nombre,
                ],
                'document_state' => [
                    'id' => $doc->documentState->id,
                    'nombre' => $doc->documentState->nombre,
                    'etiqueta' => $doc->documentState->etiqueta,
                    'color' => $doc->documentState->color,
                ],
                'archivo_nombre' => $doc->archivo_nombre,
                'has_file' => $doc->archivo_path !== null,
                'can_upload' => $doc->canUpload(),
                'can_delete' => $doc->canDelete(),
                'notas' => $doc->notas,
                'uploaded_at' => $doc->uploaded_at?->toISOString(),
            ]);

        return Inertia::render('Supplier/Dashboard', [
            'supplier' => $supplier,
            'documents' => $documents,
        ]);
    }
}
