<?php

use App\Enums\SupplierStatus;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\SupplierBranch;
use App\Models\SupplierInvitation;

test('supplier model has softDeletes', function () {
    $supplier = Supplier::factory()->create();
    $supplier->delete();

    $this->assertSoftDeleted(Supplier::class, ['id' => $supplier->id]);
});

test('supplier model has name attribute', function () {
    $supplier = Supplier::factory()->create(['name' => 'Test Supplier']);

    expect($supplier->name)->toBe('Test Supplier');
});

test('supplier model has email attribute', function () {
    $supplier = Supplier::factory()->create(['email' => 'test@supplier.com']);

    expect($supplier->email)->toBe('test@supplier.com');
});

test('supplier email is unique', function () {
    Supplier::factory()->create(['email' => 'unique@test.com']);

    $this->expectException(\Illuminate\Database\QueryException::class);

    Supplier::create(['name' => 'Test', 'email' => 'unique@test.com']);
});

test('supplier has default status created', function () {
    $supplier = Supplier::factory()->create();

    expect($supplier->status)->toBe(SupplierStatus::Created);
});

test('supplier factory creates with correct attributes', function () {
    $supplier = Supplier::factory()->create();

    expect($supplier->name)->not->toBeEmpty();
    expect($supplier->email)->not->toBeEmpty();
    expect($supplier->status)->toBe(SupplierStatus::Created);
});

test('supplier registered factory sets status to registered', function () {
    $supplier = Supplier::factory()->registered()->create();

    expect($supplier->status)->toBe(SupplierStatus::Registered);
    expect($supplier->password_hash)->not->toBeNull();
});

test('supplier invited factory sets status to invited', function () {
    $supplier = Supplier::factory()->invited()->create();

    expect($supplier->status)->toBe(SupplierStatus::Invited);
});

test('supplier profileCompleted factory has all address data', function () {
    $supplier = Supplier::factory()->profileCompleted()->create();

    expect($supplier->status)->toBe(SupplierStatus::ProfileCompleted);
    expect($supplier->address_street)->not->toBeNull();
    expect($supplier->address_city)->not->toBeNull();
});

test('supplier active factory has clabe interbancaria', function () {
    $supplier = Supplier::factory()->active()->create();

    expect($supplier->status)->toBe(SupplierStatus::Active);
    expect($supplier->clabe_interbancaria)->not->toBeNull();
});

test('supplier can have many branches', function () {
    $supplier = Supplier::factory()->create();
    $branches = Branch::factory(3)->create();

    $supplier->branches()->attach($branches);

    expect($supplier->branches)->toHaveCount(3);
});

test('supplier can have many invitations', function () {
    $supplier = Supplier::factory()->create();

    SupplierInvitation::factory(2)->for($supplier)->create();

    expect($supplier->invitations)->toHaveCount(2);
});

test('supplier isActive method returns true for active status', function () {
    $supplier = Supplier::factory()->active()->create();

    expect($supplier->isActive())->toBeTrue();
});

test('supplier isActive method returns false for non-active status', function () {
    $supplier = Supplier::factory()->invited()->create();

    expect($supplier->isActive())->toBeFalse();
});

test('supplier isPending returns true for pending statuses', function () {
    $created = Supplier::factory()->create();
    $invited = Supplier::factory()->invited()->create();
    $registered = Supplier::factory()->registered()->create();

    expect($created->isPending())->toBeTrue();
    expect($invited->isPending())->toBeTrue();
    expect($registered->isPending())->toBeTrue();
});

test('supplier isPending returns false for completed statuses', function () {
    $completed = Supplier::factory()->profileCompleted()->create();
    $active = Supplier::factory()->active()->create();

    expect($completed->isPending())->toBeFalse();
    expect($active->isPending())->toBeFalse();
});

test('supplier fillable includes name and email', function () {
    $supplier = Supplier::factory()->make();

    expect(in_array('name', $supplier->getFillable()))->toBeTrue();
    expect(in_array('email', $supplier->getFillable()))->toBeTrue();
});

test('supplier invitation has correct relationship', function () {
    $supplier = Supplier::factory()->create();
    $invitation = SupplierInvitation::factory()->for($supplier)->create();

    expect($invitation->supplier->id)->toBe($supplier->id);
});

test('supplier invitation isExpired returns correct value', function () {
    $expired = SupplierInvitation::factory()->expired()->create();
    $valid = SupplierInvitation::factory()->create();

    expect($expired->isExpired())->toBeTrue();
    expect($valid->isExpired())->toBeFalse();
});

test('supplier invitation isAccepted returns correct value', function () {
    $accepted = SupplierInvitation::factory()->accepted()->create();
    $pending = SupplierInvitation::factory()->create();

    expect($accepted->isAccepted())->toBeTrue();
    expect($pending->isAccepted())->toBeFalse();
});

test('supplier branch pivot has correct attributes', function () {
    $supplier = Supplier::factory()->create();
    $branch = Branch::factory()->create();

    $supplier->branches()->attach($branch, [
        'assigned_by' => 'admin@test.com',
    ]);

    $pivot = SupplierBranch::where('supplier_id', $supplier->id)
        ->where('branch_id', $branch->id)
        ->first();

    expect($pivot->assigned_by)->toBe('admin@test.com');
    expect($pivot->assigned_at)->not->toBeNull();
});

test('supplier branch relationship includes pivot data', function () {
    $supplier = Supplier::factory()->create();
    $branch = Branch::factory()->create();

    $supplier->branches()->attach($branch);

    $supplierWithBranch = Supplier::with('branches')->find($supplier->id);
    $branchWithPivot = $supplierWithBranch->branches->first();

    expect($branchWithPivot->pivot->assigned_at)->not->toBeNull();
});

test('can create supplier with all attributes', function () {
    $supplier = Supplier::create([
        'name' => 'Test Company',
        'email' => 'test@company.com',
        'status' => 'created',
    ]);

    $this->assertDatabaseHas(Supplier::class, [
        'id' => $supplier->id,
        'name' => 'Test Company',
        'email' => 'test@company.com',
    ]);
});

test('can update supplier status', function () {
    $supplier = Supplier::factory()->create();
    $supplier->update(['status' => 'invited']);

    expect($supplier->refresh()->status)->toBe(SupplierStatus::Invited);
});

test('can soft delete supplier', function () {
    $supplier = Supplier::factory()->create();
    $supplier->delete();

    $this->assertSoftDeleted(Supplier::class, ['id' => $supplier->id]);
});

test('can restore soft deleted supplier', function () {
    $supplier = Supplier::factory()->create();
    $supplier->delete();
    $supplier->restore();

    $this->assertNotSoftDeleted(Supplier::class, ['id' => $supplier->id]);
});

test('soft deleted suppliers excluded from default queries', function () {
    $active = Supplier::factory()->create();
    $deleted = Supplier::factory()->create();
    $deleted->delete();

    $suppliers = Supplier::all();

    expect($suppliers)->toHaveCount(1);
    expect($suppliers->first()->id)->toBe($active->id);
});

test('supplier has timestamps', function () {
    $supplier = Supplier::factory()->create();

    expect($supplier->created_at)->not->toBeNull();
    expect($supplier->updated_at)->not->toBeNull();
});
