<?php

use App\Models\Branch;

test('branch model has softDeletes', function () {
    $branch = Branch::factory()->create();
    $branch->delete();

    $this->assertSoftDeleted(Branch::class, ['id' => $branch->id]);
});

test('branch model has name attribute', function () {
    $branch = Branch::factory()->create(['name' => 'Sucursal Centro']);

    expect($branch->name)->toBe('Sucursal Centro');
});

test('branch factory creates branch with name', function () {
    $branch = Branch::factory()->create();

    expect($branch->name)->not->toBeEmpty();
});

test('branch name is required', function () {
    $branch = Branch::make(['name' => '']);

    expect($branch->name)->toBe('');
});

test('branch name must be unique', function () {
    Branch::factory()->create(['name' => 'Sucursal Centro']);

    $this->expectException(\Illuminate\Database\QueryException::class);

    Branch::create(['name' => 'Sucursal Centro']);
});

test('branch fillable includes name', function () {
    $branch = Branch::factory()->make();

    expect(in_array('name', $branch->getFillable()))->toBeTrue();
});

test('can create a branch', function () {
    $branch = Branch::create(['name' => 'Sucursal Centro']);

    $this->assertDatabaseHas(Branch::class, [
        'id' => $branch->id,
        'name' => 'Sucursal Centro',
    ]);
});

test('can update a branch', function () {
    $branch = Branch::factory()->create(['name' => 'Sucursal Centro']);
    $branch->update(['name' => 'Sucursal Norte']);

    $this->assertDatabaseHas(Branch::class, [
        'id' => $branch->id,
        'name' => 'Sucursal Norte',
    ]);
});

test('can soft delete a branch', function () {
    $branch = Branch::factory()->create();
    $branch->delete();

    $this->assertSoftDeleted(Branch::class, ['id' => $branch->id]);
});

test('can restore a soft deleted branch', function () {
    $branch = Branch::factory()->create();
    $branch->delete();
    $branch->restore();

    $this->assertNotSoftDeleted(Branch::class, ['id' => $branch->id]);
});

test('can force delete a soft deleted branch', function () {
    $branch = Branch::factory()->create();
    $branch->delete();
    $branch->forceDelete();

    $this->assertDatabaseMissing(Branch::class, ['id' => $branch->id]);
});

test('soft deleted branches are excluded from default queries', function () {
    $active = Branch::factory()->create();
    $deleted = Branch::factory()->create();
    $deleted->delete();

    $branches = Branch::all();

    expect($branches)->toHaveCount(1);
    expect($branches->first()->id)->toBe($active->id);
});

test('branch has timestamps', function () {
    $branch = Branch::factory()->create();

    expect($branch->created_at)->not->toBeNull();
    expect($branch->updated_at)->not->toBeNull();
});
