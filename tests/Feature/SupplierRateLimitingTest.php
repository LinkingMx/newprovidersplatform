<?php

use App\Models\Supplier;

describe('Supplier Login Rate Limiting', function () {
    it('allows up to 5 login attempts per minute', function () {
        $supplier = Supplier::factory()->create([
            'password_hash' => bcrypt('password'),
            'status' => 'active',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('supplier.login.store'), [
                'email' => $supplier->email,
                'password' => 'wrong-password',
            ])->assertRedirect();
        }

        $this->post(route('supplier.login.store'), [
            'email' => $supplier->email,
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    });

    it('returns 429 with Retry-After header when rate limited', function () {
        $supplier = Supplier::factory()->create([
            'password_hash' => bcrypt('password'),
            'status' => 'active',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('supplier.login.store'), [
                'email' => $supplier->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('supplier.login.store'), [
            'email' => $supplier->email,
            'password' => 'wrong-password',
        ]);

        $response->assertTooManyRequests();
        $response->assertHeader('Retry-After');
    });
});

describe('Supplier Forgot Password Rate Limiting', function () {
    it('allows up to 3 forgot password requests per minute', function () {
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('supplier.forgot-password.store'), [
                'email' => 'test@example.com',
            ])->assertRedirect();
        }

        $this->post(route('supplier.forgot-password.store'), [
            'email' => 'test@example.com',
        ])->assertTooManyRequests();
    });
});
