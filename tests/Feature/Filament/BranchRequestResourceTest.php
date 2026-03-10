<?php

use App\Enums\BranchRequestStatus;
use App\Filament\Resources\BranchRequests\BranchRequestResource;
use App\Mail\BranchRequestResolvedMailable;
use App\Models\Branch;
use App\Models\BranchRequest;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->create();
    actingAs($this->admin);
    Mail::fake();
});

// Authorization Tests

test('guests cannot access branch request list', function () {
    auth()->logout();

    $this->get(BranchRequestResource::getUrl('index'))
        ->assertRedirect('/admin/login');
});

// Model Tests

test('branch request model has softDeletes', function () {
    $request = BranchRequest::factory()->create();
    $request->delete();

    expect($request->trashed())->toBeTrue();
});

test('branch request factory creates pending request by default', function () {
    $request = BranchRequest::factory()->create();

    expect($request->status)->toBe(BranchRequestStatus::Pending);
    expect($request->isPending())->toBeTrue();
});

test('branch request factory approved state works', function () {
    $request = BranchRequest::factory()->approved()->create();

    expect($request->status)->toBe(BranchRequestStatus::Approved);
    expect($request->isApproved())->toBeTrue();
    expect($request->resolved_at)->not->toBeNull();
});

test('branch request factory rejected state works', function () {
    $request = BranchRequest::factory()->rejected()->create();

    expect($request->status)->toBe(BranchRequestStatus::Rejected);
    expect($request->isRejected())->toBeTrue();
    expect($request->notas_admin)->not->toBeNull();
});

test('branch request belongs to supplier', function () {
    $supplier = Supplier::factory()->create();
    $request = BranchRequest::factory()->create(['supplier_id' => $supplier->id]);

    expect($request->supplier->id)->toBe($supplier->id);
});

test('branch request belongs to branch', function () {
    $branch = Branch::factory()->create();
    $request = BranchRequest::factory()->create(['branch_id' => $branch->id]);

    expect($request->branch->id)->toBe($branch->id);
});

test('branch request pending scope works', function () {
    BranchRequest::factory()->count(2)->create();
    BranchRequest::factory()->approved()->create();
    BranchRequest::factory()->rejected()->create();

    expect(BranchRequest::pending()->count())->toBe(2);
});

// Approve Logic

test('approving a request assigns branch to supplier', function () {
    $supplier = Supplier::factory()->active()->create();
    $branch = Branch::factory()->create();
    $request = BranchRequest::factory()->create([
        'supplier_id' => $supplier->id,
        'branch_id' => $branch->id,
    ]);

    $request->update([
        'status' => BranchRequestStatus::Approved,
        'resolved_at' => now(),
        'resolved_by' => $this->admin->id,
    ]);

    $supplier->branches()->attach($branch->id, [
        'assigned_at' => now(),
        'assigned_by' => $this->admin->id,
    ]);

    Mail::to($supplier->email)->send(new BranchRequestResolvedMailable($request->fresh()));

    $request->refresh();
    expect($request->status)->toBe(BranchRequestStatus::Approved);
    expect($request->resolved_by)->toBe($this->admin->id);
    expect($supplier->branches()->where('branches.id', $branch->id)->exists())->toBeTrue();

    Mail::assertSent(BranchRequestResolvedMailable::class);
});

// Reject Logic

test('rejecting a request stores admin notes', function () {
    $request = BranchRequest::factory()->create();

    $request->update([
        'status' => BranchRequestStatus::Rejected,
        'notas_admin' => 'No cumple los requisitos.',
        'resolved_at' => now(),
        'resolved_by' => $this->admin->id,
    ]);

    Mail::to($request->supplier->email)->send(new BranchRequestResolvedMailable($request->fresh()));

    $request->refresh();
    expect($request->status)->toBe(BranchRequestStatus::Rejected);
    expect($request->notas_admin)->toBe('No cumple los requisitos.');

    Mail::assertSent(BranchRequestResolvedMailable::class);
});

// Navigation Badge

test('navigation badge shows pending count', function () {
    BranchRequest::factory()->count(3)->create(['status' => BranchRequestStatus::Pending]);
    BranchRequest::factory()->approved()->create();

    expect(BranchRequestResource::getNavigationBadge())->toBe('3');
});

test('navigation badge is null when no pending requests', function () {
    BranchRequest::factory()->approved()->create();

    expect(BranchRequestResource::getNavigationBadge())->toBeNull();
});

// Supplier relationship

test('supplier has branchRequests relationship', function () {
    $supplier = Supplier::factory()->active()->create();
    BranchRequest::factory()->count(2)->create(['supplier_id' => $supplier->id]);

    expect($supplier->branchRequests)->toHaveCount(2);
});
