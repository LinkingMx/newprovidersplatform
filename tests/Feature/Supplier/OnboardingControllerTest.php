<?php

use App\Jobs\SupplierVerificationJob;
use App\Models\Supplier;
use Illuminate\Support\Facades\Queue;

describe('OnboardingController', function () {
    describe('submit', function () {
        it('successfully submits onboarding with valid data', function () {
            Queue::fake();

            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Avenida Paseo de la Reforma',
                    'address_number' => '505',
                    'address_neighborhood' => 'Cuauhtémoc',
                    'address_city' => 'Ciudad de México',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                    'confirm' => 'on',
                ]);

            $response->assertRedirect();

            $supplier->refresh();
            expect($supplier->status)->toBe('profile_completed');
            expect($supplier->address_street)->toBe('Avenida Paseo de la Reforma');
            expect($supplier->clabe_interbancaria)->toBe('002011111111111111');
        });

        it('dispatches verification job after successful submission', function () {
            Queue::fake();

            $supplier = Supplier::factory()->registered()->create();

            $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Avenida Paseo de la Reforma',
                    'address_number' => '505',
                    'address_neighborhood' => 'Cuauhtémoc',
                    'address_city' => 'Ciudad de México',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                    'confirm' => 'on',
                ]);

            Queue::assertPushed(SupplierVerificationJob::class);
        });

        it('requires address_street', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => '',
                    'address_number' => '505',
                    'address_neighborhood' => 'Cuauhtémoc',
                    'address_city' => 'Ciudad de México',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                ]);

            $response->assertInvalid('address_street');
        });

        it('requires address_number', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Avenida Paseo de la Reforma',
                    'address_number' => '',
                    'address_neighborhood' => 'Cuauhtémoc',
                    'address_city' => 'Ciudad de México',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                ]);

            $response->assertInvalid('address_number');
        });

        it('requires address_neighborhood', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Avenida Paseo de la Reforma',
                    'address_number' => '505',
                    'address_neighborhood' => '',
                    'address_city' => 'Ciudad de México',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                ]);

            $response->assertInvalid('address_neighborhood');
        });

        it('requires address_city', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Avenida Paseo de la Reforma',
                    'address_number' => '505',
                    'address_neighborhood' => 'Cuauhtémoc',
                    'address_city' => '',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                ]);

            $response->assertInvalid('address_city');
        });

        it('requires address_zip', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Avenida Paseo de la Reforma',
                    'address_number' => '505',
                    'address_neighborhood' => 'Cuauhtémoc',
                    'address_city' => 'Ciudad de México',
                    'address_country' => 'Mexico',
                    'address_zip' => '',
                    'clabe_interbancaria' => '002011111111111111',
                ]);

            $response->assertInvalid('address_zip');
        });

        it('requires valid 5-digit zip code', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Avenida Paseo de la Reforma',
                    'address_number' => '505',
                    'address_neighborhood' => 'Cuauhtémoc',
                    'address_city' => 'Ciudad de México',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500ABC',
                    'clabe_interbancaria' => '002011111111111111',
                ]);

            $response->assertInvalid('address_zip');
        });

        it('requires clabe_interbancaria', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Avenida Paseo de la Reforma',
                    'address_number' => '505',
                    'address_neighborhood' => 'Cuauhtémoc',
                    'address_city' => 'Ciudad de México',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '',
                ]);

            $response->assertInvalid('clabe_interbancaria');
        });

        it('requires exactly 18 digits in clabe_interbancaria', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Avenida Paseo de la Reforma',
                    'address_number' => '505',
                    'address_neighborhood' => 'Cuauhtémoc',
                    'address_city' => 'Ciudad de México',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '00201111111111111',
                ]);

            $response->assertInvalid('clabe_interbancaria');
        });

        it('requires clabe_interbancaria to be numeric only', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Avenida Paseo de la Reforma',
                    'address_number' => '505',
                    'address_neighborhood' => 'Cuauhtémoc',
                    'address_city' => 'Ciudad de México',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011ABC111111111',
                ]);

            $response->assertInvalid('clabe_interbancaria');
        });

        it('rejects duplicate clabe_interbancaria', function () {
            Queue::fake();

            $existingSupplier = Supplier::factory()->active()->create([
                'clabe_interbancaria' => '002011111111111111',
            ]);

            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Avenida Paseo de la Reforma',
                    'address_number' => '505',
                    'address_neighborhood' => 'Cuauhtémoc',
                    'address_city' => 'Ciudad de México',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                ]);

            $response->assertInvalid('clabe_interbancaria');
        });

        it('requires authentication', function () {
            $response = $this->post('/supplier/onboarding/submit', [
                'address_street' => 'Avenida Paseo de la Reforma',
                'address_number' => '505',
                'address_neighborhood' => 'Cuauhtémoc',
                'address_city' => 'Ciudad de México',
                'address_country' => 'Mexico',
                'address_zip' => '06500',
                'clabe_interbancaria' => '002011111111111111',
            ]);

            $response->assertRedirect();
        });

        it('updates all address fields atomically', function () {
            Queue::fake();

            $supplier = Supplier::factory()->registered()->create([
                'address_street' => 'Old Street',
            ]);

            $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'New Street',
                    'address_number' => '123',
                    'address_neighborhood' => 'New Neighborhood',
                    'address_city' => 'New City',
                    'address_country' => 'Mexico',
                    'address_zip' => '12345',
                    'clabe_interbancaria' => '002011111111111111',
                    'confirm' => 'on',
                ]);

            $supplier->refresh();
            expect($supplier->address_street)->toBe('New Street');
            expect($supplier->address_number)->toBe('123');
            expect($supplier->address_neighborhood)->toBe('New Neighborhood');
            expect($supplier->address_city)->toBe('New City');
            expect($supplier->address_zip)->toBe('12345');
        });

        it('accepts valid country values', function () {
            Queue::fake();

            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Avenida Paseo de la Reforma',
                    'address_number' => '505',
                    'address_neighborhood' => 'Cuauhtémoc',
                    'address_city' => 'Ciudad de México',
                    'address_country' => 'USA',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                    'confirm' => 'on',
                ]);

            $response->assertRedirect();
            $supplier->refresh();
            expect($supplier->address_country)->toBe('USA');
        });

        it('accepts long street names', function () {
            Queue::fake();

            $supplier = Supplier::factory()->registered()->create();

            $longStreet = 'Avenida Paseo de la Reforma Miguel Ángel de Quevedo y Zubieta';

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => $longStreet,
                    'address_number' => '505',
                    'address_neighborhood' => 'Cuauhtémoc',
                    'address_city' => 'Ciudad de México',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                    'confirm' => 'on',
                ]);

            $response->assertRedirect();
            $supplier->refresh();
            expect($supplier->address_street)->toBe($longStreet);
        });
    });
});
