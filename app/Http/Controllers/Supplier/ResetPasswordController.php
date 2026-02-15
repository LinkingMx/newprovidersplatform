<?php

namespace App\Http\Controllers\Supplier;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ResetPasswordController
{
    public function show(Request $request): Response|RedirectResponse
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (! $token || ! $email) {
            return Inertia::render('Supplier/ResetPassword', [
                'token' => null,
                'email' => null,
                'error' => 'Enlace inválido. Solicita un nuevo enlace de restablecimiento.',
            ]);
        }

        $supplier = Supplier::where('email', $email)
            ->where('password_reset_token', hash('sha256', $token))
            ->first();

        if (! $supplier) {
            return Inertia::render('Supplier/ResetPassword', [
                'token' => null,
                'email' => null,
                'error' => 'Enlace inválido o expirado. Solicita un nuevo enlace de restablecimiento.',
            ]);
        }

        if ($supplier->password_reset_expires_at && $supplier->password_reset_expires_at->isPast()) {
            return Inertia::render('Supplier/ResetPassword', [
                'token' => null,
                'email' => null,
                'error' => 'El enlace ha expirado. Solicita un nuevo enlace de restablecimiento.',
            ]);
        }

        return Inertia::render('Supplier/ResetPassword', [
            'token' => $token,
            'email' => $email,
            'error' => null,
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:10|confirmed|regex:/[A-Z]/|regex:/[0-9]/|regex:/[!@#$%^&*]/',
        ], [
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 10 caracteres',
            'password.regex' => 'La contraseña debe contener mayúsculas, números y símbolos especiales',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ]);

        $supplier = Supplier::where('email', $validated['email'])
            ->where('password_reset_token', hash('sha256', $validated['token']))
            ->first();

        if (! $supplier) {
            return back()->withErrors(['token' => 'Enlace inválido o expirado']);
        }

        if ($supplier->password_reset_expires_at && $supplier->password_reset_expires_at->isPast()) {
            return back()->withErrors(['token' => 'El enlace ha expirado. Solicita uno nuevo.']);
        }

        $supplier->update([
            'password_hash' => Hash::make($validated['password']),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);

        return redirect()->route('supplier.login')
            ->with('status', 'Tu contraseña ha sido restablecida. Ahora puedes iniciar sesión.');
    }
}
