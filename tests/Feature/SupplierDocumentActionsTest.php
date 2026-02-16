<?php

use App\Models\DocumentState;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use App\Policies\SupplierDocumentPolicy;
use Database\Seeders\DocumentStateSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(DocumentStateSeeder::class);
});

describe('Document preview', function () {
    it('allows supplier to preview their own uploaded document', function () {
        Storage::fake('local');
        $supplier = Supplier::factory()->active()->create();
        $path = "supplier-documents/{$supplier->id}/test.pdf";
        Storage::disk('local')->put($path, 'PDF content');

        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'archivo_path' => $path,
            'archivo_nombre' => 'constancia.pdf',
            'document_state_id' => DocumentState::EN_REVISION,
        ]);

        $this->actingAs($supplier, 'supplier')
            ->get("/supplier/documents/{$document->id}/preview")
            ->assertSuccessful();
    });

    it('denies preview of another suppliers document', function () {
        $supplier = Supplier::factory()->active()->create();
        $other = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $other->id,
        ]);

        $this->actingAs($supplier, 'supplier')
            ->get("/supplier/documents/{$document->id}/preview")
            ->assertForbidden();
    });

    it('denies preview without a file', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'archivo_path' => null,
        ]);

        $this->actingAs($supplier, 'supplier')
            ->get("/supplier/documents/{$document->id}/preview")
            ->assertForbidden();
    });
});

describe('Document delete', function () {
    it('allows supplier to delete their pending document', function () {
        Storage::fake('local');
        $supplier = Supplier::factory()->active()->create();
        $path = "supplier-documents/{$supplier->id}/test.pdf";
        Storage::disk('local')->put($path, 'content');

        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'archivo_path' => $path,
            'archivo_nombre' => 'constancia.pdf',
            'uploaded_at' => now(),
            'document_state_id' => DocumentState::PENDIENTE,
        ]);

        $this->actingAs($supplier, 'supplier')
            ->delete("/supplier/documents/{$document->id}")
            ->assertRedirect('/dashboard');

        $document->refresh();
        expect($document->archivo_path)->toBeNull()
            ->and($document->archivo_nombre)->toBeNull()
            ->and($document->uploaded_at)->toBeNull()
            ->and($document->document_state_id)->toBe(DocumentState::PENDIENTE);

        Storage::disk('local')->assertMissing($path);
    });

    it('allows supplier to delete their en_revision document', function () {
        Storage::fake('local');
        $supplier = Supplier::factory()->active()->create();
        $path = "supplier-documents/{$supplier->id}/test.pdf";
        Storage::disk('local')->put($path, 'content');

        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'archivo_path' => $path,
            'archivo_nombre' => 'constancia.pdf',
            'uploaded_at' => now(),
            'document_state_id' => DocumentState::EN_REVISION,
        ]);

        $this->actingAs($supplier, 'supplier')
            ->delete("/supplier/documents/{$document->id}")
            ->assertRedirect('/dashboard');

        $document->refresh();
        expect($document->archivo_path)->toBeNull()
            ->and($document->document_state_id)->toBe(DocumentState::PENDIENTE);

        Storage::disk('local')->assertMissing($path);
    });

    it('denies delete of approved document', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => DocumentState::APROBADO,
        ]);

        $this->actingAs($supplier, 'supplier')
            ->delete("/supplier/documents/{$document->id}")
            ->assertForbidden();
    });

    it('denies delete of rejected document', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => DocumentState::RECHAZADO,
        ]);

        $this->actingAs($supplier, 'supplier')
            ->delete("/supplier/documents/{$document->id}")
            ->assertForbidden();
    });

    it('denies delete of another suppliers document', function () {
        $supplier = Supplier::factory()->active()->create();
        $other = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $other->id,
            'document_state_id' => DocumentState::PENDIENTE,
        ]);

        $this->actingAs($supplier, 'supplier')
            ->delete("/supplier/documents/{$document->id}")
            ->assertForbidden();
    });

    it('denies delete of document without file', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'archivo_path' => null,
            'document_state_id' => DocumentState::PENDIENTE,
        ]);

        $this->actingAs($supplier, 'supplier')
            ->delete("/supplier/documents/{$document->id}")
            ->assertForbidden();
    });
});

describe('SupplierDocumentPolicy → deleteFile', function () {
    it('allows delete for own pending document with file', function () {
        $policy = new SupplierDocumentPolicy;
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => DocumentState::PENDIENTE,
        ]);

        expect($policy->deleteFile($supplier, $document))->toBeTrue();
    });

    it('allows delete for own en_revision document with file', function () {
        $policy = new SupplierDocumentPolicy;
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => DocumentState::EN_REVISION,
        ]);

        expect($policy->deleteFile($supplier, $document))->toBeTrue();
    });

    it('denies delete for approved document', function () {
        $policy = new SupplierDocumentPolicy;
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => DocumentState::APROBADO,
        ]);

        expect($policy->deleteFile($supplier, $document))->toBeFalse();
    });

    it('denies delete for document without file', function () {
        $policy = new SupplierDocumentPolicy;
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'archivo_path' => null,
            'document_state_id' => DocumentState::PENDIENTE,
        ]);

        expect($policy->deleteFile($supplier, $document))->toBeFalse();
    });
});

describe('Dashboard documents data', function () {
    it('includes can_delete flag in document data', function () {
        $supplier = Supplier::factory()->active()->create();
        SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => DocumentState::EN_REVISION,
        ]);

        $this->actingAs($supplier, 'supplier')
            ->get('/dashboard')
            ->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('Supplier/Dashboard')
                ->has('documents', 1)
                ->where('documents.0.can_delete', true)
            );
    });
});
