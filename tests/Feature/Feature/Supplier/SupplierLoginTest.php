<?php

use App\Models\Supplier;

test('supplier login page shows for guests', function () {
    $response = $this->get('/supplier/login');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Supplier/Login')
    );
});

test('supplier can login with valid credentials', function () {
    $supplier = Supplier::factory()->active()->create([
        'email' => 'supplier@test.com',
        'password_hash' => bcrypt('ValidPass123!'),
    ]);

    $response = $this->post('/supplier/login', [
        'email' => 'supplier@test.com',
        'password' => 'ValidPass123!',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($supplier, 'supplier');
});

test('supplier cannot login with invalid password', function () {
    Supplier::factory()->active()->create([
        'email' => 'supplier@test.com',
        'password_hash' => bcrypt('ValidPass123!'),
    ]);

    $response = $this->post('/supplier/login', [
        'email' => 'supplier@test.com',
        'password' => 'WrongPassword123!',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest('supplier');
});

test('supplier cannot login with nonexistent email', function () {
    $response = $this->post('/supplier/login', [
        'email' => 'nonexistent@test.com',
        'password' => 'ValidPass123!',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest('supplier');
});

test('inactive supplier cannot login', function () {
    Supplier::factory()->create([
        'email' => 'inactive@test.com',
        'password_hash' => bcrypt('ValidPass123!'),
    ]);

    $response = $this->post('/supplier/login', [
        'email' => 'inactive@test.com',
        'password' => 'ValidPass123!',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest('supplier');
});

test('authenticated supplier is redirected from login page', function () {
    $supplier = Supplier::factory()->active()->create();

    $response = $this->actingAs($supplier, 'supplier')
        ->get('/supplier/login');

    $response->assertRedirect(route('dashboard'));
});

test('supplier can logout', function () {
    $supplier = Supplier::factory()->active()->create();

    $response = $this->actingAs($supplier, 'supplier')
        ->post(route('supplier.logout'));

    $response->assertRedirect('/');
    $this->assertGuest('supplier');
});
