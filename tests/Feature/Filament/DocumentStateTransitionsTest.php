<?php

use App\Models\DocumentState;
use App\Models\DocumentType;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    actingAs(User::factory()->create());

    // Seed all 4 document states with correct transitions
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
        'transiciones_permitidas' => ['Rechazado'],
    ]);

    DocumentState::create([
        'nombre' => 'Rechazado',
        'etiqueta' => 'Rechazado',
        'color' => 'red',
        'icono' => 'heroicon-o-x-circle',
        'por_defecto' => false,
        'completado' => false,
        'transiciones_permitidas' => ['En Revisión'],
    ]);
});

describe('State Transition Options (Section 6.4)', function () {
    it('Pendiente only allows transition to En Revisión', function () {
        $pendiente = DocumentState::where('nombre', 'Pendiente')->first();
        $allowed = $pendiente->transiciones_permitidas;

        expect($allowed)->toBe(['En Revisión'])
            ->and($allowed)->toHaveCount(1);

        // Verify the target state exists
        $targetStates = DocumentState::whereIn('nombre', $allowed)->pluck('nombre')->all();
        expect($targetStates)->toBe(['En Revisión']);
    });

    it('En Revisión allows transition to Aprobado and Rechazado', function () {
        $enRevision = DocumentState::where('nombre', 'En Revisión')->first();
        $allowed = $enRevision->transiciones_permitidas;

        expect($allowed)->toBe(['Aprobado', 'Rechazado'])
            ->and($allowed)->toHaveCount(2);
    });

    it('Aprobado only allows transition to Rechazado', function () {
        $aprobado = DocumentState::where('nombre', 'Aprobado')->first();
        $allowed = $aprobado->transiciones_permitidas;

        expect($allowed)->toBe(['Rechazado'])
            ->and($allowed)->toHaveCount(1);
    });

    it('Rechazado only allows transition to En Revisión', function () {
        $rechazado = DocumentState::where('nombre', 'Rechazado')->first();
        $allowed = $rechazado->transiciones_permitidas;

        expect($allowed)->toBe(['En Revisión'])
            ->and($allowed)->toHaveCount(1);
    });
});

describe('State Transition Execution (Section 6.5)', function () {
    it('can transition Pendiente → En Revisión with reviewer info', function () {
        $user = User::factory()->create();
        actingAs($user);

        $supplier = Supplier::factory()->create();
        $pendiente = DocumentState::where('nombre', 'Pendiente')->first();
        $enRevision = DocumentState::where('nombre', 'En Revisión')->first();

        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => $pendiente->id,
        ]);

        $document->update([
            'document_state_id' => $enRevision->id,
            'notas' => 'Recibido, iniciando revisión',
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
        ]);

        $document->refresh();

        expect($document->document_state_id)->toBe($enRevision->id)
            ->and($document->notas)->toBe('Recibido, iniciando revisión')
            ->and($document->reviewed_at)->not->toBeNull()
            ->and($document->reviewed_by)->toBe($user->id);
    });

    it('can transition En Revisión → Aprobado', function () {
        $user = User::factory()->create();
        actingAs($user);

        $supplier = Supplier::factory()->create();
        $enRevision = DocumentState::where('nombre', 'En Revisión')->first();
        $aprobado = DocumentState::where('nombre', 'Aprobado')->first();

        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => $enRevision->id,
        ]);

        $document->update([
            'document_state_id' => $aprobado->id,
            'notas' => 'Documento verificado y aprobado',
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
        ]);

        $document->refresh();

        expect($document->document_state_id)->toBe($aprobado->id)
            ->and($document->notas)->toBe('Documento verificado y aprobado');
    });

    it('can transition En Revisión → Rechazado with rejection notes', function () {
        $user = User::factory()->create();
        actingAs($user);

        $supplier = Supplier::factory()->create();
        $enRevision = DocumentState::where('nombre', 'En Revisión')->first();
        $rechazado = DocumentState::where('nombre', 'Rechazado')->first();

        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => $enRevision->id,
        ]);

        $document->update([
            'document_state_id' => $rechazado->id,
            'notas' => 'Documento ilegible, favor de subir en mejor resolución',
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
        ]);

        $document->refresh();

        expect($document->document_state_id)->toBe($rechazado->id)
            ->and($document->notas)->toBe('Documento ilegible, favor de subir en mejor resolución')
            ->and($document->reviewed_by)->toBe($user->id);
    });

    it('can transition Rechazado → En Revisión after re-upload', function () {
        $user = User::factory()->create();
        actingAs($user);

        $supplier = Supplier::factory()->create();
        $rechazado = DocumentState::where('nombre', 'Rechazado')->first();
        $enRevision = DocumentState::where('nombre', 'En Revisión')->first();

        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => $rechazado->id,
            'notas' => 'Documento ilegible',
        ]);

        $document->update([
            'document_state_id' => $enRevision->id,
            'notas' => 'Nueva versión recibida, revisando',
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
        ]);

        $document->refresh();

        expect($document->document_state_id)->toBe($enRevision->id)
            ->and($document->notas)->toBe('Nueva versión recibida, revisando');
    });

    it('can revoke: Aprobado → Rechazado', function () {
        $user = User::factory()->create();
        actingAs($user);

        $supplier = Supplier::factory()->create();
        $aprobado = DocumentState::where('nombre', 'Aprobado')->first();
        $rechazado = DocumentState::where('nombre', 'Rechazado')->first();

        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => $aprobado->id,
        ]);

        $document->update([
            'document_state_id' => $rechazado->id,
            'notas' => 'Documento revocado por inconsistencias detectadas',
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
        ]);

        $document->refresh();

        expect($document->document_state_id)->toBe($rechazado->id)
            ->and($document->notas)->toBe('Documento revocado por inconsistencias detectadas');
    });
});

describe('DocumentState Model Integrity', function () {
    it('has exactly 4 document states seeded', function () {
        expect(DocumentState::count())->toBe(4);
    });

    it('has exactly one default state', function () {
        $defaults = DocumentState::where('por_defecto', true)->get();

        expect($defaults)->toHaveCount(1)
            ->and($defaults->first()->nombre)->toBe('Pendiente');
    });

    it('has exactly one completed state', function () {
        $completed = DocumentState::where('completado', true)->get();

        expect($completed)->toHaveCount(1)
            ->and($completed->first()->nombre)->toBe('Aprobado');
    });

    it('all transition targets reference valid state names', function () {
        $allNames = DocumentState::pluck('nombre')->all();

        DocumentState::all()->each(function ($state) use ($allNames) {
            foreach ($state->transiciones_permitidas as $target) {
                expect($allNames)->toContain($target);
            }
        });
    });
});
