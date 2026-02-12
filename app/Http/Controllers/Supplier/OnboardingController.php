<?php

namespace App\Http\Controllers\Supplier;

use App\Jobs\SupplierVerificationJob;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController
{
    public function show(Request $request): Response
    {
        $supplier = auth('supplier')->user();

        if (! $supplier) {
            return redirect()->route('supplier.set-password')
                ->with('error', 'Debes establecer tu contraseña primero');
        }

        if ($supplier->isActive()) {
            return redirect()->route('supplier.dashboard')
                ->with('message', 'Tu perfil ya está completado');
        }

        return Inertia::render('Supplier/Onboarding', [
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

    public function submit(Request $request)
    {
        $supplier = auth('supplier')->user();

        if (! $supplier) {
            return redirect()->route('supplier.set-password');
        }

        $validated = $request->validate([
            'address_street' => 'required|string|max:255',
            'address_number' => 'required|string|max:50',
            'address_neighborhood' => 'required|string|max:255',
            'address_city' => 'required|string|max:255',
            'address_country' => 'required|string|in:Mexico,USA,Canada',
            'address_zip' => 'required|string|regex:/^\d{5}$/',
            'clabe_interbancaria' => 'required|string|regex:/^\d{18}$/|unique:suppliers,clabe_interbancaria',
            'confirm' => 'required|accepted',
        ], [
            'address_street.required' => 'La calle es requerida',
            'address_number.required' => 'El número es requerido',
            'address_neighborhood.required' => 'El barrio es requerido',
            'address_city.required' => 'La ciudad es requerida',
            'address_country.required' => 'El país es requerido',
            'address_zip.regex' => 'El código postal debe ser 5 dígitos',
            'clabe_interbancaria.required' => 'La CLABE es requerida',
            'clabe_interbancaria.regex' => 'La CLABE debe ser exactamente 18 dígitos',
            'clabe_interbancaria.unique' => 'Esta CLABE ya está registrada',
            'confirm.accepted' => 'Debes confirmar que la información es correcta',
        ]);

        // Update supplier with profile data
        $supplier->update([
            'address_street' => $validated['address_street'],
            'address_number' => $validated['address_number'],
            'address_neighborhood' => $validated['address_neighborhood'],
            'address_city' => $validated['address_city'],
            'address_country' => $validated['address_country'],
            'address_zip' => $validated['address_zip'],
            'clabe_interbancaria' => $validated['clabe_interbancaria'],
            'status' => 'profile_completed',
        ]);

        // Enqueue verification job
        dispatch(new SupplierVerificationJob($supplier));

        return redirect()->route('supplier.dashboard')
            ->with('message', 'Perfil completado. Estamos verificando tu información.');
    }
}
