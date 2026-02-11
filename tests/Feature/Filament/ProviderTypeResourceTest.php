<?php

use App\Filament\Resources\ProviderTypes\ProviderTypeResource;
use App\Models\ProviderType;

use function Pest\Laravel\actingAs;

// Authorization Tests

test('guests cannot access provider type list', function () {
    $this->get(ProviderTypeResource::getUrl('index'))
        ->assertRedirect('/admin/login');
});

test('users without permission cannot view provider type list', function () {
    $user = \App\Models\User::factory()->create();

    actingAs($user);

    $this->get(ProviderTypeResource::getUrl('index'))
        ->assertForbidden();
});

// Model Tests

test('provider type model has softDeletes', function () {
    $providerType = ProviderType::factory()->create();
    $providerType->delete();

    expect($providerType->trashed())->toBeTrue();
    expect(ProviderType::withTrashed()->count())->toBeGreaterThan(0);
});

test('provider type model has activo attribute', function () {
    $active = ProviderType::factory()->create(['activo' => true]);
    $inactive = ProviderType::factory()->inactive()->create();

    expect($active->activo)->toBeTrue();
    expect($inactive->activo)->toBeFalse();
});

// Factory Tests

test('provider type factory creates active type by default', function () {
    $providerType = ProviderType::factory()->create();

    expect($providerType->activo)->toBeTrue();
});

test('provider type factory can create inactive type', function () {
    $providerType = ProviderType::factory()->inactive()->create();

    expect($providerType->activo)->toBeFalse();
});

test('provider type factory generates required fields', function () {
    $providerType = ProviderType::factory()->create();

    expect($providerType->nombre)->not->toBeEmpty();
    expect($providerType->activo)->toBeTrue();
});

// Database Tests

test('soft deleted provider types are excluded from default queries', function () {
    $type1 = ProviderType::factory()->create();
    $type2 = ProviderType::factory()->create();
    $type2->delete();

    $count = ProviderType::count();

    expect($count)->toEqual(1);
    expect(ProviderType::withTrashed()->count())->toEqual(2);
});

test('can filter provider types by activo status', function () {
    $active = ProviderType::factory()->create(['activo' => true]);
    $inactive = ProviderType::factory()->inactive()->create();

    $activeCount = ProviderType::where('activo', true)->count();
    $inactiveCount = ProviderType::where('activo', false)->count();

    expect($activeCount)->toBeGreaterThan(0);
    expect($inactiveCount)->toBeGreaterThan(0);
});

test('can restore soft deleted provider type', function () {
    $providerType = ProviderType::factory()->create();
    $providerType->delete();

    expect($providerType->trashed())->toBeTrue();

    $providerType->restore();

    expect($providerType->trashed())->toBeFalse();
});

test('can force delete soft deleted provider type', function () {
    $providerType = ProviderType::factory()->create(['nombre' => 'Test Force Delete']);
    $providerType->delete();

    expect(ProviderType::withTrashed()->where('nombre', 'Test Force Delete')->count())->toEqual(1);

    $providerType->forceDelete();

    expect(ProviderType::withTrashed()->where('nombre', 'Test Force Delete')->count())->toEqual(0);
});

// Field Tests

test('provider type fillable includes required fields', function () {
    $providerType = ProviderType::factory()->create([
        'nombre' => 'Test Proveedor',
        'descripcion' => 'Test Description',
        'activo' => false,
    ]);

    expect(in_array('nombre', $providerType->getFillable()))->toBeTrue();
    expect(in_array('descripcion', $providerType->getFillable()))->toBeTrue();
    expect(in_array('activo', $providerType->getFillable()))->toBeTrue();
});

test('provider type can be updated with activo status', function () {
    $providerType = ProviderType::factory()->create(['activo' => true]);

    $providerType->update(['activo' => false]);

    expect($providerType->activo)->toBeFalse();
});

test('provider type casts activo to boolean', function () {
    $providerType = ProviderType::factory()->create(['activo' => true]);

    expect($providerType->getCasts()['activo'])->toEqual('boolean');
    expect(is_bool($providerType->activo))->toBeTrue();
});

test('provider type nombre is required and unique', function () {
    ProviderType::factory()->create(['nombre' => 'Unique Name']);

    expect(ProviderType::where('nombre', 'Unique Name')->count())->toEqual(1);
});

test('provider type descripcion can be nullable', function () {
    $providerType = ProviderType::factory()->create(['descripcion' => null]);

    expect($providerType->descripcion)->toBeNull();
});
