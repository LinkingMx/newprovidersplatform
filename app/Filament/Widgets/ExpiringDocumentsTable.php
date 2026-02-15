<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\SupplierDocument;
use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ExpiringDocumentsTable extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        $now = CarbonImmutable::now();

        return $table
            ->heading('Documentos Próximos a Vencer')
            ->query(
                SupplierDocument::query()
                    ->with(['supplier', 'documentType', 'documentState'])
                    ->whereNotNull('fecha_vencimiento')
                    ->whereBetween('fecha_vencimiento', [$now, $now->addDays(60)])
                    ->orderBy('fecha_vencimiento')
                    ->limit(10),
            )
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable(),

                TextColumn::make('documentType.nombre')
                    ->label('Tipo de Documento'),

                TextColumn::make('fecha_vencimiento')
                    ->label('Fecha de Vencimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (SupplierDocument $record): string => $record->fecha_vencimiento?->diffInDays(now()) <= 15
                        ? 'danger'
                        : 'warning',
                    ),

                TextColumn::make('documentState.nombre')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (SupplierDocument $record): string => match ($record->documentState?->color) {
                        'gray' => 'gray',
                        'blue' => 'info',
                        'green' => 'success',
                        'red' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->emptyStateHeading('Sin documentos por vencer')
            ->emptyStateDescription('No hay documentos próximos a vencer en los próximos 60 días.')
            ->emptyStateIcon('heroicon-o-document-check')
            ->paginated(false);
    }
}
