<?php

use App\Filament\Resources\ProviderTypes\Pages\EditProviderType;
use App\Filament\Resources\ProviderTypes\RelationManagers\DocumentTypesRelationManager;
use App\Models\DocumentType;
use App\Models\ProviderType;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('can render the document types relation manager', function () {
    $providerType = ProviderType::factory()->create();

    Livewire::test(DocumentTypesRelationManager::class, [
        'ownerRecord' => $providerType,
        'pageClass' => EditProviderType::class,
    ])->assertOk();
});

it('can list attached document types', function () {
    $providerType = ProviderType::factory()->create();
    $documentTypes = DocumentType::factory()->count(3)->create();

    foreach ($documentTypes as $dt) {
        $providerType->documentTypes()->attach($dt->id, ['obligatorio' => true]);
    }

    Livewire::test(DocumentTypesRelationManager::class, [
        'ownerRecord' => $providerType,
        'pageClass' => EditProviderType::class,
    ])->assertCanSeeTableRecords($documentTypes);
});

it('shows empty state when no document types attached', function () {
    $providerType = ProviderType::factory()->create();

    Livewire::test(DocumentTypesRelationManager::class, [
        'ownerRecord' => $providerType,
        'pageClass' => EditProviderType::class,
    ])->assertCountTableRecords(0);
});

it('displays obligatorio column as boolean icon', function () {
    $providerType = ProviderType::factory()->create();
    $documentType = DocumentType::factory()->create();
    $providerType->documentTypes()->attach($documentType->id, ['obligatorio' => true]);

    Livewire::test(DocumentTypesRelationManager::class, [
        'ownerRecord' => $providerType,
        'pageClass' => EditProviderType::class,
    ])->assertCanSeeTableRecords([$documentType]);
});

it('provider type has document types relationship', function () {
    $providerType = ProviderType::factory()->create();
    $documentTypes = DocumentType::factory()->count(2)->create();

    foreach ($documentTypes as $dt) {
        $providerType->documentTypes()->attach($dt->id, ['obligatorio' => true]);
    }

    expect($providerType->documentTypes)->toHaveCount(2);
});

it('pivot stores obligatorio field', function () {
    $providerType = ProviderType::factory()->create();
    $mandatory = DocumentType::factory()->create();
    $optional = DocumentType::factory()->create();

    $providerType->documentTypes()->attach($mandatory->id, ['obligatorio' => true]);
    $providerType->documentTypes()->attach($optional->id, ['obligatorio' => false]);

    $mandatoryPivot = $providerType->documentTypes()->where('document_type_id', $mandatory->id)->first();
    $optionalPivot = $providerType->documentTypes()->where('document_type_id', $optional->id)->first();

    expect($mandatoryPivot->pivot->obligatorio)->toBeTruthy()
        ->and($optionalPivot->pivot->obligatorio)->toBeFalsy();
});

it('document type has inverse provider types relationship', function () {
    $documentType = DocumentType::factory()->create();
    $providerTypes = ProviderType::factory()->count(2)->create();

    foreach ($providerTypes as $pt) {
        $documentType->providerTypes()->attach($pt->id, ['obligatorio' => true]);
    }

    expect($documentType->providerTypes)->toHaveCount(2);
});

it('can search document types in table', function () {
    $providerType = ProviderType::factory()->create();
    $target = DocumentType::factory()->create(['nombre' => 'Constancia Fiscal']);
    $other = DocumentType::factory()->create(['nombre' => 'Acta Constitutiva']);

    $providerType->documentTypes()->attach($target->id, ['obligatorio' => true]);
    $providerType->documentTypes()->attach($other->id, ['obligatorio' => true]);

    Livewire::test(DocumentTypesRelationManager::class, [
        'ownerRecord' => $providerType,
        'pageClass' => EditProviderType::class,
    ])
        ->searchTable('Constancia')
        ->assertCanSeeTableRecords([$target])
        ->assertCanNotSeeTableRecords([$other]);
});
