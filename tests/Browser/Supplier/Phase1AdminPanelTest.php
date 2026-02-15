<?php

/**
 * Phase 1: Admin Panel Foundation
 *
 * 1.1 Admin Login
 * 1.2 Admin Navigation
 * 1.3 Catálogos
 */

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Spatie\Permission\Models\Role;

$prefix = 'dusk_p1_'.substr(uniqid(), -6).'_';

beforeEach(function () use ($prefix) {
    $this->prefix = $prefix;

    // Clean up any leftover data from previous runs
    DB::table('users')->where('email', 'like', $prefix.'%')->delete();

    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create([
        'name' => 'Admin Dusk Test',
        'email' => $prefix.'admin@test.com',
        'password' => Hash::make('AdminDusk2024!'),
    ]);
    $this->admin->assignRole('super_admin');
});

afterEach(function () use ($prefix) {
    DB::table('users')->where('email', 'like', $prefix.'%')->delete();
});

// =============================================================================
// 1.1 — Admin Login
// =============================================================================

test('1.1a admin can login with valid credentials', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/admin/login')
            ->waitFor('input[type="email"]', 5)
            ->type('input[type="email"]', $this->admin->email)
            ->type('input[type="password"]', 'AdminDusk2024!')
            ->press('Entrar')
            ->waitForLocation('/admin', 10)
            ->assertPathBeginsWith('/admin');
    });
});

test('1.1b admin login rejects invalid credentials', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/admin/login')
            ->waitFor('input[type="email"]', 5)
            ->type('input[type="email"]', $this->admin->email)
            ->type('input[type="password"]', 'WrongPassword!')
            ->press('Entrar')
            ->pause(2000)
            ->assertPathIs('/admin/login');
    });
});

// =============================================================================
// 1.2 — Admin Navigation
// =============================================================================

test('1.2a admin sidebar shows navigation sections', function () {
    $this->browse(function (Browser $browser) {
        $browser->logout()
            ->loginAs($this->admin)
            ->visit('/admin')
            ->pause(3000)
            ->assertSee('Proveedores');
    });
});

test('1.2b admin can navigate to suppliers list', function () {
    $this->browse(function (Browser $browser) {
        $browser->logout()
            ->loginAs($this->admin)
            ->visit('/admin/suppliers')
            ->pause(3000)
            ->assertPathIs('/admin/suppliers');
    });
});

// =============================================================================
// 1.3 — Catálogos
// =============================================================================

test('1.3a admin can access provider types catalogue', function () {
    $this->browse(function (Browser $browser) {
        $browser->logout()
            ->loginAs($this->admin)
            ->visit('/admin/provider-types')
            ->pause(3000)
            ->assertPathIs('/admin/provider-types');
    });
});

test('1.3b admin can access document types catalogue', function () {
    $this->browse(function (Browser $browser) {
        $browser->logout()
            ->loginAs($this->admin)
            ->visit('/admin/document-types')
            ->pause(3000)
            ->assertPathIs('/admin/document-types');
    });
});

test('1.3c admin can access document states catalogue', function () {
    $this->browse(function (Browser $browser) {
        $browser->logout()
            ->loginAs($this->admin)
            ->visit('/admin/document-states')
            ->pause(3000)
            ->assertPathIs('/admin/document-states');
    });
});
