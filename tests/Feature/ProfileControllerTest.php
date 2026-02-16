<?php

use App\Models\Supplier;

it('shows edit profile page for active supplier', function () {
    $supplier = Supplier::factory()->active()->create([
        'address_street' => 'Reforma',
        'address_number' => '505',
        'address_neighborhood' => 'Cuauhtémoc',
        'address_city' => 'CDMX',
        'address_country' => 'Mexico',
        'address_zip' => '06500',
        'clabe_interbancaria' => '012345678901234567',
    ]);

    $this->actingAs($supplier, 'supplier')
        ->get('/supplier/profile/edit')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Supplier/EditProfile')
            ->has('supplier')
            ->where('supplier.address_street', 'Reforma')
            ->where('supplier.address_number', '505')
            ->where('supplier.address_neighborhood', 'Cuauhtémoc')
        );
});

it('shows edit profile page for profile_completed supplier', function () {
    $supplier = Supplier::factory()->profileCompleted()->create();

    $this->actingAs($supplier, 'supplier')
        ->get('/supplier/profile/edit')
        ->assertSuccessful();
});

it('requires authentication to access edit profile', function () {
    $this->get('/supplier/profile/edit')
        ->assertRedirect();
});

it('updates supplier profile with valid data', function () {
    $supplier = Supplier::factory()->active()->create([
        'clabe_interbancaria' => '012345678901234567',
    ]);

    $this->actingAs($supplier, 'supplier')
        ->put('/supplier/profile', [
            'address_street' => 'Av. Insurgentes',
            'address_number' => '1000',
            'address_neighborhood' => 'Del Valle',
            'address_city' => 'Ciudad de México',
            'address_country' => 'Mexico',
            'address_zip' => '03100',
            'clabe_interbancaria' => '987654321098765432',
        ])
        ->assertRedirect('/dashboard');

    $supplier->refresh();
    expect($supplier->address_street)->toBe('Av. Insurgentes')
        ->and($supplier->address_number)->toBe('1000')
        ->and($supplier->address_neighborhood)->toBe('Del Valle')
        ->and($supplier->address_city)->toBe('Ciudad de México')
        ->and($supplier->address_zip)->toBe('03100')
        ->and($supplier->clabe_interbancaria)->toBe('987654321098765432');
});

it('does not change supplier status on profile update', function () {
    $supplier = Supplier::factory()->active()->create([
        'clabe_interbancaria' => '012345678901234567',
    ]);

    $this->actingAs($supplier, 'supplier')
        ->put('/supplier/profile', [
            'address_street' => 'Av. Insurgentes',
            'address_number' => '1000',
            'address_neighborhood' => 'Del Valle',
            'address_city' => 'Ciudad de México',
            'address_country' => 'Mexico',
            'address_zip' => '03100',
            'clabe_interbancaria' => '987654321098765432',
        ]);

    $supplier->refresh();
    expect($supplier->status->value)->toBe('active');
});

it('validates required fields on profile update', function () {
    $supplier = Supplier::factory()->active()->create([
        'clabe_interbancaria' => '012345678901234567',
    ]);

    $this->actingAs($supplier, 'supplier')
        ->put('/supplier/profile', [])
        ->assertSessionHasErrors([
            'address_street',
            'address_number',
            'address_neighborhood',
            'address_city',
            'address_country',
            'address_zip',
            'clabe_interbancaria',
        ]);
});

it('validates clabe format is 18 digits', function () {
    $supplier = Supplier::factory()->active()->create([
        'clabe_interbancaria' => '012345678901234567',
    ]);

    $this->actingAs($supplier, 'supplier')
        ->put('/supplier/profile', [
            'address_street' => 'Calle',
            'address_number' => '1',
            'address_neighborhood' => 'Colonia',
            'address_city' => 'Ciudad',
            'address_country' => 'Mexico',
            'address_zip' => '06500',
            'clabe_interbancaria' => '1234',
        ])
        ->assertSessionHasErrors('clabe_interbancaria');
});

it('validates zip code is 5 digits', function () {
    $supplier = Supplier::factory()->active()->create([
        'clabe_interbancaria' => '012345678901234567',
    ]);

    $this->actingAs($supplier, 'supplier')
        ->put('/supplier/profile', [
            'address_street' => 'Calle',
            'address_number' => '1',
            'address_neighborhood' => 'Colonia',
            'address_city' => 'Ciudad',
            'address_country' => 'Mexico',
            'address_zip' => 'ABCDE',
            'clabe_interbancaria' => '012345678901234567',
        ])
        ->assertSessionHasErrors('address_zip');
});

it('validates country must be valid option', function () {
    $supplier = Supplier::factory()->active()->create([
        'clabe_interbancaria' => '012345678901234567',
    ]);

    $this->actingAs($supplier, 'supplier')
        ->put('/supplier/profile', [
            'address_street' => 'Calle',
            'address_number' => '1',
            'address_neighborhood' => 'Colonia',
            'address_city' => 'Ciudad',
            'address_country' => 'InvalidCountry',
            'address_zip' => '06500',
            'clabe_interbancaria' => '012345678901234567',
        ])
        ->assertSessionHasErrors('address_country');
});

it('validates clabe uniqueness excluding own record', function () {
    $otherSupplier = Supplier::factory()->active()->create([
        'clabe_interbancaria' => '111111111111111111',
    ]);

    $supplier = Supplier::factory()->active()->create([
        'clabe_interbancaria' => '222222222222222222',
    ]);

    $this->actingAs($supplier, 'supplier')
        ->put('/supplier/profile', [
            'address_street' => 'Calle',
            'address_number' => '1',
            'address_neighborhood' => 'Colonia',
            'address_city' => 'Ciudad',
            'address_country' => 'Mexico',
            'address_zip' => '06500',
            'clabe_interbancaria' => '111111111111111111',
        ])
        ->assertSessionHasErrors('clabe_interbancaria');
});

it('allows supplier to keep their own clabe unchanged', function () {
    $supplier = Supplier::factory()->active()->create([
        'clabe_interbancaria' => '012345678901234567',
    ]);

    $this->actingAs($supplier, 'supplier')
        ->put('/supplier/profile', [
            'address_street' => 'Calle Nueva',
            'address_number' => '1',
            'address_neighborhood' => 'Colonia',
            'address_city' => 'Ciudad',
            'address_country' => 'Mexico',
            'address_zip' => '06500',
            'clabe_interbancaria' => '012345678901234567',
        ])
        ->assertRedirect('/dashboard');
});

it('forbids created supplier from updating profile', function () {
    $supplier = Supplier::factory()->create(['status' => 'created']);

    $this->actingAs($supplier, 'supplier')
        ->put('/supplier/profile', [
            'address_street' => 'Calle',
            'address_number' => '1',
            'address_neighborhood' => 'Colonia',
            'address_city' => 'Ciudad',
            'address_country' => 'Mexico',
            'address_zip' => '06500',
            'clabe_interbancaria' => '012345678901234567',
        ])
        ->assertForbidden();
});

it('forbids invited supplier from updating profile', function () {
    $supplier = Supplier::factory()->invited()->create();

    $this->actingAs($supplier, 'supplier')
        ->put('/supplier/profile', [
            'address_street' => 'Calle',
            'address_number' => '1',
            'address_neighborhood' => 'Colonia',
            'address_city' => 'Ciudad',
            'address_country' => 'Mexico',
            'address_zip' => '06500',
            'clabe_interbancaria' => '012345678901234567',
        ])
        ->assertForbidden();
});
