<?php

use App\Models\DocumentState;
use App\Models\DocumentType;
use App\Models\ProviderType;
use App\Models\User;

it('runs install command successfully', function () {
    $this->artisan('app:install')
        ->assertSuccessful();
});

it('creates all document states', function () {
    $this->artisan('app:install');

    expect(DocumentState::count())->toBe(4)
        ->and(DocumentState::pluck('nombre')->sort()->values()->toArray())
        ->toBe(['Aprobado', 'En Revisión', 'Pendiente', 'Rechazado']);
});

it('creates base document types', function () {
    $this->artisan('app:install');

    expect(DocumentType::where('nombre', 'Constancia de situación fiscal')->exists())->toBeTrue()
        ->and(DocumentType::where('nombre', 'Comprobante de domicilio')->exists())->toBeTrue();
});

it('creates provider types with document types attached', function () {
    $this->artisan('app:install');

    $pfae = ProviderType::where('nombre', 'Persona Física con Actividad Empresarial')->first();
    $pm = ProviderType::where('nombre', 'Persona Moral')->first();

    expect($pfae)->not->toBeNull()
        ->and($pm)->not->toBeNull()
        ->and($pfae->documentTypes)->toHaveCount(2)
        ->and($pm->documentTypes)->toHaveCount(2);

    // All documents are obligatory
    $pfae->documentTypes->each(function ($dt) {
        expect((bool) $dt->pivot->obligatorio)->toBeTrue();
    });
});

it('creates admin user with super_admin role', function () {
    $this->artisan('app:install');

    $admin = User::where('email', 'admin@grupocosteno.com')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->name)->toBe('Administrador')
        ->and($admin->active)->toBeTrue()
        ->and($admin->hasRole('super_admin'))->toBeTrue();
});

it('is idempotent when run multiple times', function () {
    $this->artisan('app:install')->assertSuccessful();
    $this->artisan('app:install')->assertSuccessful();

    expect(DocumentState::count())->toBe(4)
        ->and(DocumentType::where('nombre', 'Constancia de situación fiscal')->count())->toBe(1)
        ->and(ProviderType::where('nombre', 'Persona Moral')->count())->toBe(1)
        ->and(User::where('email', 'admin@grupocosteno.com')->count())->toBe(1);
});
