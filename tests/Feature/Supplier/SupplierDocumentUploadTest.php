<?php

use App\Models\DocumentState;
use App\Models\DocumentType;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');

    // Seed document states
    DocumentState::create(['id' => 1, 'nombre' => 'Pendiente', 'etiqueta' => 'Pendiente', 'color' => 'gray', 'icono' => 'heroicon-o-clock', 'por_defecto' => true, 'completado' => false, 'transiciones_permitidas' => ['En Revisión']]);
    DocumentState::create(['id' => 2, 'nombre' => 'En Revisión', 'etiqueta' => 'En Revisión', 'color' => 'blue', 'icono' => 'heroicon-o-eye', 'por_defecto' => false, 'completado' => false, 'transiciones_permitidas' => ['Aprobado', 'Rechazado']]);
    DocumentState::create(['id' => 3, 'nombre' => 'Aprobado', 'etiqueta' => 'Aprobado', 'color' => 'green', 'icono' => 'heroicon-o-check-circle', 'por_defecto' => false, 'completado' => true, 'transiciones_permitidas' => ['Rechazado']]);
    DocumentState::create(['id' => 4, 'nombre' => 'Rechazado', 'etiqueta' => 'Rechazado', 'color' => 'red', 'icono' => 'heroicon-o-x-circle', 'por_defecto' => false, 'completado' => false, 'transiciones_permitidas' => ['En Revisión']]);
});

describe('Supplier Document Upload', function () {
    it('requires authentication to upload', function () {
        $document = SupplierDocument::factory()->create();

        $response = $this->post("/supplier/documents/{$document->id}/upload", [
            'archivo' => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect();
    });

    it('rejects upload for document not owned by supplier', function () {
        $supplier = Supplier::factory()->active()->create();
        $otherSupplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $otherSupplier->id,
            'document_state_id' => 1,
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->post("/supplier/documents/{$document->id}/upload", [
                'archivo' => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
            ]);

        $response->assertForbidden();
    });

    it('validates file is required', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 1,
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->post("/supplier/documents/{$document->id}/upload", []);

        $response->assertInvalid('archivo');
    });

    it('validates file type must be pdf, jpg, or png', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 1,
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->post("/supplier/documents/{$document->id}/upload", [
                'archivo' => UploadedFile::fake()->create('test.exe', 100, 'application/x-msdownload'),
            ]);

        $response->assertInvalid('archivo');
    });

    it('validates file size must not exceed 10MB', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 1,
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->post("/supplier/documents/{$document->id}/upload", [
                'archivo' => UploadedFile::fake()->create('test.pdf', 11000, 'application/pdf'),
            ]);

        $response->assertInvalid('archivo');
    });

    it('uploads a PDF file successfully', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 1,
        ]);

        $file = UploadedFile::fake()->create('contrato.pdf', 500, 'application/pdf');

        $response = $this->actingAs($supplier, 'supplier')
            ->post("/supplier/documents/{$document->id}/upload", [
                'archivo' => $file,
            ]);

        $response->assertRedirect(route('dashboard'));

        $document->refresh();
        expect($document->archivo_nombre)->toBe('contrato.pdf')
            ->and($document->archivo_path)->toStartWith("supplier-documents/{$supplier->id}/")
            ->and($document->uploaded_at)->not->toBeNull()
            ->and($document->document_state_id)->toBe(DocumentState::EN_REVISION);

        Storage::disk('local')->assertExists($document->archivo_path);
    });

    it('uploads a JPG file successfully', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 1,
        ]);

        $file = UploadedFile::fake()->image('foto.jpg', 800, 600);

        $response = $this->actingAs($supplier, 'supplier')
            ->post("/supplier/documents/{$document->id}/upload", [
                'archivo' => $file,
            ]);

        $response->assertRedirect(route('dashboard'));

        $document->refresh();
        expect($document->archivo_nombre)->toBe('foto.jpg');
        Storage::disk('local')->assertExists($document->archivo_path);
    });

    it('uploads a PNG file successfully', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 1,
        ]);

        $file = UploadedFile::fake()->image('identificacion.png', 1024, 768);

        $response = $this->actingAs($supplier, 'supplier')
            ->post("/supplier/documents/{$document->id}/upload", [
                'archivo' => $file,
            ]);

        $response->assertRedirect(route('dashboard'));

        $document->refresh();
        expect($document->archivo_nombre)->toBe('identificacion.png');
        Storage::disk('local')->assertExists($document->archivo_path);
    });

    it('allows re-upload after rejection and sets state to En Revisión', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 4, // Rechazado
            'notas' => 'Documento ilegible',
        ]);

        // Store a fake "old" file
        $oldPath = $document->archivo_path;
        Storage::disk('local')->put($oldPath, 'old content');

        $file = UploadedFile::fake()->create('nuevo-contrato.pdf', 500, 'application/pdf');

        $response = $this->actingAs($supplier, 'supplier')
            ->post("/supplier/documents/{$document->id}/upload", [
                'archivo' => $file,
            ]);

        $response->assertRedirect(route('dashboard'));

        $document->refresh();
        expect($document->archivo_nombre)->toBe('nuevo-contrato.pdf')
            ->and($document->document_state_id)->toBe(DocumentState::EN_REVISION)
            ->and($document->notas)->toBeNull()
            ->and($document->uploaded_at)->not->toBeNull();

        // Old file should be deleted
        Storage::disk('local')->assertMissing($oldPath);
        // New file should exist
        Storage::disk('local')->assertExists($document->archivo_path);
    });

    it('allows upload when document is En Revisión', function () {
        Storage::fake('local');

        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => DocumentState::EN_REVISION,
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->post("/supplier/documents/{$document->id}/upload", [
                'archivo' => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
            ]);

        $response->assertRedirect('/dashboard');
        $document->refresh();
        expect($document->archivo_path)->not->toBeNull();
    });

    it('rejects upload when document is Aprobado', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 3, // Aprobado
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->post("/supplier/documents/{$document->id}/upload", [
                'archivo' => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
            ]);

        $response->assertForbidden();
    });
});

describe('Supplier Document Download', function () {
    it('requires authentication to download', function () {
        $document = SupplierDocument::factory()->uploaded()->create();

        $response = $this->get("/supplier/documents/{$document->id}/download");

        $response->assertRedirect();
    });

    it('rejects download for document not owned by supplier', function () {
        $supplier = Supplier::factory()->active()->create();
        $otherSupplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $otherSupplier->id,
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->get("/supplier/documents/{$document->id}/download");

        $response->assertForbidden();
    });

    it('redirects when file does not exist on disk', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
        ]);

        // File path set but file not actually on disk
        $response = $this->actingAs($supplier, 'supplier')
            ->get("/supplier/documents/{$document->id}/download");

        $response->assertRedirect(route('dashboard'));
    });

    it('returns 403 when no file has been uploaded', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 1,
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->get("/supplier/documents/{$document->id}/download");

        $response->assertForbidden();
    });

    it('downloads file successfully with original name', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 1,
            'archivo_path' => "supplier-documents/{$supplier->id}/test-file.pdf",
            'archivo_nombre' => 'mi-contrato.pdf',
            'uploaded_at' => now(),
        ]);

        // Create the file on the fake disk
        Storage::disk('local')->put($document->archivo_path, 'PDF file content');

        $response = $this->actingAs($supplier, 'supplier')
            ->get("/supplier/documents/{$document->id}/download");

        $response->assertOk();
        $response->assertDownload('mi-contrato.pdf');
    });
});

describe('Supplier Dashboard Documents', function () {
    it('shows documents on the dashboard', function () {
        $supplier = Supplier::factory()->active()->create();
        $docType = DocumentType::factory()->create(['nombre' => 'Contrato Firmado']);
        SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => $docType->id,
            'document_state_id' => 1,
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Supplier/Dashboard')
            ->has('documents', 1)
            ->where('documents.0.document_type.nombre', 'Contrato Firmado')
            ->where('documents.0.can_upload', true)
            ->where('documents.0.has_file', false)
        );
    });

    it('returns empty documents array when no documents assigned', function () {
        $supplier = Supplier::factory()->active()->create();

        $response = $this->actingAs($supplier, 'supplier')
            ->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Supplier/Dashboard')
            ->has('documents', 0)
        );
    });

    it('shows Rechazado document with notas and can_upload=true', function () {
        $supplier = Supplier::factory()->active()->create();
        $docType = DocumentType::factory()->create(['nombre' => 'RFC']);
        $rechazado = DocumentState::where('nombre', 'Rechazado')->first();

        SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => $docType->id,
            'document_state_id' => $rechazado->id,
            'notas' => 'Documento ilegible',
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Supplier/Dashboard')
            ->has('documents', 1)
            ->where('documents.0.can_upload', true)
            ->where('documents.0.has_file', true)
            ->where('documents.0.notas', 'Documento ilegible')
            ->where('documents.0.document_state.color', 'red')
        );
    });

    it('shows Aprobado document with can_upload=false', function () {
        $supplier = Supplier::factory()->active()->create();
        $docType = DocumentType::factory()->create(['nombre' => 'Contrato']);
        $aprobado = DocumentState::where('nombre', 'Aprobado')->first();

        SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => $docType->id,
            'document_state_id' => $aprobado->id,
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Supplier/Dashboard')
            ->has('documents', 1)
            ->where('documents.0.can_upload', false)
            ->where('documents.0.has_file', true)
            ->where('documents.0.document_state.color', 'green')
        );
    });

    it('shows En Revisión document with can_upload=true', function () {
        $supplier = Supplier::factory()->active()->create();
        $docType = DocumentType::factory()->create(['nombre' => 'Identificación']);
        $enRevision = DocumentState::where('nombre', 'En Revisión')->first();

        SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => $docType->id,
            'document_state_id' => $enRevision->id,
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Supplier/Dashboard')
            ->has('documents', 1)
            ->where('documents.0.can_upload', true)
            ->where('documents.0.has_file', true)
            ->where('documents.0.document_state.color', 'blue')
        );
    });

    it('shows Pendiente document with file has can_upload=true and has_file=true', function () {
        $supplier = Supplier::factory()->active()->create();
        $docType = DocumentType::factory()->create(['nombre' => 'Comprobante']);
        $pendiente = DocumentState::where('nombre', 'Pendiente')->first();

        SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => $docType->id,
            'document_state_id' => $pendiente->id,
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Supplier/Dashboard')
            ->has('documents', 1)
            ->where('documents.0.can_upload', true)
            ->where('documents.0.has_file', true)
        );
    });

    it('shows multiple documents in different states', function () {
        $supplier = Supplier::factory()->active()->create();

        $pendiente = DocumentState::where('nombre', 'Pendiente')->first();
        $aprobado = DocumentState::where('nombre', 'Aprobado')->first();
        $rechazado = DocumentState::where('nombre', 'Rechazado')->first();

        SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => DocumentType::factory()->create(['nombre' => 'Doc A'])->id,
            'document_state_id' => $pendiente->id,
        ]);

        SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => DocumentType::factory()->create(['nombre' => 'Doc B'])->id,
            'document_state_id' => $aprobado->id,
        ]);

        SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'document_type_id' => DocumentType::factory()->create(['nombre' => 'Doc C'])->id,
            'document_state_id' => $rechazado->id,
            'notas' => 'Motivo de rechazo',
        ]);

        $response = $this->actingAs($supplier, 'supplier')
            ->get('/dashboard');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Supplier/Dashboard')
            ->has('documents', 3)
        );
    });
});
