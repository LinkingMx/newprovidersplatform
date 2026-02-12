<?php

use App\Models\Supplier;
use App\Models\SupplierInvitation;

describe('SetPasswordController', function () {
    describe('store', function () {
        it('successfully sets password with valid token and password', function () {
            $supplier = Supplier::factory()->invited()->create();
            $invitation = SupplierInvitation::factory()->for($supplier)->create();

            $response = $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'SecurePassword123!',
                'password_confirmation' => 'SecurePassword123!',
            ]);

            $response->assertRedirect();

            $supplier->refresh();
            expect($supplier->status)->toBe('registered');
            expect($supplier->password_hash)->not->toBeNull();
            expect(password_verify('SecurePassword123!', $supplier->password_hash))->toBeTrue();
        });

        it('logs in supplier after password is set', function () {
            $supplier = Supplier::factory()->invited()->create();
            $invitation = SupplierInvitation::factory()->for($supplier)->create();

            $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'SecurePassword123!',
                'password_confirmation' => 'SecurePassword123!',
            ]);

            $this->assertAuthenticated('supplier');
            expect(auth('supplier')->user()->id)->toBe($supplier->id);
        });

        it('rejects password shorter than 10 characters', function () {
            $supplier = Supplier::factory()->invited()->create();
            $invitation = SupplierInvitation::factory()->for($supplier)->create();

            $response = $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'Short1!',
                'password_confirmation' => 'Short1!',
            ]);

            $response->assertInvalid('password');
            $supplier->refresh();
            expect($supplier->password_hash)->toBeNull();
        });

        it('rejects password without uppercase character', function () {
            $supplier = Supplier::factory()->invited()->create();
            $invitation = SupplierInvitation::factory()->for($supplier)->create();

            $response = $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'lowercase123!',
                'password_confirmation' => 'lowercase123!',
            ]);

            $response->assertInvalid('password');
        });

        it('rejects password without number', function () {
            $supplier = Supplier::factory()->invited()->create();
            $invitation = SupplierInvitation::factory()->for($supplier)->create();

            $response = $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'NoNumbers!Abc',
                'password_confirmation' => 'NoNumbers!Abc',
            ]);

            $response->assertInvalid('password');
        });

        it('rejects password without special character', function () {
            $supplier = Supplier::factory()->invited()->create();
            $invitation = SupplierInvitation::factory()->for($supplier)->create();

            $response = $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'NoSpecial123Abc',
                'password_confirmation' => 'NoSpecial123Abc',
            ]);

            $response->assertInvalid('password');
        });

        it('rejects mismatched passwords', function () {
            $supplier = Supplier::factory()->invited()->create();
            $invitation = SupplierInvitation::factory()->for($supplier)->create();

            $response = $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'SecurePassword123!',
                'password_confirmation' => 'DifferentPassword123!',
            ]);

            $response->assertInvalid('password');
        });

        it('rejects expired token', function () {
            $supplier = Supplier::factory()->invited()->create();
            $invitation = SupplierInvitation::factory()
                ->for($supplier)
                ->expired()
                ->create();

            $response = $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'SecurePassword123!',
                'password_confirmation' => 'SecurePassword123!',
            ]);

            $response->assertRedirect();
        });

        it('rejects invalid token', function () {
            $response = $this->post('/supplier/auth/set-password', [
                'token' => 'invalid-token',
                'password' => 'SecurePassword123!',
                'password_confirmation' => 'SecurePassword123!',
            ]);

            $response->assertRedirect();
        });

        it('requires token, password and password_confirmation', function () {
            $response = $this->post('/supplier/auth/set-password', []);

            $response->assertInvalid(['token', 'password']);
        });

        it('accepts password with multiple special characters', function () {
            $supplier = Supplier::factory()->invited()->create();
            $invitation = SupplierInvitation::factory()->for($supplier)->create();

            $response = $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'Complex!@#Password123',
                'password_confirmation' => 'Complex!@#Password123',
            ]);

            $response->assertRedirect();
        });

        it('marks token as accepted after successful password set', function () {
            $supplier = Supplier::factory()->invited()->create();
            $invitation = SupplierInvitation::factory()->for($supplier)->create();

            $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'SecurePassword123!',
                'password_confirmation' => 'SecurePassword123!',
            ]);

            $invitation->refresh();
            expect($invitation->accepted_at)->not->toBeNull();
        });
    });
});
