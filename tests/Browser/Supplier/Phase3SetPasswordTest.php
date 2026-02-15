<?php

/**
 * Phase 3: Set Password via Invitation Link
 */

use App\Enums\SupplierStatus;
use App\Models\Supplier;
use App\Models\SupplierInvitation;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Supplier\LoginPage;
use Tests\Browser\Pages\Supplier\SetPasswordPage;

$prefix = 'dusk_p3_'.substr(uniqid(), -6).'_';

beforeEach(function () use ($prefix) {
    $this->prefix = $prefix;

    // Clean up
    $ids = DB::table('suppliers')->where('email', 'like', $prefix.'%')->pluck('id');
    if ($ids->isNotEmpty()) {
        DB::table('supplier_invitations')->whereIn('supplier_id', $ids)->delete();
        DB::table('suppliers')->whereIn('id', $ids)->delete();
    }

    $this->token = bin2hex(random_bytes(32));

    $this->supplier = Supplier::factory()->invited()->create([
        'name' => 'Dusk SetPassword Supplier',
        'email' => $prefix.'setpw@test.com',
    ]);

    $this->invitation = SupplierInvitation::factory()->create([
        'supplier_id' => $this->supplier->id,
        'token' => $this->token,
        'expires_at' => now()->addDays(7),
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
// 3.1 — Set-password page UI
// =============================================================================

test('3.1a set password page shows form with valid token', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new SetPasswordPage($this->token))
            ->assertSee('Establecer Contraseña')
            ->assertPresent('input[name="password"]')
            ->assertPresent('input[name="password_confirmation"]');
    });
});

test('3.1b set password page shows error without token', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/supplier/set-password')
            ->assertSee('Enlace Inválido');
    });
});

test('3.1c set password page shows error with invalid token', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/supplier/set-password?token=invalidtoken123')
            ->assertSee('Enlace Inválido');
    });
});

test('3.1d set password page shows error with expired token', function () {
    $expired = SupplierInvitation::factory()->expired()->create([
        'supplier_id' => $this->supplier->id,
    ]);

    $this->browse(function (Browser $browser) use ($expired) {
        $browser->visit("/supplier/set-password?token={$expired->token}")
            ->assertSee('Enlace Inválido');
    });
});

// =============================================================================
// 3.2 — Password set + redirect to onboarding
// =============================================================================

test('3.2a supplier can set password and is redirected to onboarding', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new SetPasswordPage($this->token))
            ->type('@password', 'DuskTest2024!!')
            ->type('@password_confirmation', 'DuskTest2024!!')
            ->press('Establecer Contraseña')
            ->waitForLocation('/supplier/onboarding', 15)
            ->assertPathIs('/supplier/onboarding');
    });

    expect($this->supplier->fresh()->status)->toBe(SupplierStatus::Registered);
    expect($this->invitation->fresh()->accepted_at)->not->toBeNull();
});

test('3.2b set password validates weak password', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new SetPasswordPage($this->token))
            ->type('@password', '123')
            ->type('@password_confirmation', '123')
            ->press('Establecer Contraseña')
            ->pause(2000)
            ->assertSee('al menos 10 caracteres');
    });
});

test('3.2c set password validates mismatched passwords', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new SetPasswordPage($this->token))
            ->type('@password', 'DuskTest2024!!')
            ->type('@password_confirmation', 'DifferentPass2024!')
            ->press('Establecer Contraseña')
            ->pause(2000)
            ->assertSee('no coinciden');
    });
});

// =============================================================================
// 3.3 — Provider login with new credentials
// =============================================================================

test('3.3 supplier can login after setting password', function () {
    $this->supplier->update([
        'password_hash' => bcrypt('DuskTest2024!!'),
        'status' => 'registered',
    ]);
    $this->invitation->update(['accepted_at' => now()]);

    $this->browse(function (Browser $browser) {
        $browser->visit(new LoginPage)
            ->submitLogin($this->supplier->email, 'DuskTest2024!!')
            ->waitForLocation('/supplier/onboarding', 10)
            ->assertPathIs('/supplier/onboarding');
    });
});
