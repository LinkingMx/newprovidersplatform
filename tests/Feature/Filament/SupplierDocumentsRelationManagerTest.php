<?php

use App\Filament\Resources\Suppliers\Pages\EditSupplier;
use App\Filament\Resources\Suppliers\RelationManagers\DocumentsRelationManager;
use App\Models\DocumentState;
use App\Models\DocumentType;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use App\Models\User;
use Livewire\Livewire;

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

    DocumentState::create([
        'nombre' => 'En Revisión',
        'etiqueta' => 'En Revisión',
        'color' => 'blue',
        'icono' => 'heroicon-o-eye',
        'por_defecto' => false,
        'completado' => false,
        'transiciones_permitidas' => ['Aprobado', 'Rechazado'],
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

it('can render the documents relation manager', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(DocumentsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => EditSupplier::class,
    ])->assertOk();
});

it('can list supplier documents', function () {
    $supplier = Supplier::factory()->create();
    $documents = SupplierDocument::factory()->count(3)->create([
        'supplier_id' => $supplier->id,
    ]);

    Livewire::test(DocumentsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => EditSupplier::class,
    ])->assertCanSeeTableRecords($documents);
});

it('shows empty state when no documents', function () {
    $supplier = Supplier::factory()->create();

    Livewire::test(DocumentsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => EditSupplier::class,
    ])->assertCountTableRecords(0);
});

it('can update document state directly', function () {
    $user = User::factory()->create();
    actingAs($user);

    $supplier = Supplier::factory()->create();
    $document = SupplierDocument::factory()->create([
        'supplier_id' => $supplier->id,
    ]);

    $enRevision = DocumentState::where('nombre', 'En Revisión')->first();

    // Test updating state directly on the model
    $document->update([
        'document_state_id' => $enRevision->id,
        'notas' => 'Documento recibido, en proceso de revisión',
        'reviewed_at' => now(),
        'reviewed_by' => $user->id,
    ]);

    $document->refresh();

    expect($document->document_state_id)->toBe($enRevision->id)
        ->and($document->notas)->toBe('Documento recibido, en proceso de revisión')
        ->and($document->reviewed_at)->not->toBeNull()
        ->and($document->reviewed_by)->toBe($user->id);
});

it('records reviewer info on state change', function () {
    $user = User::factory()->create();

    $supplier = Supplier::factory()->create();
    $document = SupplierDocument::factory()->create([
        'supplier_id' => $supplier->id,
    ]);

    $aprobado = DocumentState::where('nombre', 'Aprobado')->first();

    $document->update([
        'document_state_id' => $aprobado->id,
        'reviewed_at' => now(),
        'reviewed_by' => $user->id,
    ]);

    $document->refresh();

    expect($document->reviewed_by)->toBe($user->id)
        ->and($document->reviewed_at)->not->toBeNull()
        ->and($document->document_state_id)->toBe($aprobado->id);
});

it('can search documents by document type name', function () {
    $supplier = Supplier::factory()->create();
    $constancia = DocumentType::factory()->create(['nombre' => 'Constancia Fiscal']);
    $acta = DocumentType::factory()->create(['nombre' => 'Acta Constitutiva']);

    $doc1 = SupplierDocument::factory()->create([
        'supplier_id' => $supplier->id,
        'document_type_id' => $constancia->id,
    ]);
    $doc2 = SupplierDocument::factory()->create([
        'supplier_id' => $supplier->id,
        'document_type_id' => $acta->id,
    ]);

    Livewire::test(DocumentsRelationManager::class, [
        'ownerRecord' => $supplier,
        'pageClass' => EditSupplier::class,
    ])
        ->searchTable('Constancia')
        ->assertCanSeeTableRecords([$doc1])
        ->assertCanNotSeeTableRecords([$doc2]);
});
