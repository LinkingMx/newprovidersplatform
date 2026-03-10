<?php

declare(strict_types=1);

namespace App\Http\Controllers\Supplier;

use App\Http\Requests\Supplier\StoreBranchRequestRequest;
use Illuminate\Http\RedirectResponse;

class BranchRequestController
{
    public function store(StoreBranchRequestRequest $request): RedirectResponse
    {
        $request->user('supplier')->branchRequests()->create([
            'branch_id' => $request->validated('branch_id'),
            'notas_proveedor' => $request->validated('notas_proveedor'),
        ]);

        return back()->with('success', 'Solicitud enviada exitosamente.');
    }
}
