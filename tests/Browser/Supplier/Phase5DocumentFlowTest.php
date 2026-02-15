<?php

/**
 * Phase 5: Document Flow
 */

use App\Models\DocumentState;
use App\Models\DocumentType;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Spatie\Permission\Models\Role;

$prefix = 'dusk_p5_'.substr(uniqid(), -6).'_';

beforeEach(function () use ($prefix) {
    $this->prefix = $prefix;

    DB::table('users')->where('email', 'like', $prefix.'%')->delete();
    $ids = DB::table('suppliers')->where('email', 'like', $prefix.'%')->pluck('id');
    if ($ids->isNotEmpty()) {
        DB::table('supplier_documents')->whereIn('supplier_id', $ids)->delete();
        DB::table('suppliers')->whereIn('id', $ids)->delete();
    }

    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $this->admin = User::factory()->create([
        'name' => 'Admin Doc Flow',
        'email' => $prefix.'admin@test.com',
        'password' => Hash::make('AdminDusk2024!'),
    ]);
    $this->admin->assignRole('super_admin');

    $this->supplier = Supplier::factory()->profileCompleted()->create([
        'name' => 'Dusk Doc Supplier',
        'email' => $prefix.'doc@test.com',
        'password_hash' => Hash::make('DuskTest2024!'),
    ]);
});

afterEach(function () use ($prefix) {
    DB::table('users')->where('email', 'like', $prefix.'%')->delete();
    $ids = DB::table('suppliers')->where('email', 'like', $prefix.'%')->pluck('id');
    if ($ids->isNotEmpty()) {
        DB::table('supplier_documents')->whereIn('supplier_id', $ids)->delete();
        DB::table('suppliers')->whereIn('id', $ids)->delete();
    }
});

// =============================================================================
// 5.1 — Supplier sees documents on dashboard
// =============================================================================

test('5.1 supplier can see documents section on dashboard', function () {
    $docType = DocumentType::first();
    $defaultState = DocumentState::where('por_defecto', true)->first() ?? DocumentState::first();

    if (! $docType || ! $defaultState) {
        $docType = $docType ?? DocumentType::create(['nombre' => 'Test Doc Type', 'descripcion' => 'Test', 'activo' => true]);
        $defaultState = $defaultState ?? DocumentState::create(['nombre' => 'Pendiente', 'etiqueta' => 'Pendiente', 'color' => '#FFA500', 'por_defecto' => true]);
    }

    SupplierDocument::factory()->create([
        'supplier_id' => $this->supplier->id,
        'document_type_id' => $docType->id,
        'document_state_id' => $defaultState->id,
    ]);

    $this->browse(function (Browser $browser) use ($docType) {
        $browser->loginAs($this->supplier, 'supplier')
            ->visit('/dashboard')
            ->waitForText('Mis Documentos', 10)
            ->assertSee($docType->nombre);
    });
});

// =============================================================================
// 5.2 — Document state transitions
// =============================================================================

test('5.2a document state transitions: Pendiente → En Revisión → Aprobado', function () {
    $docType = DocumentType::first();
    $pendiente = DocumentState::where('nombre', 'Pendiente')->first();
    $enRevision = DocumentState::where('nombre', 'En Revisión')->first();
    $aprobado = DocumentState::where('nombre', 'Aprobado')->first();

    if (! $docType || ! $pendiente || ! $enRevision || ! $aprobado) {
        $this->markTestSkipped('Required DocumentStates not seeded.');
    }

    $doc = SupplierDocument::factory()->uploaded()->create([
        'supplier_id' => $this->supplier->id,
        'document_type_id' => $docType->id,
        'document_state_id' => $pendiente->id,
    ]);

    $doc->update(['document_state_id' => $enRevision->id, 'reviewed_by' => $this->admin->id, 'reviewed_at' => now()]);
    expect($doc->fresh()->documentState->nombre)->toBe('En Revisión');

    $doc->update(['document_state_id' => $aprobado->id]);
    expect($doc->fresh()->documentState->nombre)->toBe('Aprobado');
});

test('5.2b document state transitions: Pendiente → En Revisión → Rechazado', function () {
    $docType = DocumentType::first();
    $pendiente = DocumentState::where('nombre', 'Pendiente')->first();
    $rechazado = DocumentState::where('nombre', 'Rechazado')->first();

    if (! $docType || ! $pendiente || ! $rechazado) {
        $this->markTestSkipped('Required DocumentStates not seeded.');
    }

    $doc = SupplierDocument::factory()->uploaded()->create([
        'supplier_id' => $this->supplier->id,
        'document_type_id' => $docType->id,
        'document_state_id' => $pendiente->id,
    ]);

    $doc->update([
        'document_state_id' => $rechazado->id,
        'notas' => 'Documento ilegible',
        'reviewed_at' => now(),
        'reviewed_by' => $this->admin->id,
    ]);

    expect($doc->fresh()->documentState->nombre)->toBe('Rechazado');
    expect($doc->fresh()->notas)->toBe('Documento ilegible');
});

// =============================================================================
// 5.3 — Supplier sees rejected document
// =============================================================================

test('5.3a supplier sees rejected document on dashboard', function () {
    $docType = DocumentType::first();
    $rechazado = DocumentState::where('nombre', 'Rechazado')->first();

    if (! $docType || ! $rechazado) {
        $this->markTestSkipped('Required data not seeded.');
    }

    SupplierDocument::factory()->uploaded()->create([
        'supplier_id' => $this->supplier->id,
        'document_type_id' => $docType->id,
        'document_state_id' => $rechazado->id,
        'notas' => 'Documento ilegible',
    ]);

    $this->browse(function (Browser $browser) {
        $browser->loginAs($this->supplier, 'supplier')
            ->visit('/dashboard')
            ->waitForText('Mis Documentos', 10)
            ->assertSee('Rechazado');
    });
});

test('5.3b admin can view documents relation manager in edit page', function () {
    $this->browse(function (Browser $browser) {
        $browser->logout()
            ->loginAs($this->admin)
            ->visit("/admin/suppliers/{$this->supplier->id}/edit")
            ->pause(3000)
            ->assertSee('Documentos del Expediente');
    });
});
