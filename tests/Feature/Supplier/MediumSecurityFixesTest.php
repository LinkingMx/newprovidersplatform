<?php

use App\Models\Supplier;
use App\Models\SupplierDocument;
use Database\Seeders\DocumentStateSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(DocumentStateSeeder::class);
});

describe('Path traversal protection on download', function () {
    it('blocks download when archivo_path contains directory traversal', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'archivo_path' => '../../../etc/passwd',
        ]);

        $this->actingAs($supplier, 'supplier')
            ->get(route('supplier.documents.download', $document))
            ->assertForbidden();
    });

    it('blocks download when archivo_path is outside supplier-documents directory', function () {
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'archivo_path' => 'other-directory/secret.pdf',
        ]);

        $this->actingAs($supplier, 'supplier')
            ->get(route('supplier.documents.download', $document))
            ->assertForbidden();
    });

    it('allows download when archivo_path is within supplier-documents directory', function () {
        Storage::fake('local');
        $supplier = Supplier::factory()->active()->create();

        $path = "supplier-documents/{$supplier->id}/test.pdf";
        Storage::disk('local')->put($path, 'test content');

        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
            'archivo_path' => $path,
            'archivo_nombre' => 'test.pdf',
        ]);

        $this->actingAs($supplier, 'supplier')
            ->get(route('supplier.documents.download', $document))
            ->assertSuccessful();
    });
});

describe('Upload rate limiting', function () {
    it('throttles document uploads after 10 requests per minute', function () {
        Storage::fake('local');
        $supplier = Supplier::factory()->active()->create();
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 1,
        ]);

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($supplier, 'supplier')
                ->post(route('supplier.documents.upload', $document), [
                    'archivo' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
                ]);
        }

        $this->actingAs($supplier, 'supplier')
            ->post(route('supplier.documents.upload', $document), [
                'archivo' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
            ])
            ->assertStatus(429);
    });
});

describe('Logout session invalidation', function () {
    it('invalidates session and regenerates CSRF token on logout', function () {
        $supplier = Supplier::factory()->active()->create();

        $this->actingAs($supplier, 'supplier');

        $oldToken = session()->token();

        $response = $this->post(route('supplier.logout'));

        $response->assertRedirect('/');
        $this->assertGuest('supplier');
        expect(session()->token())->not->toBe($oldToken);
    });
});
