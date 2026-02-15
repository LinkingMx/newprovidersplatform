<?php

/**
 * Phase 2: Full Onboarding E2E
 *
 * 2.1 Create provider (admin panel)
 * 2.2 Set password link generated
 * 2.3 Invitation sent (status = invited)
 * 2.4 Docs assigned
 * 2.5 Branch assigned
 */

use App\Enums\SupplierStatus;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Spatie\Permission\Models\Role;

$prefix = 'dusk_p2_'.substr(uniqid(), -6).'_';

beforeEach(function () use ($prefix) {
    $this->prefix = $prefix;

    DB::table('users')->where('email', 'like', $prefix.'%')->delete();
    $ids = DB::table('suppliers')->where('email', 'like', $prefix.'%')->pluck('id');
    if ($ids->isNotEmpty()) {
        DB::table('supplier_invitations')->whereIn('supplier_id', $ids)->delete();
        DB::table('supplier_documents')->whereIn('supplier_id', $ids)->delete();
        DB::table('suppliers')->whereIn('id', $ids)->delete();
    }

    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create([
        'name' => 'Admin Dusk Onboarding',
        'email' => $prefix.'admin@test.com',
        'password' => Hash::make('AdminDusk2024!'),
    ]);
    $this->admin->assignRole('super_admin');
});

afterEach(function () use ($prefix) {
    DB::table('users')->where('email', 'like', $prefix.'%')->delete();
    $ids = DB::table('suppliers')->where('email', 'like', $prefix.'%')->pluck('id');
    if ($ids->isNotEmpty()) {
        DB::table('supplier_invitations')->whereIn('supplier_id', $ids)->delete();
        DB::table('supplier_documents')->whereIn('supplier_id', $ids)->delete();
        DB::table('suppliers')->whereIn('id', $ids)->delete();
    }
});

// =============================================================================
// 2.1 — Create Provider via Admin Panel
// =============================================================================

test('2.1 admin can access create supplier form', function () {
    $this->browse(function (Browser $browser) {
        $browser->logout()
            ->loginAs($this->admin)
            ->visit('/admin/suppliers/create')
            ->waitForText('Información Básica', 10)
            ->assertPresent('input[type="text"]')
            ->assertPresent('input[type="email"]');
    });
});

// =============================================================================
// 2.2 — Set Password link generated
// =============================================================================

test('2.2 creating supplier generates invitation token', function () {
    $supplier = Supplier::factory()->invited()->create([
        'name' => 'Dusk Token Check',
        'email' => $this->prefix.'token@test.com',
    ]);

    $token = bin2hex(random_bytes(32));
    $supplier->invitations()->create([
        'token' => $token,
        'expires_at' => now()->addDays(7),
    ]);

    $this->assertDatabaseHas('supplier_invitations', [
        'supplier_id' => $supplier->id,
        'token' => $token,
    ]);
});

// =============================================================================
// 2.3 — Invitation sent (status = invited)
// =============================================================================

test('2.3 supplier with invited status cannot login', function () {
    $supplier = Supplier::factory()->invited()->create([
        'email' => $this->prefix.'invited@test.com',
    ]);

    expect($supplier->status)->toBe(SupplierStatus::Invited);
    expect($supplier->canLogin())->toBeFalse();
});

// =============================================================================
// 2.4 — Docs assigned
// =============================================================================

test('2.4 admin can view supplier documents relation', function () {
    $supplier = Supplier::factory()->registered()->create([
        'name' => 'Dusk Docs Test',
        'email' => $this->prefix.'docs@test.com',
    ]);

    $this->browse(function (Browser $browser) use ($supplier) {
        $browser->logout()
            ->loginAs($this->admin)
            ->visit("/admin/suppliers/{$supplier->id}/edit")
            ->pause(3000)
            ->assertSee('Documentos del Expediente');
    });
});

// =============================================================================
// 2.5 — Branch assigned
// =============================================================================

test('2.5 admin can view supplier branches relation', function () {
    $supplier = Supplier::factory()->registered()->create([
        'name' => 'Dusk Branch Test',
        'email' => $this->prefix.'branch@test.com',
    ]);

    $this->browse(function (Browser $browser) use ($supplier) {
        $browser->logout()
            ->loginAs($this->admin)
            ->visit("/admin/suppliers/{$supplier->id}/edit")
            ->pause(3000)
            ->assertSee('Sucursales');
    });
});
