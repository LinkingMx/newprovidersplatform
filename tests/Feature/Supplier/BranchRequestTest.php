<?php

use App\Enums\BranchRequestStatus;
use App\Models\Branch;
use App\Models\BranchRequest;
use App\Models\Supplier;

beforeEach(function () {
    $this->supplier = Supplier::factory()->active()->create();
    $this->branch = Branch::factory()->create();
});

it('can create a branch request', function () {
    $this->actingAs($this->supplier, 'supplier')
        ->post('/supplier/branch-requests', [
            'branch_id' => $this->branch->id,
            'notas_proveedor' => 'Me interesa esta sucursal.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('branch_requests', [
        'supplier_id' => $this->supplier->id,
        'branch_id' => $this->branch->id,
        'status' => 'pending',
        'notas_proveedor' => 'Me interesa esta sucursal.',
    ]);
});

it('can create a branch request without notes', function () {
    $this->actingAs($this->supplier, 'supplier')
        ->post('/supplier/branch-requests', [
            'branch_id' => $this->branch->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('branch_requests', [
        'supplier_id' => $this->supplier->id,
        'branch_id' => $this->branch->id,
        'notas_proveedor' => null,
    ]);
});

it('requires branch_id', function () {
    $this->actingAs($this->supplier, 'supplier')
        ->post('/supplier/branch-requests', [])
        ->assertSessionHasErrors('branch_id');
});

it('requires a valid branch', function () {
    $this->actingAs($this->supplier, 'supplier')
        ->post('/supplier/branch-requests', [
            'branch_id' => 99999,
        ])
        ->assertSessionHasErrors('branch_id');
});

it('prevents duplicate pending requests for the same branch', function () {
    BranchRequest::factory()->create([
        'supplier_id' => $this->supplier->id,
        'branch_id' => $this->branch->id,
        'status' => BranchRequestStatus::Pending,
    ]);

    $this->actingAs($this->supplier, 'supplier')
        ->post('/supplier/branch-requests', [
            'branch_id' => $this->branch->id,
        ])
        ->assertSessionHasErrors('branch_id');
});

it('allows re-requesting after rejection', function () {
    BranchRequest::factory()->rejected()->create([
        'supplier_id' => $this->supplier->id,
        'branch_id' => $this->branch->id,
    ]);

    $this->actingAs($this->supplier, 'supplier')
        ->post('/supplier/branch-requests', [
            'branch_id' => $this->branch->id,
        ])
        ->assertRedirect();

    expect(BranchRequest::where('supplier_id', $this->supplier->id)
        ->where('branch_id', $this->branch->id)
        ->count())->toBe(2);
});

it('prevents requesting an already assigned branch', function () {
    $this->supplier->branches()->attach($this->branch->id, [
        'assigned_at' => now(),
    ]);

    $this->actingAs($this->supplier, 'supplier')
        ->post('/supplier/branch-requests', [
            'branch_id' => $this->branch->id,
        ])
        ->assertSessionHasErrors('branch_id');
});

it('requires authentication', function () {
    $this->post('/supplier/branch-requests', [
        'branch_id' => $this->branch->id,
    ])
        ->assertRedirect();
});

it('profile_completed supplier can create a branch request', function () {
    $supplier = Supplier::factory()->profileCompleted()->create();

    $this->actingAs($supplier, 'supplier')
        ->post('/supplier/branch-requests', [
            'branch_id' => $this->branch->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('branch_requests', [
        'supplier_id' => $supplier->id,
        'branch_id' => $this->branch->id,
    ]);
});

it('shows available branches and branch requests on dashboard', function () {
    $assignedBranch = Branch::factory()->create();
    $this->supplier->branches()->attach($assignedBranch->id, [
        'assigned_at' => now(),
    ]);

    $pendingRequest = BranchRequest::factory()->create([
        'supplier_id' => $this->supplier->id,
    ]);

    $this->actingAs($this->supplier, 'supplier')
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Supplier/Dashboard')
            ->has('availableBranches')
            ->has('branchRequests')
        );
});
