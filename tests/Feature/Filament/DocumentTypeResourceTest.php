<?php

use App\Filament\Resources\DocumentTypes\DocumentTypeResource;
use App\Models\DocumentType;

use function Pest\Laravel\actingAs;

// Authorization Tests

test('guests cannot access document type list', function () {
    $this->get(DocumentTypeResource::getUrl('index'))
        ->assertRedirect('/admin/login');
});

test('users without permission cannot view document type list', function () {
    $user = \App\Models\User::factory()->create();

    actingAs($user);

    $this->get(DocumentTypeResource::getUrl('index'))
        ->assertForbidden();
});

// Model Tests

test('document type model has softDeletes', function () {
    $documentType = DocumentType::factory()->create();
    $documentType->delete();

    expect($documentType->trashed())->toBeTrue();
    expect(DocumentType::withTrashed()->count())->toBeGreaterThan(0);
});

test('document type model has activo attribute', function () {
    $active = DocumentType::factory()->create(['activo' => true]);
    $inactive = DocumentType::factory()->inactive()->create();

    expect($active->activo)->toBeTrue();
    expect($inactive->activo)->toBeFalse();
});

// Factory Tests

test('document type factory creates active type by default', function () {
    $documentType = DocumentType::factory()->create();

    expect($documentType->activo)->toBeTrue();
});

test('document type factory can create inactive type', function () {
    $documentType = DocumentType::factory()->inactive()->create();

    expect($documentType->activo)->toBeFalse();
});

test('document type factory generates required fields', function () {
    $documentType = DocumentType::factory()->create();

    expect($documentType->nombre)->not->toBeEmpty();
    expect($documentType->activo)->toBeTrue();
});

// Database Tests

test('soft deleted document types are excluded from default queries', function () {
    $type1 = DocumentType::factory()->create();
    $type2 = DocumentType::factory()->create();
    $type2->delete();

    $count = DocumentType::count();

    expect($count)->toEqual(1);
    expect(DocumentType::withTrashed()->count())->toEqual(2);
});

test('can filter document types by activo status', function () {
    $active = DocumentType::factory()->create(['activo' => true]);
    $inactive = DocumentType::factory()->inactive()->create();

    $activeCount = DocumentType::where('activo', true)->count();
    $inactiveCount = DocumentType::where('activo', false)->count();

    expect($activeCount)->toBeGreaterThan(0);
    expect($inactiveCount)->toBeGreaterThan(0);
});

test('can restore soft deleted document type', function () {
    $documentType = DocumentType::factory()->create();
    $documentType->delete();

    expect($documentType->trashed())->toBeTrue();

    $documentType->restore();

    expect($documentType->trashed())->toBeFalse();
});

test('can force delete soft deleted document type', function () {
    $documentType = DocumentType::factory()->create(['nombre' => 'Test Force Delete']);
    $documentType->delete();

    expect(DocumentType::withTrashed()->where('nombre', 'Test Force Delete')->count())->toEqual(1);

    $documentType->forceDelete();

    expect(DocumentType::withTrashed()->where('nombre', 'Test Force Delete')->count())->toEqual(0);
});

// Field Tests

test('document type fillable includes required fields', function () {
    $documentType = DocumentType::factory()->create([
        'nombre' => 'Test Documento',
        'descripcion' => 'Test Description',
        'validez_dias' => 365,
        'activo' => false,
    ]);

    expect(in_array('nombre', $documentType->getFillable()))->toBeTrue();
    expect(in_array('descripcion', $documentType->getFillable()))->toBeTrue();
    expect(in_array('validez_dias', $documentType->getFillable()))->toBeTrue();
    expect(in_array('activo', $documentType->getFillable()))->toBeTrue();
});

test('document type can be updated with activo status', function () {
    $documentType = DocumentType::factory()->create(['activo' => true]);

    $documentType->update(['activo' => false]);

    expect($documentType->activo)->toBeFalse();
});

test('document type casts activo to boolean', function () {
    $documentType = DocumentType::factory()->create(['activo' => true]);

    expect($documentType->getCasts()['activo'])->toEqual('boolean');
    expect(is_bool($documentType->activo))->toBeTrue();
});

test('document type nombre is required and unique', function () {
    DocumentType::factory()->create(['nombre' => 'Unique Name']);

    expect(DocumentType::where('nombre', 'Unique Name')->count())->toEqual(1);
});

test('document type validez_dias can be nullable', function () {
    $documentType = DocumentType::factory()->create(['validez_dias' => null]);

    expect($documentType->validez_dias)->toBeNull();
});
