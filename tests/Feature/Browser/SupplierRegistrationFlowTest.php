<?php

use App\Jobs\SupplierVerificationJob;
use App\Models\Supplier;
use App\Models\SupplierInvitation;
use Illuminate\Support\Facades\Queue;

describe('Supplier Registration Flow', function () {
    describe('SetPassword Endpoint', function () {
        it('shows set password page with valid token', function () {
            $supplier = Supplier::factory()->create();
            $invitation = SupplierInvitation::factory()->create([
                'supplier_id' => $supplier->id,
            ]);

            $response = $this->get('/supplier/set-password?token='.$invitation->token);

            $response->assertSuccessful();
        });

        it('shows error page when no token provided', function () {
            $response = $this->get('/supplier/set-password');

            $response->assertSuccessful();
        });

        it('shows error page when token is invalid', function () {
            $response = $this->get('/supplier/set-password?token=invalid-token');

            $response->assertSuccessful();
        });

        it('shows error page when token is expired', function () {
            $supplier = Supplier::factory()->create();
            $invitation = SupplierInvitation::factory()->expired()->create([
                'supplier_id' => $supplier->id,
            ]);

            $response = $this->get('/supplier/set-password?token='.$invitation->token);

            $response->assertSuccessful();
        });

        it('rejects weak passwords', function () {
            $supplier = Supplier::factory()->create();
            $invitation = SupplierInvitation::factory()->create([
                'supplier_id' => $supplier->id,
            ]);

            $response = $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'weak',
                'password_confirmation' => 'weak',
            ]);

            $response->assertInvalid('password');
        });

        it('accepts strong password and logs in supplier', function () {
            $supplier = Supplier::factory()->create();
            $invitation = SupplierInvitation::factory()->create([
                'supplier_id' => $supplier->id,
            ]);

            $response = $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
            ]);

            $response->assertRedirect('/supplier/onboarding');
            $this->assertAuthenticated('supplier');
            $supplier->refresh();
            expect($supplier->status)->toBe('registered');
        });

        it('marks invitation as accepted after password set', function () {
            $supplier = Supplier::factory()->create();
            $invitation = SupplierInvitation::factory()->create([
                'supplier_id' => $supplier->id,
            ]);

            $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
            ]);

            $invitation->refresh();
            expect($invitation->accepted_at)->not->toBeNull();
        });

        it('redirects active supplier away from set password page', function () {
            $supplier = Supplier::factory()->active()->create();
            $invitation = SupplierInvitation::factory()->create([
                'supplier_id' => $supplier->id,
            ]);

            $response = $this->get('/supplier/set-password?token='.$invitation->token);

            $response->assertRedirect('/supplier/dashboard');
        });
    });

    describe('Onboarding Endpoint', function () {
        it('requires supplier authentication', function () {
            $response = $this->get('/supplier/onboarding');

            $response->assertRedirect();
        });

        it('displays onboarding page for registered supplier', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->get('/supplier/onboarding');

            $response->assertSuccessful();
        });

        it('redirects if supplier is already active', function () {
            $supplier = Supplier::factory()->active()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->get('/supplier/onboarding');

            $response->assertRedirect('/supplier/dashboard');
        });

        it('requires all address fields', function () {
            $supplier = Supplier::factory()->registered()->create();

            $missingFields = [
                'address_street',
                'address_number',
                'address_neighborhood',
                'address_city',
                'address_zip',
                'clabe_interbancaria',
            ];

            foreach ($missingFields as $field) {
                $data = [
                    'address_street' => 'Calle 123',
                    'address_number' => '505',
                    'address_neighborhood' => 'Centro',
                    'address_city' => 'Mexico',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                    'confirm' => 'on',
                ];

                unset($data[$field]);

                $response = $this->actingAs($supplier, 'supplier')
                    ->post('/supplier/onboarding/submit', $data);

                $response->assertInvalid($field);
            }
        });

        it('validates CLABE format must be 18 digits', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Calle 123',
                    'address_number' => '505',
                    'address_neighborhood' => 'Centro',
                    'address_city' => 'Mexico',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '00201111111111111',
                    'confirm' => 'on',
                ]);

            $response->assertInvalid('clabe_interbancaria');
        });

        it('validates CLABE must be numeric only', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Calle 123',
                    'address_number' => '505',
                    'address_neighborhood' => 'Centro',
                    'address_city' => 'Mexico',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011ABC11111111',
                    'confirm' => 'on',
                ]);

            $response->assertInvalid('clabe_interbancaria');
        });

        it('validates zip code must be 5 digits', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Calle 123',
                    'address_number' => '505',
                    'address_neighborhood' => 'Centro',
                    'address_city' => 'Mexico',
                    'address_country' => 'Mexico',
                    'address_zip' => 'ABC',
                    'clabe_interbancaria' => '002011111111111111',
                    'confirm' => 'on',
                ]);

            $response->assertInvalid('address_zip');
        });

        it('requires confirmation checkbox', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Calle 123',
                    'address_number' => '505',
                    'address_neighborhood' => 'Centro',
                    'address_city' => 'Mexico',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                ]);

            $response->assertInvalid('confirm');
        });

        it('updates supplier profile and redirects to dashboard', function () {
            Queue::fake();

            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Calle Principal 123',
                    'address_number' => '123',
                    'address_neighborhood' => 'Centro',
                    'address_city' => 'Mexico City',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                    'confirm' => 'on',
                ]);

            $response->assertRedirect('/supplier/dashboard');
            $supplier->refresh();
            expect($supplier->status)->toBe('profile_completed');
            expect($supplier->address_street)->toBe('Calle Principal 123');
            expect($supplier->address_number)->toBe('123');
            expect($supplier->clabe_interbancaria)->toBe('002011111111111111');
        });

        it('queues verification job after onboarding completion', function () {
            Queue::fake();

            $supplier = Supplier::factory()->registered()->create();

            $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Calle 123',
                    'address_number' => '505',
                    'address_neighborhood' => 'Centro',
                    'address_city' => 'Mexico',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                    'confirm' => 'on',
                ]);

            Queue::assertPushed(SupplierVerificationJob::class);
        });

        it('rejects duplicate CLABE', function () {
            $existingSupplier = Supplier::factory()->active()->create([
                'clabe_interbancaria' => '002011111111111111',
            ]);

            $newSupplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($newSupplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Calle 123',
                    'address_number' => '505',
                    'address_neighborhood' => 'Centro',
                    'address_city' => 'Mexico',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                    'confirm' => 'on',
                ]);

            $response->assertInvalid('clabe_interbancaria');
        });

        it('accepts Mexico as valid country', function () {
            Queue::fake();

            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Calle 123',
                    'address_number' => '505',
                    'address_neighborhood' => 'Centro',
                    'address_city' => 'Mexico',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                    'confirm' => 'on',
                ]);

            $response->assertRedirect();
            $supplier->refresh();
            expect($supplier->address_country)->toBe('Mexico');
        });

        it('accepts USA as valid country', function () {
            Queue::fake();

            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Calle 123',
                    'address_number' => '505',
                    'address_neighborhood' => 'Centro',
                    'address_city' => 'City',
                    'address_country' => 'USA',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002021111111111111',
                    'confirm' => 'on',
                ]);

            $response->assertRedirect();
            $supplier->refresh();
            expect($supplier->address_country)->toBe('USA');
        });

        it('accepts Canada as valid country', function () {
            Queue::fake();

            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Calle 123',
                    'address_number' => '505',
                    'address_neighborhood' => 'Centro',
                    'address_city' => 'City',
                    'address_country' => 'Canada',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002031111111111111',
                    'confirm' => 'on',
                ]);

            $response->assertRedirect();
            $supplier->refresh();
            expect($supplier->address_country)->toBe('Canada');
        });
    });

    describe('Dashboard Endpoint', function () {
        it('requires supplier authentication', function () {
            $response = $this->get('/supplier/dashboard');

            $response->assertRedirect();
        });

        it('displays dashboard for any authenticated supplier', function () {
            $supplier = Supplier::factory()->registered()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->get('/supplier/dashboard');

            $response->assertSuccessful();
        });
    });

    describe('Complete Registration Flow', function () {
        it('completes entire flow: SetPassword → Onboarding → Dashboard', function () {
            Queue::fake();

            // Step 1: Create invited supplier
            $supplier = Supplier::factory()->create([
                'name' => 'Acme Supply Co',
            ]);
            $invitation = SupplierInvitation::factory()->create([
                'supplier_id' => $supplier->id,
            ]);

            // Step 2: Set password
            $passwordResponse = $this->post('/supplier/auth/set-password', [
                'token' => $invitation->token,
                'password' => 'SecurePass123!',
                'password_confirmation' => 'SecurePass123!',
            ]);

            $passwordResponse->assertRedirect('/supplier/onboarding');
            $supplier->refresh();
            expect($supplier->status)->toBe('registered');
            $this->assertAuthenticated('supplier');

            // Step 3: Complete onboarding
            $onboardingResponse = $this->actingAs($supplier, 'supplier')
                ->post('/supplier/onboarding/submit', [
                    'address_street' => 'Calle Principal 456',
                    'address_number' => '456',
                    'address_neighborhood' => 'Centro',
                    'address_city' => 'Mexico City',
                    'address_country' => 'Mexico',
                    'address_zip' => '06500',
                    'clabe_interbancaria' => '002011111111111111',
                    'confirm' => 'on',
                ]);

            $onboardingResponse->assertRedirect('/supplier/dashboard');
            $supplier->refresh();
            expect($supplier->status)->toBe('profile_completed');
            expect($supplier->address_street)->toBe('Calle Principal 456');

            // Step 4: Dashboard access
            $dashboardResponse = $this->actingAs($supplier, 'supplier')
                ->get('/supplier/dashboard');

            $dashboardResponse->assertSuccessful();

            // Verify job dispatch
            Queue::assertPushed(SupplierVerificationJob::class);
        });

        it('shows onboarding for created supplier (not yet registered)', function () {
            $supplier = Supplier::factory()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->get('/supplier/onboarding');

            $response->assertSuccessful();
        });

        it('shows onboarding for profile_completed supplier (awaiting verification)', function () {
            $supplier = Supplier::factory()->profileCompleted()->create();

            $response = $this->actingAs($supplier, 'supplier')
                ->get('/supplier/onboarding');

            $response->assertSuccessful();
        });
    });
});
