<?php

/**
 * Phase 7: Edge Cases & Security
 */

use App\Models\Supplier;
use App\Models\SupplierInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\Supplier\DashboardPage;
use Tests\Browser\Pages\Supplier\LoginPage;

$prefix = 'dusk_p7_'.substr(uniqid(), -6).'_';

beforeEach(function () use ($prefix) {
    $this->prefix = $prefix;
    $this->testPassword = 'DuskTest2024!';

    $ids = DB::table('suppliers')->where('email', 'like', $prefix.'%')->pluck('id');
    if ($ids->isNotEmpty()) {
        DB::table('supplier_invitations')->whereIn('supplier_id', $ids)->delete();
        DB::table('suppliers')->whereIn('id', $ids)->delete();
    }

    $this->supplier = Supplier::factory()->profileCompleted()->create([
        'name' => 'Dusk Security Test',
        'email' => $prefix.'security@test.com',
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
// 7.1 — Invalid credentials show error
// =============================================================================

test('7.1a invalid credentials show error message', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new LoginPage)
            ->submitLogin($this->supplier->email, 'WrongPassword123!')
            ->waitForText('Las credenciales no coinciden', 10)
            ->assertSee('Las credenciales no coinciden')
            ->assertPathIs('/supplier/login');
    });
});

test('7.1b nonexistent email shows same error', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new LoginPage)
            ->submitLogin('noexiste_'.uniqid().'@test.com', 'AnyPassword123!')
            ->waitForText('Las credenciales no coinciden', 10)
            ->assertSee('Las credenciales no coinciden');
    });
});

// =============================================================================
// 7.2 — Invalid invitation token
// =============================================================================

test('7.2a invalid invitation token shows error', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/supplier/set-password?token=invalid_random_token_'.uniqid())
            ->assertSee('Enlace Inválido');
    });
});

test('7.2b expired invitation token shows error', function () {
    $expired = SupplierInvitation::factory()->expired()->create([
        'supplier_id' => $this->supplier->id,
    ]);

    $this->browse(function (Browser $browser) use ($expired) {
        $browser->visit("/supplier/set-password?token={$expired->token}")
            ->assertSee('Enlace Inválido');
    });
});

// =============================================================================
// 7.3 — Admin panel access (unauthenticated)
// =============================================================================

test('7.3a unauthenticated user is redirected from admin panel', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/admin')
            ->waitForLocation('/admin/login', 10)
            ->assertPathIs('/admin/login');
    });
});

test('7.3b unauthenticated user is redirected from admin suppliers', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/admin/suppliers')
            ->waitForLocation('/admin/login', 10)
            ->assertPathIs('/admin/login');
    });
});

// =============================================================================
// 7.4 — Invalid reset password token
// =============================================================================

test('7.4a invalid reset token shows error', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/supplier/reset-password?token=invalidtoken_'.uniqid().'&email=test@test.com')
            ->assertSee('Enlace Inválido');
    });
});

test('7.4b expired reset token shows error', function () {
    $expiredToken = bin2hex(random_bytes(32));
    $this->supplier->update([
        'password_reset_token' => hash('sha256', $expiredToken),
        'password_reset_expires_at' => now()->subHour(),
    ]);

    $this->browse(function (Browser $browser) use ($expiredToken) {
        $browser->visit("/supplier/reset-password?token={$expiredToken}&email={$this->supplier->email}")
            ->assertSee('Enlace Inválido');
    });
});

// =============================================================================
// 7.5 — Valid login + dashboard access
// =============================================================================

test('7.5 valid login redirects to dashboard', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new LoginPage)
            ->submitLogin($this->supplier->email, $this->testPassword)
            ->waitForLocation('/dashboard', 10)
            ->on(new DashboardPage);
    });
});

// =============================================================================
// 7.6 — Authenticated user → login page redirect
// =============================================================================

test('7.6a authenticated supplier is redirected from login page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new LoginPage)
            ->submitLogin($this->supplier->email, $this->testPassword)
            ->waitForLocation('/dashboard', 10);

        $browser->visit('/supplier/login')
            ->waitForLocation('/dashboard', 10)
            ->assertPathIs('/dashboard');
    });
});

test('7.6b authenticated supplier is redirected from forgot password', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new LoginPage)
            ->submitLogin($this->supplier->email, $this->testPassword)
            ->waitForLocation('/dashboard', 10);

        $browser->visit('/supplier/forgot-password')
            ->waitForLocation('/dashboard', 10)
            ->assertPathIs('/dashboard');
    });
});

// =============================================================================
// 7.7 — Provider accessing admin routes
// =============================================================================

test('7.7a authenticated supplier cannot access admin panel', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->supplier, 'supplier')
            ->visit('/admin')
            ->pause(2000)
            ->assertPathIs('/admin/login');
    });
});

test('7.7b authenticated supplier cannot access admin suppliers', function () {
    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->supplier, 'supplier')
            ->visit('/admin/suppliers')
            ->pause(2000)
            ->assertPathIs('/admin/login');
    });
});

// =============================================================================
// 7.8 — Rate limiting
// =============================================================================

test('7.8.1 login is rate limited after multiple failures', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit(new LoginPage);

        // Submit login attempts, waiting for each response
        for ($i = 0; $i < 6; $i++) {
            // If an Inertia error modal (iframe) appeared, rate limiting worked
            $iframes = $browser->driver->findElements(\Facebook\WebDriver\WebDriverBy::tagName('iframe'));
            if (count($iframes) > 0) {
                break;
            }

            $browser->waitFor('button[type="submit"]:not([disabled])', 5)
                ->clear('@email')
                ->clear('@password')
                ->type('@email', $this->supplier->email)
                ->type('@password', 'WrongPassword'.$i)
                ->click('@submit')
                ->pause(2000);
        }

        $browser->pause(1000);

        // Check main page source
        $pageSource = $browser->driver->getPageSource();
        $hasRateLimiting = str_contains($pageSource, 'Too Many')
            || str_contains($pageSource, '429');

        // Check inside Inertia error modal iframe
        if (! $hasRateLimiting) {
            $iframes = $browser->driver->findElements(\Facebook\WebDriver\WebDriverBy::tagName('iframe'));
            foreach ($iframes as $iframe) {
                try {
                    $browser->driver->switchTo()->frame($iframe);
                    $iframeSource = $browser->driver->getPageSource();
                    if (str_contains($iframeSource, 'Too Many') || str_contains($iframeSource, '429')) {
                        $hasRateLimiting = true;
                    }
                    $browser->driver->switchTo()->defaultContent();
                } catch (\Exception) {
                    $browser->driver->switchTo()->defaultContent();
                }
                if ($hasRateLimiting) {
                    break;
                }
            }
        }

        expect($hasRateLimiting)->toBeTrue();
    });
})->group('rate-limiting');

test('7.8.2 forgot password is rate limited', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/supplier/forgot-password');

        // Submit 3 times (the rate limit), waiting for each response
        for ($i = 0; $i < 3; $i++) {
            $browser->waitFor('button[type="submit"]:not([disabled])', 5)
                ->clear('input[name="email"]')
                ->type('input[name="email"]', $this->supplier->email)
                ->press('Enviar enlace de restablecimiento')
                ->pause(2000);
        }

        // 4th attempt should be rate-limited (limit is 3/min)
        $browser->waitFor('button[type="submit"]:not([disabled])', 5)
            ->clear('input[name="email"]')
            ->type('input[name="email"]', $this->supplier->email)
            ->press('Enviar enlace de restablecimiento')
            ->pause(2000);

        $pageSource = $browser->driver->getPageSource();
        $hasRateLimiting = str_contains($pageSource, 'muchos intentos')
            || str_contains($pageSource, 'Too many')
            || str_contains($pageSource, 'Too Many')
            || str_contains($pageSource, 'Demasiados')
            || str_contains($pageSource, '429');

        // Inertia renders 429 responses in an iframe modal
        if (! $hasRateLimiting) {
            $iframes = $browser->driver->findElements(\Facebook\WebDriver\WebDriverBy::tagName('iframe'));
            foreach ($iframes as $iframe) {
                try {
                    $browser->driver->switchTo()->frame($iframe);
                    $iframeSource = $browser->driver->getPageSource();
                    if (str_contains($iframeSource, 'Too Many') || str_contains($iframeSource, '429')) {
                        $hasRateLimiting = true;
                    }
                    $browser->driver->switchTo()->defaultContent();
                } catch (\Exception) {
                    $browser->driver->switchTo()->defaultContent();
                }
                if ($hasRateLimiting) {
                    break;
                }
            }
        }

        expect($hasRateLimiting)->toBeTrue();
    });
})->group('rate-limiting');

// =============================================================================
// Soft-deleted / non-loginable supplier
// =============================================================================

test('7.C1 soft-deleted supplier cannot login', function () {
    // Clear rate limiter cache from prior rate limiting tests
    \Illuminate\Support\Facades\Cache::flush();

    $this->supplier->delete();

    $this->browse(function (Browser $browser) {
        // Navigate away from any 429 error page
        $browser->visit('about:blank')
            ->pause(500);

        $browser->visit('/supplier/login')
            ->waitFor('input[name="email"]', 10)
            ->type('input[name="email"]', $this->supplier->email)
            ->type('input[name="password"]', $this->testPassword)
            ->click('button[type="submit"]')
            ->waitForText('Las credenciales no coinciden', 10)
            ->assertSee('Las credenciales no coinciden')
            ->assertPathIs('/supplier/login');
    });
});

test('7.C2 supplier with non-loginable status cannot login', function () {
    $this->supplier->update(['status' => 'created']);

    $this->browse(function (Browser $browser) {
        $browser->visit(new LoginPage)
            ->submitLogin($this->supplier->email, $this->testPassword)
            ->pause(3000)
            ->assertPathIs('/supplier/login');
    });
});
