<?php

use App\Models\Supplier;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect('/login');
});

test('authenticated suppliers can visit the dashboard', function () {
    $supplier = Supplier::factory()->active()->create();
    $this->actingAs($supplier, 'supplier');

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});