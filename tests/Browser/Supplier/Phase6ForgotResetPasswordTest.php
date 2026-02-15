<?php

/**
 * Phase 6: Forgot/Reset Password
 */

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Supplier\ForgotPasswordPage;
use Tests\Browser\Pages\Supplier\LoginPage;
use Tests\Browser\Pages\Supplier\ResetPasswordPage;

$prefix = 'dusk_p6_'.substr(uniqid(), -6).'_';

beforeEach(function () use ($prefix) {
    $this->prefix = $prefix;

    $ids = DB::table('suppliers')->where('email', 'like', $prefix.'%')->pluck('id');
    if ($ids->isNotEmpty()) {
        DB::table('suppliers')->whereIn('id', $ids)->delete();
    }

    $this->supplier = Supplier::factory()->profileCompleted()->create([
        'name' => 'Dusk Reset Password',
        'email' => $prefix.'reset@test.com',
        'password_hash' => Hash::make('DuskTest2024!'),
    ]);
});

afterEach(function () use ($prefix) {
    $ids = DB::table('suppliers')->where('email', 'like', $prefix.'%')->pluck('id');
    if ($ids->isNotEmpty()) {
        DB::table('suppliers')->whereIn('id', $ids)->delete();
    }
});

// =============================================================================
// 6.1 — Forgot password page UI
// =============================================================================

test('6.1 forgot password page shows form', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new ForgotPasswordPage)
            ->assertSee('Restablecer Contraseña')
            ->assertPresent('input[name="email"]')
            ->assertPresent('button[type="submit"]');
    });
});

// =============================================================================
// 6.2 — Valid email shows success message
// =============================================================================

test('6.2 forgot password with valid email shows success', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new ForgotPasswordPage)
            ->type('@email', $this->supplier->email)
            ->press('Enviar enlace de restablecimiento')
            ->waitForText('Si tu correo está registrado', 10)
            ->assertSee('recibirás un enlace');
    });
});

// =============================================================================
// 6.3 — Invalid email shows same success (anti-enumeration)
// =============================================================================

test('6.3 forgot password with nonexistent email shows same success', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new ForgotPasswordPage)
            ->type('@email', 'nonexistent_'.uniqid().'@test.com')
            ->press('Enviar enlace de restablecimiento')
            ->waitForText('Si tu correo está registrado', 10)
            ->assertSee('recibirás un enlace');
    });
});

// =============================================================================
// 6.4 — Empty email stays on page
// =============================================================================

test('6.4 forgot password with empty email stays on page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new ForgotPasswordPage)
            ->press('Enviar enlace de restablecimiento')
            ->pause(1000)
            ->assertPathIs('/supplier/forgot-password');
    });
});

// =============================================================================
// 6.6 — Reset password page loads with token
// =============================================================================

test('6.6a reset password page loads with valid token', function () {
    $token = bin2hex(random_bytes(32));
    $this->supplier->update([
        'password_reset_token' => hash('sha256', $token),
        'password_reset_expires_at' => now()->addHour(),
    ]);

    $this->browse(function (Browser $browser) use ($token) {
        $browser->visit("/supplier/reset-password?token={$token}&email={$this->supplier->email}")
            ->assertSee('Nueva Contraseña')
            ->assertPresent('input[name="password"]')
            ->assertPresent('input[name="password_confirmation"]');
    });
});

test('6.6b reset password page with invalid token shows error', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/supplier/reset-password?token=invalidtoken&email=test_'.uniqid().'@test.com')
            ->assertSee('Enlace Inválido');
    });
});

// =============================================================================
// 6.7 — Password reset submitted
// =============================================================================

test('6.7a password reset with valid data succeeds', function () {
    $token = bin2hex(random_bytes(32));
    $this->supplier->update([
        'password_reset_token' => hash('sha256', $token),
        'password_reset_expires_at' => now()->addHour(),
    ]);

    $this->browse(function (Browser $browser) use ($token) {
        $browser->visit(new ResetPasswordPage($token, $this->supplier->email))
            ->type('@password', 'NewDuskPass2024!')
            ->type('@password_confirmation', 'NewDuskPass2024!')
            ->press('Restablecer Contraseña')
            ->waitForLocation('/supplier/login', 10)
            ->assertPathIs('/supplier/login')
            ->assertSee('Tu contraseña ha sido restablecida');
    });

    expect(Hash::check('NewDuskPass2024!', $this->supplier->fresh()->password_hash))->toBeTrue();
});

test('6.7b password reset validates weak password', function () {
    $token = bin2hex(random_bytes(32));
    $this->supplier->update([
        'password_reset_token' => hash('sha256', $token),
        'password_reset_expires_at' => now()->addHour(),
    ]);

    $this->browse(function (Browser $browser) use ($token) {
        $browser->visit(new ResetPasswordPage($token, $this->supplier->email))
            ->type('@password', '123')
            ->type('@password_confirmation', '123')
            ->press('Restablecer Contraseña')
            ->pause(2000)
            ->assertSee('al menos 10 caracteres');
    });
});

test('6.7c password reset validates mismatched passwords', function () {
    $token = bin2hex(random_bytes(32));
    $this->supplier->update([
        'password_reset_token' => hash('sha256', $token),
        'password_reset_expires_at' => now()->addHour(),
    ]);

    $this->browse(function (Browser $browser) use ($token) {
        $browser->visit(new ResetPasswordPage($token, $this->supplier->email))
            ->type('@password', 'NewDuskPass2024!')
            ->type('@password_confirmation', 'DifferentPass2024!')
            ->press('Restablecer Contraseña')
            ->pause(2000)
            ->assertSee('no coinciden');
    });
});

test('6.7d supplier can login after password reset', function () {
    $this->supplier->update([
        'password_hash' => Hash::make('ResetDusk2024!'),
        'password_reset_token' => null,
        'password_reset_expires_at' => null,
    ]);

    $this->browse(function (Browser $browser) {
        $browser->visit(new LoginPage)
            ->submitLogin($this->supplier->email, 'ResetDusk2024!')
            ->waitForLocation('/dashboard', 10)
            ->assertPathIs('/dashboard');
    });
});
