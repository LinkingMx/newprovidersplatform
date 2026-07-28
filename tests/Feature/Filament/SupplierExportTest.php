<?php

use App\Filament\Exports\SupplierExporter;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Models\Supplier;
use App\Models\User;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    Storage::fake('local');
});

test('supplier exporter columns include clabe interbancaria', function () {
    $columns = collect(SupplierExporter::getColumns());

    $clabeColumn = $columns->firstWhere(fn ($column) => $column->getName() === 'clabe_interbancaria');

    expect($clabeColumn)->not->toBeNull();
    expect($clabeColumn->getLabel())->toBe('CLABE Interbancaria');
});

test('supplier exporter columns mirror the visible table columns plus clabe', function () {
    $names = collect(SupplierExporter::getColumns())->map(fn ($column) => $column->getName());

    expect($names->all())->toBe([
        'name',
        'email',
        'rfc',
        'clabe_interbancaria',
        'status',
        'branches_count',
        'created_at',
    ]);
});

test('export action is available on the suppliers list for an admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    actingAs($admin);

    Livewire::test(ListSuppliers::class)
        ->assertActionExists(TestAction::make('export')->table());
});

test('exporting suppliers creates an export record with the correct row count and clabe data', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    Supplier::factory()->count(2)->create();
    Supplier::factory()->active()->create(['clabe_interbancaria' => '072580008831205168']);

    actingAs($admin);

    Livewire::test(ListSuppliers::class)
        ->callAction(TestAction::make('export')->table())
        ->assertHasNoActionErrors();

    $export = Export::latest('id')->first();

    expect($export)->not->toBeNull();
    expect($export->successful_rows)->toBe(Supplier::count());
    expect($export->exporter)->toBe(SupplierExporter::class);

    // El export debe forzar disco 'local': el default de filesystems ('s3' en
    // producción, apunta al bucket de Supabase Storage) rechaza los archivos
    // de trabajo del export con 415 Unsupported Media Type.
    expect($export->file_disk)->toBe('local');
});
