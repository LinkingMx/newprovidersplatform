<?php

/**
 * Phase 4: Onboarding Wizard
 */

use App\Enums\SupplierStatus;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;

$prefix = 'dusk_p4_'.substr(uniqid(), -6).'_';

beforeEach(function () use ($prefix) {
    $this->prefix = $prefix;

    $ids = DB::table('suppliers')->where('email', 'like', $prefix.'%')->pluck('id');
    if ($ids->isNotEmpty()) {
        DB::table('supplier_invitations')->whereIn('supplier_id', $ids)->delete();
        DB::table('suppliers')->whereIn('id', $ids)->delete();
    }

    $this->supplier = Supplier::factory()->create([
        'name' => 'Dusk Onboarding Wizard',
        'email' => $prefix.'wizard@test.com',
        'status' => 'registered',
        'password_hash' => Hash::make('DuskTest2024!'),
    ]);
});

afterEach(function () use ($prefix) {
    $ids = DB::table('suppliers')->where('email', 'like', $prefix.'%')->pluck('id');
    if ($ids->isNotEmpty()) {
        DB::table('supplier_invitations')->whereIn('supplier_id', $ids)->delete();
        DB::table('suppliers')->whereIn('id', $ids)->delete();
    }
});

// =============================================================================
// 4.1 — Step 1 (Dirección)
// =============================================================================

test('4.1a onboarding step 1 shows address fields', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->supplier, 'supplier')
            ->visit('/supplier/onboarding')
            ->waitForText('Completa tu Perfil', 10)
            ->assertSee('Paso 1 de 3')
            ->assertSee('Dirección')
            ->assertPresent('#address_street')
            ->assertPresent('#address_number')
            ->assertPresent('#address_neighborhood')
            ->assertPresent('#address_city')
            ->assertPresent('#address_zip');
    });
});

test('4.1b onboarding step 1 can fill and advance to step 2', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->supplier, 'supplier')
            ->visit('/supplier/onboarding')
            ->waitForText('Paso 1 de 3', 10)
            ->type('#address_street', 'Av. Paseo de la Reforma')
            ->type('#address_number', '505')
            ->type('#address_neighborhood', 'Cuauhtémoc')
            ->type('#address_city', 'Ciudad de México')
            ->type('#address_zip', '06500')
            ->press('Siguiente')
            ->waitForText('Paso 2 de 3', 5)
            ->assertSee('Datos Bancarios');
    });
});

// =============================================================================
// 4.2 — Step 2 (Datos Bancarios)
// =============================================================================

test('4.2a onboarding step 2 shows CLABE field', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->supplier, 'supplier')
            ->visit('/supplier/onboarding')
            ->waitForText('Paso 1 de 3', 10)
            ->type('#address_street', 'Calle Test')
            ->type('#address_number', '123')
            ->type('#address_neighborhood', 'Colonia')
            ->type('#address_city', 'CDMX')
            ->type('#address_zip', '06500')
            ->press('Siguiente')
            ->waitForText('Paso 2 de 3', 5)
            ->assertSee('CLABE Interbancaria')
            ->assertPresent('#clabe_interbancaria');
    });
});

test('4.2b onboarding step 2 can advance to step 3', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->supplier, 'supplier')
            ->visit('/supplier/onboarding')
            ->waitForText('Paso 1 de 3', 10)
            ->type('#address_street', 'Calle Test')
            ->type('#address_number', '123')
            ->type('#address_neighborhood', 'Colonia')
            ->type('#address_city', 'CDMX')
            ->type('#address_zip', '06500')
            ->press('Siguiente')
            ->waitForText('Paso 2 de 3', 5)
            ->type('#clabe_interbancaria', '012345678901234567')
            ->press('Siguiente')
            ->waitForText('Paso 3 de 3', 5)
            ->assertSee('Confirmación');
    });
});

test('4.2c onboarding can navigate back to previous step', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->supplier, 'supplier')
            ->visit('/supplier/onboarding')
            ->waitForText('Paso 1 de 3', 10)
            ->type('#address_street', 'Calle Test')
            ->type('#address_number', '123')
            ->type('#address_neighborhood', 'Colonia')
            ->type('#address_city', 'CDMX')
            ->type('#address_zip', '06500')
            ->press('Siguiente')
            ->waitForText('Paso 2 de 3', 5)
            ->press('Anterior')
            ->waitForText('Paso 1 de 3', 5)
            ->assertSee('Dirección');
    });
});

// =============================================================================
// 4.3 — Step 3 (Confirmación)
// =============================================================================

test('4.3a onboarding step 3 shows confirmation summary', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->supplier, 'supplier')
            ->visit('/supplier/onboarding')
            ->waitForText('Paso 1 de 3', 10)
            ->type('#address_street', 'Av. Paseo de la Reforma')
            ->type('#address_number', '505')
            ->type('#address_neighborhood', 'Cuauhtémoc')
            ->type('#address_city', 'Ciudad de México')
            ->type('#address_zip', '06500')
            ->press('Siguiente')
            ->waitForText('Paso 2 de 3', 5)
            ->type('#clabe_interbancaria', '012345678901234567')
            ->press('Siguiente')
            ->waitForText('Paso 3 de 3', 5)
            ->assertSee('Confirmación')
            ->assertSee('Av. Paseo de la Reforma');
    });
});

// =============================================================================
// 4.4 — Submit onboarding
// =============================================================================

test('4.4a onboarding submit completes profile and redirects to dashboard', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->supplier, 'supplier')
            ->visit('/supplier/onboarding')
            ->waitForText('Paso 1 de 3', 10)
            // Step 1
            ->type('#address_street', 'Av. Paseo de la Reforma')
            ->type('#address_number', '505')
            ->type('#address_neighborhood', 'Cuauhtémoc')
            ->type('#address_city', 'Ciudad de México')
            ->type('#address_zip', '06500')
            ->press('Siguiente')
            ->waitForText('Paso 2 de 3', 5)
            // Step 2
            ->type('#clabe_interbancaria', '012345678901234567')
            ->press('Siguiente')
            ->waitForText('Paso 3 de 3', 5)
            // Step 3
            ->click('#confirm')
            ->pause(500)
            ->press('Completar Onboarding')
            ->waitForLocation('/dashboard', 15)
            ->assertPathIs('/dashboard');
    });

    expect($this->supplier->fresh()->status)->toBe(SupplierStatus::ProfileCompleted);
});

test('4.4b active supplier is redirected from onboarding to dashboard', function () {
    $this->supplier->update(['status' => 'active']);

    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->supplier, 'supplier')
            ->visit('/supplier/onboarding')
            ->waitForLocation('/dashboard', 10)
            ->assertPathIs('/dashboard');
    });
});
