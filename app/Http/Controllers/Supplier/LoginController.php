<?php

namespace App\Http\Controllers\Supplier;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class LoginController
{
    public function show(): Response
    {
        return Inertia::render('Supplier/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'El correo es requerido',
            'email.email' => 'El correo debe ser válido',
            'password.required' => 'La contraseña es requerida',
        ]);

        $supplier = Supplier::where('email', $validated['email'])->first();

        if (! $supplier || ! Hash::check($validated['password'], $supplier->password_hash)) {
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }

        if (! $supplier->isActive()) {
            return back()->withErrors([
                'email' => 'Tu cuenta no está activa. Por favor completa tu onboarding.',
            ])->onlyInput('email');
        }

        Auth::guard('supplier')->login($supplier);

        return redirect()->route('supplier.dashboard')
            ->with('message', '¡Bienvenido de vuelta!');
    }
}
