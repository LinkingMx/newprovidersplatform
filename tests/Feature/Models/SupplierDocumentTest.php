<?php

use App\Models\DocumentState;
use App\Models\DocumentType;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use App\Models\User;

beforeEach(function () {
    DocumentState::create([
        'nombre' => 'Pendiente',
        'etiqueta' => 'Pendiente',
        'color' => 'gray',
        'icono' => 'heroicon-o-clock',
        'por_defecto' => true,
        'completado' => false,
        'transiciones_permitidas' => ['En Revisión'],
    ]);

    DocumentState::create([
        'nombre' => 'Aprobado',
        'etiqueta' => 'Aprobado',
        'color' => 'green',
        'icono' => 'heroicon-o-check-circle',
        'por_defecto' => false,
        'completado' => true,
        'transiciones_permitidas' => [],
    ]);
});

it('can create a supplier document', function () {
    $supplier = Supplier::factory()->create();
    $documentType = DocumentType::factory()->create();
    $state = DocumentState::where('por_defecto', true)->first();

    $document = SupplierDocument::create([
        'supplier_id' => $supplier->id,
        'document_type_id' => $documentType->id,
        'document_state_id' => $state->id,
    ]);

    expect($document)->not->toBeNull()
        ->and($document->supplier_id)->toBe($supplier->id)
        ->and($document->document_type_id)->toBe($documentType->id)
        ->and($document->document_state_id)->toBe($state->id);
});

it('belongs to a supplier', function () {
    $document = SupplierDocument::factory()->create();

    expect($document->supplier)->toBeInstanceOf(Supplier::class);
});

it('belongs to a document type', function () {
    $document = SupplierDocument::factory()->create();

    expect($document->documentType)->toBeInstanceOf(DocumentType::class);
});

it('belongs to a document state', function () {
    $document = SupplierDocument::factory()->create();

    expect($document->documentState)->toBeInstanceOf(DocumentState::class);
});

it('can have a reviewer', function () {
    $reviewer = User::factory()->create();
    $document = SupplierDocument::factory()->create([
        'reviewed_by' => $reviewer->id,
        'reviewed_at' => now(),
    ]);

    expect($document->reviewer)->toBeInstanceOf(User::class)
        ->and($document->reviewer->id)->toBe($reviewer->id);
});

it('has nullable reviewer when not reviewed', function () {
    $document = SupplierDocument::factory()->create();

    expect($document->reviewer)->toBeNull()
        ->and($document->reviewed_at)->toBeNull();
});

it('uses soft deletes', function () {
    $document = SupplierDocument::factory()->create();
    $document->delete();

    expect($document->trashed())->toBeTrue()
        ->and(SupplierDocument::withTrashed()->count())->toBeGreaterThan(0);
});

it('factory creates document with default state', function () {
    $document = SupplierDocument::factory()->create();
    $defaultState = DocumentState::where('por_defecto', true)->first();

    expect($document->document_state_id)->toBe($defaultState->id);
});

it('factory uploaded state sets file info', function () {
    $document = SupplierDocument::factory()->uploaded()->create();

    expect($document->archivo_path)->not->toBeNull()
        ->and($document->archivo_nombre)->not->toBeNull()
        ->and($document->uploaded_at)->not->toBeNull();
});

it('factory withExpiry state sets fecha_vencimiento', function () {
    $document = SupplierDocument::factory()->withExpiry()->create();

    expect($document->fecha_vencimiento)->not->toBeNull();
});

it('casts dates correctly', function () {
    $document = SupplierDocument::factory()->uploaded()->withExpiry()->create([
        'reviewed_at' => now(),
    ]);

    expect($document->fecha_vencimiento)->toBeInstanceOf(Carbon\CarbonInterface::class)
        ->and($document->uploaded_at)->toBeInstanceOf(Carbon\CarbonInterface::class)
        ->and($document->reviewed_at)->toBeInstanceOf(Carbon\CarbonInterface::class);
});

it('enforces unique supplier and document type combination', function () {
    $supplier = Supplier::factory()->create();
    $documentType = DocumentType::factory()->create();
    $state = DocumentState::where('por_defecto', true)->first();

    SupplierDocument::create([
        'supplier_id' => $supplier->id,
        'document_type_id' => $documentType->id,
        'document_state_id' => $state->id,
    ]);

    expect(fn () => SupplierDocument::create([
        'supplier_id' => $supplier->id,
        'document_type_id' => $documentType->id,
        'document_state_id' => $state->id,
    ]))->toThrow(Exception::class);
});

it('supplier has documents relationship', function () {
    $supplier = Supplier::factory()->create();
    SupplierDocument::factory()->count(3)->create(['supplier_id' => $supplier->id]);

    expect($supplier->documents)->toHaveCount(3);
});

it('document type has supplier documents relationship', function () {
    $documentType = DocumentType::factory()->create();
    SupplierDocument::factory()->count(2)->create(['document_type_id' => $documentType->id]);

    expect($documentType->supplierDocuments)->toHaveCount(2);
});
