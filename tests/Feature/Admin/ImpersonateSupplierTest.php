<?php

use App\Http\Controllers\Admin\ImpersonateSupplierController;
use App\Models\Supplier;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::findOrCreate('super_admin', 'web');
});

test('admin con rol super_admin puede iniciar impersonación', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $supplier = Supplier::factory()->active()->create();

    $this->actingAs($admin)
        ->post(route('admin.suppliers.impersonate.start', ['supplier' => $supplier]))
        ->assertRedirect(route('dashboard'));

    expect(auth('supplier')->id())->toBe($supplier->id);
    expect(session(ImpersonateSupplierController::SESSION_KEY))->toBe($admin->id);
});

test('admin sin permiso recibe 403', function () {
    $admin = User::factory()->create();
    $supplier = Supplier::factory()->active()->create();

    $this->actingAs($admin)
        ->post(route('admin.suppliers.impersonate.start', ['supplier' => $supplier]))
        ->assertForbidden();

    expect(auth('supplier')->check())->toBeFalse();
});

test('no se puede anidar impersonación', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $supplier1 = Supplier::factory()->active()->create();
    $supplier2 = Supplier::factory()->active()->create();

    $this->actingAs($admin)
        ->post(route('admin.suppliers.impersonate.start', ['supplier' => $supplier1]))
        ->assertRedirect(route('dashboard'));

    $this->actingAs($admin)
        ->post(route('admin.suppliers.impersonate.start', ['supplier' => $supplier2]))
        ->assertStatus(409);
});

test('stop limpia el guard supplier y la session', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $supplier = Supplier::factory()->active()->create();

    $this->actingAs($admin)
        ->post(route('admin.suppliers.impersonate.start', ['supplier' => $supplier]));

    $this->post(route('admin.suppliers.impersonate.stop'))
        ->assertRedirect('/admin/suppliers');

    expect(auth('supplier')->check())->toBeFalse();
    expect(session()->has(ImpersonateSupplierController::SESSION_KEY))->toBeFalse();
});

test('logout del supplier también limpia la key de impersonación', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $supplier = Supplier::factory()->active()->create();

    $this->actingAs($admin)
        ->post(route('admin.suppliers.impersonate.start', ['supplier' => $supplier]));

    expect(session()->has(ImpersonateSupplierController::SESSION_KEY))->toBeTrue();

    $this->post('/supplier/auth/logout')
        ->assertRedirect('/admin/suppliers');

    expect(auth('supplier')->check())->toBeFalse();
    expect(session()->has(ImpersonateSupplierController::SESSION_KEY))->toBeFalse();
});

test('stop sin impersonación activa redirige a /admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->post(route('admin.suppliers.impersonate.stop'))
        ->assertRedirect('/admin');
});
