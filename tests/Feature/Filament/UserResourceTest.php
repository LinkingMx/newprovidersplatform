<?php

use App\Filament\Resources\Users\UserResource;
use App\Models\User;

use function Pest\Laravel\actingAs;

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
