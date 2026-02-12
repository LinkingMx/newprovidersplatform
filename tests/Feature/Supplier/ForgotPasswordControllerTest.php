<?php

use App\Mail\SupplierPasswordResetMailable;
use App\Models\Supplier;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

describe('ForgotPasswordController → show', function () {
    it('renders the forgot password page', function () {
        $response = $this->get(route('supplier.forgot-password'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page->component('Supplier/ForgotPassword'));
    });

    it('redirects authenticated suppliers away', function () {
        $supplier = Supplier::factory()->active()->create();
        $this->actingAs($supplier, 'supplier');

        $response = $this->get(route('supplier.forgot-password'));

        $response->assertRedirect();
    });
});

describe('ForgotPasswordController → sendResetLink', function () {
    it('sends a reset email for a valid supplier', function () {
        $supplier = Supplier::factory()->active()->create();

        $response = $this->post(route('supplier.forgot-password.store'), [
            'email' => $supplier->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        Mail::assertSent(SupplierPasswordResetMailable::class, function ($mail) use ($supplier) {
            return $mail->hasTo($supplier->email);
        });

        $supplier->refresh();
        expect($supplier->password_reset_token)->not->toBeNull();
        expect($supplier->password_reset_expires_at)->not->toBeNull();
    });

    it('returns success even for non-existent emails to prevent enumeration', function () {
        $response = $this->post(route('supplier.forgot-password.store'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        Mail::assertNothingSent();
    });

    it('validates email is required', function () {
        $response = $this->post(route('supplier.forgot-password.store'), [
            'email' => '',
        ]);

        $response->assertSessionHasErrors('email');
    });

    it('validates email format', function () {
        $response = $this->post(route('supplier.forgot-password.store'), [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    });

    it('throttles reset requests within 60 seconds', function () {
        $supplier = Supplier::factory()->active()->create();
        $supplier->update([
            'password_reset_token' => 'existing-token',
            'password_reset_expires_at' => now()->addMinutes(60),
        ]);

        $response = $this->post(route('supplier.forgot-password.store'), [
            'email' => $supplier->email,
        ]);

        $response->assertRedirect();

        // Should not send a new email (throttled)
        Mail::assertNothingSent();

        // Token should remain unchanged
        $supplier->refresh();
        expect($supplier->password_reset_token)->toBe('existing-token');
    });

    it('allows reset request after throttle period', function () {
        $supplier = Supplier::factory()->active()->create();
        $supplier->update([
            'password_reset_token' => 'old-token',
            'password_reset_expires_at' => now()->addMinutes(58),
        ]);

        $response = $this->post(route('supplier.forgot-password.store'), [
            'email' => $supplier->email,
        ]);

        $response->assertRedirect();

        Mail::assertSent(SupplierPasswordResetMailable::class);

        $supplier->refresh();
        expect($supplier->password_reset_token)->not->toBe('old-token');
    });
});
