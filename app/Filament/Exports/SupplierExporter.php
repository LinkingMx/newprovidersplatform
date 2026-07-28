<?php

namespace App\Filament\Exports;

use App\Enums\SupplierStatus;
use App\Models\Supplier;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class SupplierExporter extends Exporter
{
    protected static ?string $model = Supplier::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('Nombre'),

            ExportColumn::make('email')
                ->label('Email'),

            ExportColumn::make('rfc')
                ->label('RFC'),

            ExportColumn::make('clabe_interbancaria')
                ->label('CLABE Interbancaria'),

            ExportColumn::make('status')
                ->label('Estado')
                ->formatStateUsing(fn (SupplierStatus|string $state): string => $state instanceof SupplierStatus ? $state->label() : (SupplierStatus::tryFrom($state)?->label() ?? $state)),

            ExportColumn::make('branches_count')
                ->label('Sucursales')
                ->counts('branches'),

            ExportColumn::make('created_at')
                ->label('Registrado')
                ->formatStateUsing(fn ($state) => $state?->format('d M Y H:i')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Tu exportación de proveedores ha finalizado y '.Number::format($export->successful_rows).' '.str('fila')->plural($export->successful_rows).' se exportaron correctamente.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('fila')->plural($failedRowsCount).' no se pudieron exportar.';
        }

        return $body;
    }

    public function getJobConnection(): ?string
    {
        // Corre el job de exportación en la misma request en lugar de encolarlo,
        // para que el archivo quede listo de inmediato (sin depender del worker).
        return 'sync';
    }
}
