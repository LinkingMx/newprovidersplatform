<?php

namespace App\Http\Controllers\Supplier;

use App\Models\Supplier;
use App\Models\SupplierInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SetPasswordController
{
    public function show(Request $request): Response|RedirectResponse
    {
        $token = $request->query('token');

        if (! $token) {
            return Inertia::render('Supplier/SetPassword', [
                'error' => 'Token no proporcionado',
                'token' => null,
            ]);
        }

        $invitation = SupplierInvitation::where('token', $token)->first();

        if (! $invitation) {
            return Inertia::render('Supplier/SetPassword', [
                'error' => 'Token inválido',
                'token' => null,
            ]);
        }

        if ($invitation->isExpired()) {
            return Inertia::render('Supplier/SetPassword', [
                'error' => 'Token expirado. Solicita una nueva invitación',
                'token' => null,
            ]);
        }

        if ($invitation->supplier->isActive()) {
            return redirect()->route('dashboard')
                ->with('message', 'Tu cuenta ya está activa');
        }

        return Inertia::render('Supplier/SetPassword', [
            'token' => $token,
            'error' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:10|confirmed|regex:/[A-Z]/|regex:/[0-9]/|regex:/[!@#$%^&*]/|confirmed',
        ], [
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 10 caracteres',
            'password.regex' => 'La contraseña debe contener mayúsculas, números y símbolos especiales',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ]);

        $invitation = SupplierInvitation::where('token', $validated['token'])->first();

        if (! $invitation) {
            return back()->withErrors(['token' => 'Token inválido']);
        }

        if ($invitation->isExpired()) {
            return back()->withErrors(['token' => 'Token expirado']);
        }

        $supplier = $invitation->supplier;

        if ($supplier->isActive()) {
            return redirect()->route('dashboard');
        }

        // Update supplier password and status
        $supplier->update([
            'password_hash' => Hash::make($validated['password']),
            'status' => 'registered',
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);

        // Mark invitation as accepted
        $invitation->update(['accepted_at' => now()]);

        // Log in the supplier
        Auth::guard('supplier')->login($supplier);
        $request->session()->regenerate();

        // Redirect to onboarding if not completed, else to dashboard
        if ($supplier->status === 'registered') {
            return redirect()->route('supplier.onboarding')
                ->with('message', 'Contraseña establecida. Completa tu perfil.');
        }

        return redirect()->route('dashboard')
            ->with('message', 'Bienvenido de vuelta!');
    }
}
