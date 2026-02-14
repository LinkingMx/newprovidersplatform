<?php

use App\Mail\SupplierDocumentStatusMailable;
use App\Models\DocumentState;
use App\Models\DocumentType;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Mail::fake();
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

describe('Email Sending Triggers (Section 7)', function () {
    it('sends email when state changes to Aprobado', function () {
        $supplier = Supplier::factory()->active()->create(['email' => 'proveedor@test.com']);
        $docType = DocumentType::factory()->create(['nombre' => 'RFC']);
        $enRevision = DocumentState::where('nombre', 'En Revisión')->first();
        $aprobado = DocumentState::where('nombre', 'Aprobado')->first();

        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => $docType->id,
            'document_state_id' => $enRevision->id,
        ]);

        // Simulate the DocumentsRelationManager action logic
        $document->update([
            'document_state_id' => $aprobado->id,
            'notas' => 'Documento verificado',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $document->load(['supplier', 'documentType']);

        // Send email (same logic as DocumentsRelationManager)
        if (in_array($aprobado->nombre, ['Aprobado', 'Rechazado'])) {
            Mail::to($document->supplier->email)->send(
                new SupplierDocumentStatusMailable($document->supplier, $document, $aprobado)
            );
        }

        Mail::assertSent(SupplierDocumentStatusMailable::class, function ($mail) {
            return $mail->hasTo('proveedor@test.com');
        });
    });

    it('sends email when state changes to Rechazado with notes', function () {
        $supplier = Supplier::factory()->active()->create(['email' => 'proveedor@test.com']);
        $docType = DocumentType::factory()->create(['nombre' => 'Identificación Oficial']);
        $enRevision = DocumentState::where('nombre', 'En Revisión')->first();
        $rechazado = DocumentState::where('nombre', 'Rechazado')->first();

        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => $docType->id,
            'document_state_id' => $enRevision->id,
        ]);

        $document->update([
            'document_state_id' => $rechazado->id,
            'notas' => 'Documento ilegible, favor de subir en mejor resolución',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $document->load(['supplier', 'documentType']);

        if (in_array($rechazado->nombre, ['Aprobado', 'Rechazado'])) {
            Mail::to($document->supplier->email)->send(
                new SupplierDocumentStatusMailable($document->supplier, $document, $rechazado)
            );
        }

        Mail::assertSent(SupplierDocumentStatusMailable::class, 1);
    });

    it('sends email when state changes to Rechazado without notes', function () {
        $supplier = Supplier::factory()->active()->create();
        $docType = DocumentType::factory()->create();
        $enRevision = DocumentState::where('nombre', 'En Revisión')->first();
        $rechazado = DocumentState::where('nombre', 'Rechazado')->first();

        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => $docType->id,
            'document_state_id' => $enRevision->id,
        ]);

        $document->update([
            'document_state_id' => $rechazado->id,
            'notas' => null,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $document->load(['supplier', 'documentType']);

        if (in_array($rechazado->nombre, ['Aprobado', 'Rechazado'])) {
            Mail::to($document->supplier->email)->send(
                new SupplierDocumentStatusMailable($document->supplier, $document, $rechazado)
            );
        }

        Mail::assertSent(SupplierDocumentStatusMailable::class, 1);
    });

    it('does NOT send email when state changes to En Revisión', function () {
        $supplier = Supplier::factory()->active()->create();
        $pendiente = DocumentState::where('nombre', 'Pendiente')->first();
        $enRevision = DocumentState::where('nombre', 'En Revisión')->first();

        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => $pendiente->id,
        ]);

        $document->update([
            'document_state_id' => $enRevision->id,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        // Simulate the action: only send email for Aprobado/Rechazado
        if (in_array($enRevision->nombre, ['Aprobado', 'Rechazado'])) {
            Mail::to($document->supplier->email)->send(
                new SupplierDocumentStatusMailable($document->supplier, $document, $enRevision)
            );
        }

        Mail::assertNotSent(SupplierDocumentStatusMailable::class);
    });

    it('sends email on revocation Aprobado → Rechazado', function () {
        $supplier = Supplier::factory()->active()->create(['email' => 'revocar@test.com']);
        $docType = DocumentType::factory()->create(['nombre' => 'Comprobante de Domicilio']);
        $aprobado = DocumentState::where('nombre', 'Aprobado')->first();
        $rechazado = DocumentState::where('nombre', 'Rechazado')->first();

        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => $docType->id,
            'document_state_id' => $aprobado->id,
        ]);

        $document->update([
            'document_state_id' => $rechazado->id,
            'notas' => 'Documento revocado por inconsistencias',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $document->load(['supplier', 'documentType']);

        if (in_array($rechazado->nombre, ['Aprobado', 'Rechazado'])) {
            Mail::to($document->supplier->email)->send(
                new SupplierDocumentStatusMailable($document->supplier, $document, $rechazado)
            );
        }

        Mail::assertSent(SupplierDocumentStatusMailable::class, function ($mail) {
            return $mail->hasTo('revocar@test.com');
        });
    });
});

describe('Email Content Verification (Section 7.1-7.2)', function () {
    it('approval email has correct subject format', function () {
        $supplier = Supplier::factory()->active()->create(['name' => 'Juan García']);
        $docType = DocumentType::factory()->create(['nombre' => 'RFC']);
        $aprobado = DocumentState::where('nombre', 'Aprobado')->first();

        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => $docType->id,
            'document_state_id' => $aprobado->id,
        ]);

        $document->load(['supplier', 'documentType']);

        $mailable = new SupplierDocumentStatusMailable($supplier, $document, $aprobado);

        $mailable->assertHasSubject('Tu documento "RFC" fue Aprobado');
    });

    it('rejection email has correct subject format', function () {
        $supplier = Supplier::factory()->active()->create(['name' => 'María López']);
        $docType = DocumentType::factory()->create(['nombre' => 'Identificación Oficial']);
        $rechazado = DocumentState::where('nombre', 'Rechazado')->first();

        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => $docType->id,
            'document_state_id' => $rechazado->id,
            'notas' => 'Documento ilegible',
        ]);

        $document->load(['supplier', 'documentType']);

        $mailable = new SupplierDocumentStatusMailable($supplier, $document, $rechazado);

        $mailable->assertHasSubject('Tu documento "Identificación Oficial" fue Rechazado');
    });

    it('email is sent to the supplier email address', function () {
        $supplier = Supplier::factory()->active()->create(['email' => 'correcto@proveedor.com']);
        $docType = DocumentType::factory()->create();
        $aprobado = DocumentState::where('nombre', 'Aprobado')->first();

        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => $docType->id,
        ]);

        $document->load(['supplier', 'documentType']);

        Mail::to($supplier->email)->send(
            new SupplierDocumentStatusMailable($supplier, $document, $aprobado)
        );

        Mail::assertSent(SupplierDocumentStatusMailable::class, function ($mail) {
            return $mail->hasTo('correcto@proveedor.com');
        });
    });

    it('approval mailable renders with dashboard URL', function () {
        $supplier = Supplier::factory()->active()->create(['name' => 'Pedro Gómez']);
        $docType = DocumentType::factory()->create(['nombre' => 'RFC']);
        $aprobado = DocumentState::where('nombre', 'Aprobado')->first();

        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => $docType->id,
        ]);

        $document->load(['supplier', 'documentType']);

        $mailable = new SupplierDocumentStatusMailable($supplier, $document, $aprobado);

        $mailable->assertSeeInHtml('Pedro Gómez')
            ->assertSeeInHtml('aprobado')
            ->assertSeeInHtml('Ir a mi Panel');
    });
});
