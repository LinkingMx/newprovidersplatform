<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

// Setup: Create roles and permissions for testing
beforeEach(function () {
    $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'panel_user', 'guard_name' => 'web']);

    // Create all necessary permissions
    Permission::firstOrCreate(['name' => 'ViewAny:User', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'View:User', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Create:User', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Update:User', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Delete:User', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'Restore:User', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'ForceDelete:User', 'guard_name' => 'web']);

    // Assign all permissions to super_admin
    $superAdminRole->givePermissionTo([
        'ViewAny:User',
        'View:User',
        'Create:User',
        'Update:User',
        'Delete:User',
        'Restore:User',
        'ForceDelete:User',
    ]);
});

// Authorization Tests

test('guests cannot access user list', function () {
    $this->get(UserResource::getUrl('index'))
        ->assertRedirect('/admin/login');
});

test('users without permission cannot view user list', function () {
    $user = User::factory()->create();

    actingAs($user);

    $this->get(UserResource::getUrl('index'))
        ->assertForbidden();
});

// Model Tests

test('user model has softDeletes', function () {
    $user = User::factory()->create();
    $user->delete();

    expect($user->trashed())->toBeTrue();
    expect(User::withTrashed()->count())->toBeGreaterThan(0);
});

test('user model has active attribute', function () {
    $activeUser = User::factory()->create(['active' => true]);
    $inactiveUser = User::factory()->inactive()->create();

    expect($activeUser->active)->toBeTrue();
    expect($inactiveUser->active)->toBeFalse();
});

test('user has roles relationship', function () {
    $user = User::factory()->create();

    expect($user->roles)->toBeTruthy();
});

// Factory Tests

test('user factory creates active user by default', function () {
    $user = User::factory()->create();

    expect($user->active)->toBeTrue();
});

test('user factory can create inactive user', function () {
    $user = User::factory()->inactive()->create();

    expect($user->active)->toBeFalse();
});

// Database Tests

test('soft deleted users are excluded from default queries', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user2->delete();

    $count = User::count();

    expect($count)->toEqual(1);
    expect(User::withTrashed()->count())->toEqual(2);
});

test('can filter users by active status', function () {
    $active = User::factory()->create(['active' => true]);
    $inactive = User::factory()->inactive()->create();

    $activeCount = User::where('active', true)->count();
    $inactiveCount = User::where('active', false)->count();

    expect($activeCount)->toBeGreaterThan(0);
    expect($inactiveCount)->toBeGreaterThan(0);
});

test('can restore soft deleted user', function () {
    $user = User::factory()->create();
    $user->delete();

    expect($user->trashed())->toBeTrue();

    $user->restore();

    expect($user->trashed())->toBeFalse();
});

test('can force delete soft deleted user', function () {
    $user = User::factory()->create(['email' => 'force-delete@test.com']);
    $user->delete();

    expect(User::withTrashed()->where('email', 'force-delete@test.com')->count())->toEqual(1);

    $user->forceDelete();

    expect(User::withTrashed()->where('email', 'force-delete@test.com')->count())->toEqual(0);
});

// Policy Tests

test('user has policy methods available', function () {
    $user = User::factory()->create();

    expect(method_exists($user, 'can'))->toBeTrue();
});

// User Field Tests

test('user fillable includes active field', function () {
    $user = User::factory()->create(['active' => false]);

    expect(in_array('active', $user->getFillable()))->toBeTrue();
    expect($user->active)->toBeFalse();
});

test('user can be updated with active status', function () {
    $user = User::factory()->create(['active' => true]);

    $user->update(['active' => false]);

    expect($user->active)->toBeFalse();
});

test('user casts active to boolean', function () {
    $user = User::factory()->create(['active' => true]);

    expect($user->getCasts()['active'])->toEqual('boolean');
    expect(is_bool($user->active))->toBeTrue();
});

// Filament Resource Tests

test('admin user has correct permissions assigned', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    expect($admin->can('ViewAny:User'))->toBeTrue();
    expect($admin->can('Create:User'))->toBeTrue();
    expect($admin->can('Update:User'))->toBeTrue();
    expect($admin->can('Delete:User'))->toBeTrue();
});

test('can create new active user through filament', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'active' => true,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect(UserResource::getUrl('index'));

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'newuser@example.com',
        'active' => true,
    ]);
});

test('inactive users are marked correctly in filament', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $activeUser = User::factory()->create(['active' => true]);
    $inactiveUser = User::factory()->inactive()->create();

    actingAs($admin);

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords([$activeUser, $inactiveUser]);
});

test('email must be unique in filament form', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $existingUser = User::factory()->create(['email' => 'taken@example.com']);

    actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Another User',
            'email' => 'taken@example.com',
            'password' => 'password123',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

test('password is required on user creation', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['password' => 'required']);
});

test('password is optional on user edit', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $user = User::factory()->create();
    $oldPassword = $user->password;

    actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'name' => 'Updated Name',
            'email' => $user->email,
            'password' => null,
        ])
        ->call('save')
        ->assertNotified()
        ->assertRedirect(UserResource::getUrl('index'));

    $user->refresh();
    expect($user->password)->toBe($oldPassword);
});

test('resource has spanish labels configured', function () {
    expect(UserResource::getModelLabel())->toBe('usuario');
    expect(UserResource::getPluralModelLabel())->toBe('usuarios');
    expect(UserResource::getNavigationLabel())->toBe('Usuarios');
});
