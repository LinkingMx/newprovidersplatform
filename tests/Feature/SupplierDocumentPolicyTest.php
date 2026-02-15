<?php

use App\Models\Supplier;
use App\Models\SupplierDocument;
use App\Policies\SupplierDocumentPolicy;
use Database\Seeders\DocumentStateSeeder;

beforeEach(function () {
    $this->seed(DocumentStateSeeder::class);
    $this->policy = new SupplierDocumentPolicy;
});

describe('SupplierDocumentPolicy → download', function () {
    it('allows supplier to download their own document', function () {
        $supplier = Supplier::factory()->create(['status' => 'active']);
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $supplier->id,
        ]);

        expect($this->policy->download($supplier, $document))->toBeTrue();
    });

    it('denies supplier from downloading another suppliers document', function () {
        $supplier = Supplier::factory()->create(['status' => 'active']);
        $otherSupplier = Supplier::factory()->create(['status' => 'active']);
        $document = SupplierDocument::factory()->uploaded()->create([
            'supplier_id' => $otherSupplier->id,
        ]);

        expect($this->policy->download($supplier, $document))->toBeFalse();
    });
});

describe('SupplierDocumentPolicy → upload', function () {
    it('allows supplier to upload to their own pending document', function () {
        $supplier = Supplier::factory()->create(['status' => 'active']);
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 1,
        ]);

        expect($this->policy->upload($supplier, $document))->toBeTrue();
    });

    it('allows supplier to re-upload to their own rejected document', function () {
        $supplier = Supplier::factory()->create(['status' => 'active']);
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 4,
        ]);

        expect($this->policy->upload($supplier, $document))->toBeTrue();
    });

    it('denies supplier from uploading to another suppliers document', function () {
        $supplier = Supplier::factory()->create(['status' => 'active']);
        $otherSupplier = Supplier::factory()->create(['status' => 'active']);
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $otherSupplier->id,
            'document_state_id' => 1,
        ]);

        expect($this->policy->upload($supplier, $document))->toBeFalse();
    });

    it('denies upload to approved document', function () {
        $supplier = Supplier::factory()->create(['status' => 'active']);
        $document = SupplierDocument::factory()->create([
            'supplier_id' => $supplier->id,
            'document_state_id' => 3,
        ]);

        expect($this->policy->upload($supplier, $document))->toBeFalse();
    });
});
