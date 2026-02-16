<?php

declare(strict_types=1);

namespace App\Http\Controllers\Supplier;

use App\Http\Requests\Supplier\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController
{
    public function edit(Request $request): Response
    {
        $supplier = $request->user('supplier');

        return Inertia::render('Supplier/EditProfile', [
            'supplier' => [
                'name' => $supplier->name,
                'email' => $supplier->email,
                'address_street' => $supplier->address_street,
                'address_number' => $supplier->address_number,
                'address_neighborhood' => $supplier->address_neighborhood,
                'address_city' => $supplier->address_city,
                'address_country' => $supplier->address_country ?? 'Mexico',
                'address_zip' => $supplier->address_zip,
                'clabe_interbancaria' => $supplier->clabe_interbancaria,
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $supplier = $request->user('supplier');

        $supplier->update($request->validated());

        return redirect()->route('dashboard')
            ->with('success', 'Tu información ha sido actualizada.');
    }
}
