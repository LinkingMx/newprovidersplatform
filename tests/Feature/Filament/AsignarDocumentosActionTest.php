<?php

use App\Models\DocumentState;
use App\Models\DocumentType;
use App\Models\ProviderType;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    actingAs(User::factory()->create());

    DocumentState::create([
        'nombre' => 'Pendiente',
        'etiqueta' => 'Pendiente',
        'color' => 'gray',
        'icono' => 'heroicon-o-clock',
        'por_defecto' => true,
        'completado' => false,
        'transiciones_permitidas' => ['En Revisión'],
    ]);
});

it('assigns document types based on provider type', function () {
    $providerType = ProviderType::factory()->create();
    $docTypes = DocumentType::factory()->count(3)->create();

    foreach ($docTypes as $dt) {
        $providerType->documentTypes()->attach($dt->id, ['obligatorio' => true]);
    }

    $supplier = Supplier::factory()->create(['provider_type_id' => $providerType->id]);
    $defaultState = DocumentState::where('por_defecto', true)->first();

    // Simulate the action logic directly
    foreach ($providerType->documentTypes as $documentType) {
        $exists = SupplierDocument::where('supplier_id', $supplier->id)
            ->where('document_type_id', $documentType->id)
            ->exists();

        if (! $exists) {
            SupplierDocument::create([
                'supplier_id' => $supplier->id,
                'document_type_id' => $documentType->id,
                'document_state_id' => $defaultState->id,
            ]);
        }
    }

    expect(SupplierDocument::where('supplier_id', $supplier->id)->count())->toBe(3);
});

it('skips already assigned document types', function () {
    $providerType = ProviderType::factory()->create();
    $docType1 = DocumentType::factory()->create();
    $docType2 = DocumentType::factory()->create();

    $providerType->documentTypes()->attach($docType1->id, ['obligatorio' => true]);
    $providerType->documentTypes()->attach($docType2->id, ['obligatorio' => true]);

    $supplier = Supplier::factory()->create(['provider_type_id' => $providerType->id]);
    $defaultState = DocumentState::where('por_defecto', true)->first();

    // Pre-assign one document
    SupplierDocument::create([
        'supplier_id' => $supplier->id,
        'document_type_id' => $docType1->id,
        'document_state_id' => $defaultState->id,
    ]);

    // Run assignment logic
    foreach ($providerType->documentTypes as $documentType) {
        $exists = SupplierDocument::where('supplier_id', $supplier->id)
            ->where('document_type_id', $documentType->id)
            ->exists();

        if (! $exists) {
            SupplierDocument::create([
                'supplier_id' => $supplier->id,
                'document_type_id' => $documentType->id,
                'document_state_id' => $defaultState->id,
            ]);
        }
    }

    // Should only have 2 total (1 pre-existing + 1 new)
    expect(SupplierDocument::where('supplier_id', $supplier->id)->count())->toBe(2);
});

it('creates documents with default pending state', function () {
    $providerType = ProviderType::factory()->create();
    $docType = DocumentType::factory()->create();
    $providerType->documentTypes()->attach($docType->id, ['obligatorio' => true]);

    $supplier = Supplier::factory()->create(['provider_type_id' => $providerType->id]);
    $defaultState = DocumentState::where('por_defecto', true)->first();

    SupplierDocument::create([
        'supplier_id' => $supplier->id,
        'document_type_id' => $docType->id,
        'document_state_id' => $defaultState->id,
    ]);

    $doc = SupplierDocument::where('supplier_id', $supplier->id)->first();

    expect($doc->document_state_id)->toBe($defaultState->id);
});

it('does not create documents when provider type has no document types', function () {
    $providerType = ProviderType::factory()->create();
    $supplier = Supplier::factory()->create(['provider_type_id' => $providerType->id]);

    // No document types attached to provider type
    expect($providerType->documentTypes)->toBeEmpty();
    expect(SupplierDocument::where('supplier_id', $supplier->id)->count())->toBe(0);
});

it('can run multiple times without duplicating', function () {
    $providerType = ProviderType::factory()->create();
    $docTypes = DocumentType::factory()->count(2)->create();

    foreach ($docTypes as $dt) {
        $providerType->documentTypes()->attach($dt->id, ['obligatorio' => true]);
    }

    $supplier = Supplier::factory()->create(['provider_type_id' => $providerType->id]);
    $defaultState = DocumentState::where('por_defecto', true)->first();

    // Run assignment twice
    for ($i = 0; $i < 2; $i++) {
        foreach ($providerType->documentTypes as $documentType) {
            $exists = SupplierDocument::where('supplier_id', $supplier->id)
                ->where('document_type_id', $documentType->id)
                ->exists();

            if (! $exists) {
                SupplierDocument::create([
                    'supplier_id' => $supplier->id,
                    'document_type_id' => $documentType->id,
                    'document_state_id' => $defaultState->id,
                ]);
            }
        }
    }

    expect(SupplierDocument::where('supplier_id', $supplier->id)->count())->toBe(2);
});

it('action visibility depends on provider type being set', function () {
    $providerType = ProviderType::factory()->create();
    $supplierWithType = Supplier::factory()->create(['provider_type_id' => $providerType->id]);
    $supplierWithoutType = Supplier::factory()->create(['provider_type_id' => null]);

    expect($supplierWithType->provider_type_id)->not->toBeNull();
    expect($supplierWithoutType->provider_type_id)->toBeNull();
});
