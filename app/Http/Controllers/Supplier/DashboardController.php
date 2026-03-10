<?php

declare(strict_types=1);

namespace App\Http\Controllers\Supplier;

use App\Enums\BranchRequestStatus;
use App\Models\Branch;
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

        $assignedBranchIds = $supplier->branches->pluck('id');
        $pendingRequestBranchIds = $supplier->branchRequests()
            ->where('status', BranchRequestStatus::Pending)
            ->pluck('branch_id');

        $excludedIds = $assignedBranchIds->merge($pendingRequestBranchIds)->unique();

        $availableBranches = Branch::whereNotIn('id', $excludedIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $branchRequests = $supplier->branchRequests()
            ->with('branch:id,name')
            ->latest('requested_at')
            ->get()
            ->map(fn ($br) => [
                'id' => $br->id,
                'branch' => [
                    'id' => $br->branch->id,
                    'name' => $br->branch->name,
                ],
                'status' => $br->status->value,
                'status_label' => $br->status->label(),
                'status_color' => $br->status->color(),
                'notas_proveedor' => $br->notas_proveedor,
                'notas_admin' => $br->notas_admin,
                'requested_at' => $br->requested_at?->toISOString(),
                'resolved_at' => $br->resolved_at?->toISOString(),
            ]);

        return Inertia::render('Supplier/Dashboard', [
            'supplier' => $supplier,
            'documents' => $documents,
            'availableBranches' => $availableBranches,
            'branchRequests' => $branchRequests,
        ]);
    }
}
