<?php

namespace App\Http\Controllers\Supplier;

use App\Mail\SupplierPasswordResetMailable;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ForgotPasswordController
{
    public function show(): Response
    {
        return Inertia::render('Supplier/ForgotPassword');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'El correo electrónico es requerido',
            'email.email' => 'Ingresa un correo electrónico válido',
        ]);

        $supplier = Supplier::where('email', $request->email)->first();

        // Always return success to prevent email enumeration
        if (! $supplier) {
            return back()->with('status', 'Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.');
        }

        // Throttle: if token expires in more than 59 minutes, it was created less than 60 seconds ago
        if ($supplier->password_reset_token && $supplier->password_reset_expires_at) {
            if ($supplier->password_reset_expires_at->greaterThan(now()->addMinutes(59))) {
                return back()->with('status', 'Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.');
            }
        }

        // Generate token and save
        $token = Str::random(64);
        $supplier->update([
            'password_reset_token' => $token,
            'password_reset_expires_at' => now()->addMinutes(60),
        ]);

        Mail::to($supplier->email)->send(new SupplierPasswordResetMailable($supplier, $token));

        return back()->with('status', 'Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.');
    }
}
