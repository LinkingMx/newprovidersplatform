<?php

use App\Models\Supplier;
use Illuminate\Support\Facades\Hash;

describe('ResetPasswordController → show', function () {
    it('renders the reset password page with valid token and email', function () {
        $supplier = Supplier::factory()->active()->create();
        $supplier->update([
            'password_reset_token' => hash('sha256', 'valid-token'),
            'password_reset_expires_at' => now()->addMinutes(30),
        ]);

        $response = $this->get(route('supplier.reset-password', [
            'token' => 'valid-token',
            'email' => $supplier->email,
        ]));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Supplier/ResetPassword')
            ->where('token', 'valid-token')
            ->where('email', $supplier->email)
            ->where('error', null)
        );
    });

    it('shows error when token is missing', function () {
        $response = $this->get(route('supplier.reset-password'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Supplier/ResetPassword')
            ->where('token', null)
            ->where('error', fn ($error) => str_contains($error, 'Enlace inválido'))
        );
    });

    it('shows error when token is invalid', function () {
        $supplier = Supplier::factory()->active()->create();

        $response = $this->get(route('supplier.reset-password', [
            'token' => 'wrong-token',
            'email' => $supplier->email,
        ]));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Supplier/ResetPassword')
            ->where('token', null)
            ->where('error', fn ($error) => str_contains($error, 'inválido o expirado'))
        );
    });

    it('shows error when token is expired', function () {
        $supplier = Supplier::factory()->active()->create();
        $supplier->update([
            'password_reset_token' => hash('sha256', 'expired-token'),
            'password_reset_expires_at' => now()->subMinutes(1),
        ]);

        $response = $this->get(route('supplier.reset-password', [
            'token' => 'expired-token',
            'email' => $supplier->email,
        ]));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Supplier/ResetPassword')
            ->where('token', null)
            ->where('error', fn ($error) => str_contains($error, 'expirado'))
        );
    });
});

describe('ResetPasswordController → reset', function () {
    it('resets the password with valid token', function () {
        $supplier = Supplier::factory()->active()->create();
        $supplier->update([
            'password_reset_token' => hash('sha256', 'valid-token'),
            'password_reset_expires_at' => now()->addMinutes(30),
        ]);

        $response = $this->post(route('supplier.reset-password.store'), [
            'token' => 'valid-token',
            'email' => $supplier->email,
            'password' => 'NewP@ssw0rd!',
            'password_confirmation' => 'NewP@ssw0rd!',
        ]);

        $response->assertRedirect(route('supplier.login'));
        $response->assertSessionHas('status');

        $supplier->refresh();
        expect(Hash::check('NewP@ssw0rd!', $supplier->password_hash))->toBeTrue();
        expect($supplier->password_reset_token)->toBeNull();
        expect($supplier->password_reset_expires_at)->toBeNull();
    });

    it('rejects invalid token', function () {
        $supplier = Supplier::factory()->active()->create();

        $response = $this->post(route('supplier.reset-password.store'), [
            'token' => 'wrong-token',
            'email' => $supplier->email,
            'password' => 'NewP@ssw0rd!',
            'password_confirmation' => 'NewP@ssw0rd!',
        ]);

        $response->assertSessionHasErrors('token');
    });

    it('rejects expired token', function () {
        $supplier = Supplier::factory()->active()->create();
        $supplier->update([
            'password_reset_token' => hash('sha256', 'expired-token'),
            'password_reset_expires_at' => now()->subMinutes(1),
        ]);

        $response = $this->post(route('supplier.reset-password.store'), [
            'token' => 'expired-token',
            'email' => $supplier->email,
            'password' => 'NewP@ssw0rd!',
            'password_confirmation' => 'NewP@ssw0rd!',
        ]);

        $response->assertSessionHasErrors('token');
    });

    it('requires password confirmation', function () {
        $supplier = Supplier::factory()->active()->create();
        $supplier->update([
            'password_reset_token' => hash('sha256', 'valid-token'),
            'password_reset_expires_at' => now()->addMinutes(30),
        ]);

        $response = $this->post(route('supplier.reset-password.store'), [
            'token' => 'valid-token',
            'email' => $supplier->email,
            'password' => 'NewP@ssw0rd!',
            'password_confirmation' => 'DifferentP@ss1!',
        ]);

        $response->assertSessionHasErrors('password');
    });

    it('requires minimum 10 characters', function () {
        $supplier = Supplier::factory()->active()->create();
        $supplier->update([
            'password_reset_token' => hash('sha256', 'valid-token'),
            'password_reset_expires_at' => now()->addMinutes(30),
        ]);

        $response = $this->post(route('supplier.reset-password.store'), [
            'token' => 'valid-token',
            'email' => $supplier->email,
            'password' => 'Short1!',
            'password_confirmation' => 'Short1!',
        ]);

        $response->assertSessionHasErrors('password');
    });

    it('requires uppercase letters', function () {
        $supplier = Supplier::factory()->active()->create();
        $supplier->update([
            'password_reset_token' => hash('sha256', 'valid-token'),
            'password_reset_expires_at' => now()->addMinutes(30),
        ]);

        $response = $this->post(route('supplier.reset-password.store'), [
            'token' => 'valid-token',
            'email' => $supplier->email,
            'password' => 'nouppercase1!nouppercase',
            'password_confirmation' => 'nouppercase1!nouppercase',
        ]);

        $response->assertSessionHasErrors('password');
    });

    it('requires numbers', function () {
        $supplier = Supplier::factory()->active()->create();
        $supplier->update([
            'password_reset_token' => hash('sha256', 'valid-token'),
            'password_reset_expires_at' => now()->addMinutes(30),
        ]);

        $response = $this->post(route('supplier.reset-password.store'), [
            'token' => 'valid-token',
            'email' => $supplier->email,
            'password' => 'NoNumbersHere!!',
            'password_confirmation' => 'NoNumbersHere!!',
        ]);

        $response->assertSessionHasErrors('password');
    });

    it('requires special characters', function () {
        $supplier = Supplier::factory()->active()->create();
        $supplier->update([
            'password_reset_token' => hash('sha256', 'valid-token'),
            'password_reset_expires_at' => now()->addMinutes(30),
        ]);

        $response = $this->post(route('supplier.reset-password.store'), [
            'token' => 'valid-token',
            'email' => $supplier->email,
            'password' => 'NoSpecials123AB',
            'password_confirmation' => 'NoSpecials123AB',
        ]);

        $response->assertSessionHasErrors('password');
    });
});
