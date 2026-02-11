<?php

use App\Filament\Resources\DocumentStates\DocumentStateResource;
use App\States\DocumentState;

test('guests cannot access document state list', function () {
    $this->get(DocumentStateResource::getUrl('index'))
        ->assertRedirect('/admin/login');
});

test('resource cannot create states', function () {
    expect(DocumentStateResource::canCreate())->toBeFalse();
});

test('resource cannot edit states', function () {
    expect(DocumentStateResource::canEdit(null))->toBeFalse();
});

test('resource cannot delete states', function () {
    expect(DocumentStateResource::canDelete(null))->toBeFalse();
});

test('all four document states are defined', function () {
    $states = [
        DocumentState\Pendiente::class,
        DocumentState\EnRevision::class,
        DocumentState\Aprobado::class,
        DocumentState\Rechazado::class,
    ];

    foreach ($states as $stateClass) {
        expect($stateClass::getLabel())->not->toBeEmpty();
    }
});

test('pendiente is default state', function () {
    expect(DocumentState\Pendiente::isDefaultState())->toBeTrue();
});

test('pendiente is not completed state', function () {
    expect(DocumentState\Pendiente::isCompletedState())->toBeFalse();
});

test('aprobado is completed state', function () {
    expect(DocumentState\Aprobado::isCompletedState())->toBeTrue();
});

test('pendiente can transition to en revision', function () {
    $state = new DocumentState\Pendiente(null);
    expect($state->canTransitionTo(DocumentState\EnRevision::class))->toBeTrue();
});

test('en revision can transition to aprobado and rechazado', function () {
    $state = new DocumentState\EnRevision(null);
    expect($state->canTransitionTo(DocumentState\Aprobado::class))->toBeTrue();
    expect($state->canTransitionTo(DocumentState\Rechazado::class))->toBeTrue();
});

test('rechazado can transition to en revision', function () {
    $state = new DocumentState\Rechazado(null);
    expect($state->canTransitionTo(DocumentState\EnRevision::class))->toBeTrue();
});
