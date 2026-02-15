<?php

use App\Enums\SupplierStatus;
use App\Filament\Widgets\DocumentsByStateChart;
use App\Filament\Widgets\ExpiringDocumentsTable;
use App\Filament\Widgets\SuppliersByStatusChart;
use App\Filament\Widgets\SuppliersStatsOverview;
use App\Models\DocumentState;
use App\Models\DocumentType;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    actingAs($user);

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

// --- Stats Overview Widget ---

test('stats overview widget renders successfully', function () {
    Livewire::test(SuppliersStatsOverview::class)
        ->assertSuccessful();
});

test('stats overview shows correct supplier counts', function () {
    Supplier::factory()->count(3)->active()->create();
    Supplier::factory()->count(2)->invited()->create();
    Supplier::factory()->count(1)->create(); // created status

    $widget = new SuppliersStatsOverview;
    $reflection = new ReflectionMethod($widget, 'getStats');
    $stats = $reflection->invoke($widget);

    expect($stats)->toHaveCount(4);
    expect($stats[0]->getValue())->toBe(6);    // Total
    expect($stats[1]->getValue())->toBe(3);    // Active
    expect($stats[2]->getValue())->toBe(3);    // Pending (created + invited)
});

test('stats overview counts expiring documents within 30 days', function () {
    $supplier1 = Supplier::factory()->active()->create();
    $supplier2 = Supplier::factory()->active()->create();

    // 2 documents expiring within 30 days (different suppliers to avoid unique constraint)
    SupplierDocument::factory()
        ->for($supplier1)
        ->for(DocumentType::factory(), 'documentType')
        ->create(['fecha_vencimiento' => now()->addDays(15)]);

    SupplierDocument::factory()
        ->for($supplier2)
        ->for(DocumentType::factory(), 'documentType')
        ->create(['fecha_vencimiento' => now()->addDays(10)]);

    // 1 document beyond 30 days — should NOT count
    SupplierDocument::factory()
        ->for(Supplier::factory()->active())
        ->for(DocumentType::factory(), 'documentType')
        ->create(['fecha_vencimiento' => now()->addDays(45)]);

    $widget = new SuppliersStatsOverview;
    $reflection = new ReflectionMethod($widget, 'getStats');
    $stats = $reflection->invoke($widget);

    expect($stats[3]->getValue())->toBe(2);
});

// --- Suppliers by Status Chart ---

test('suppliers by status chart renders successfully', function () {
    Livewire::test(SuppliersByStatusChart::class)
        ->assertSuccessful();
});

test('suppliers by status chart returns valid doughnut data', function () {
    Supplier::factory()->count(3)->active()->create();
    Supplier::factory()->count(2)->invited()->create();

    $widget = new SuppliersByStatusChart;

    $reflection = new ReflectionMethod($widget, 'getData');
    $data = $reflection->invoke($widget);

    expect($data)->toHaveKeys(['datasets', 'labels']);
    expect($data['labels'])->toHaveCount(count(SupplierStatus::cases()));
    expect($data['datasets'][0]['data'])->toHaveCount(count(SupplierStatus::cases()));
    expect($data['datasets'][0]['backgroundColor'])->toHaveCount(count(SupplierStatus::cases()));
});

test('suppliers by status chart type is doughnut', function () {
    $widget = new SuppliersByStatusChart;

    $reflection = new ReflectionMethod($widget, 'getType');

    expect($reflection->invoke($widget))->toBe('doughnut');
});

// --- Documents by State Chart ---

test('documents by state chart renders successfully', function () {
    Livewire::test(DocumentsByStateChart::class)
        ->assertSuccessful();
});

test('documents by state chart returns valid data', function () {
    // 3 documents in Pendiente state (each with unique supplier+docType)
    for ($i = 0; $i < 3; $i++) {
        SupplierDocument::factory()
            ->for(Supplier::factory()->active())
            ->for(DocumentType::factory(), 'documentType')
            ->create(['document_state_id' => DocumentState::PENDIENTE]);
    }

    // 1 document in Aprobado state
    SupplierDocument::factory()
        ->for(Supplier::factory()->active())
        ->for(DocumentType::factory(), 'documentType')
        ->create(['document_state_id' => DocumentState::APROBADO]);

    $widget = new DocumentsByStateChart;

    $reflection = new ReflectionMethod($widget, 'getData');
    $data = $reflection->invoke($widget);

    expect($data)->toHaveKeys(['datasets', 'labels']);
    expect($data['labels'])->toContain('Pendiente', 'En Revisión', 'Aprobado', 'Rechazado');

    $pendienteIndex = array_search('Pendiente', $data['labels']);
    $aprobadoIndex = array_search('Aprobado', $data['labels']);

    expect($data['datasets'][0]['data'][$pendienteIndex])->toBe(3);
    expect($data['datasets'][0]['data'][$aprobadoIndex])->toBe(1);
});

test('documents by state chart type is doughnut', function () {
    $widget = new DocumentsByStateChart;

    $reflection = new ReflectionMethod($widget, 'getType');

    expect($reflection->invoke($widget))->toBe('doughnut');
});

// --- Expiring Documents Table ---

test('expiring documents table renders successfully', function () {
    Livewire::test(ExpiringDocumentsTable::class)
        ->assertSuccessful();
});

test('expiring documents table shows documents within 60 days', function () {
    $supplier = Supplier::factory()->active()->create();

    $expiringDoc = SupplierDocument::factory()
        ->for($supplier)
        ->for(DocumentType::factory(), 'documentType')
        ->create(['fecha_vencimiento' => now()->addDays(20)]);

    $farDoc = SupplierDocument::factory()
        ->for($supplier)
        ->for(DocumentType::factory(), 'documentType')
        ->create(['fecha_vencimiento' => now()->addDays(90)]);

    Livewire::test(ExpiringDocumentsTable::class)
        ->assertCanSeeTableRecords([$expiringDoc])
        ->assertCanNotSeeTableRecords([$farDoc]);
});

test('expiring documents table does not show past-due documents', function () {
    $pastDoc = SupplierDocument::factory()
        ->for(Supplier::factory()->active())
        ->for(DocumentType::factory(), 'documentType')
        ->create(['fecha_vencimiento' => now()->subDays(5)]);

    Livewire::test(ExpiringDocumentsTable::class)
        ->assertCanNotSeeTableRecords([$pastDoc]);
});

test('expiring documents table shows correct columns', function () {
    Livewire::test(ExpiringDocumentsTable::class)
        ->assertTableColumnExists('supplier.name')
        ->assertTableColumnExists('documentType.nombre')
        ->assertTableColumnExists('fecha_vencimiento')
        ->assertTableColumnExists('documentState.nombre');
});

// --- Dashboard Access ---

test('dashboard is accessible for super admin', function () {
    $this->get('/admin')
        ->assertSuccessful();
});

test('dashboard is not accessible for guests', function () {
    auth()->logout();

    $this->get('/admin')
        ->assertRedirect('/admin/login');
});
