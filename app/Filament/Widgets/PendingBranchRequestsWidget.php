<?php

namespace App\Filament\Widgets;

use App\Enums\BranchRequestStatus;
use App\Filament\Resources\BranchRequests\BranchRequestResource;
use App\Models\BranchRequest;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingBranchRequestsWidget extends TableWidget
{
    protected static ?string $heading = 'Solicitudes de Sucursal Pendientes';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BranchRequest::query()
                    ->where('status', BranchRequestStatus::Pending)
                    ->with(['supplier', 'branch'])
                    ->latest('requested_at')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Proveedor'),

                TextColumn::make('branch.name')
                    ->label('Sucursal'),

                TextColumn::make('requested_at')
                    ->label('Solicitada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([])
            ->recordUrl(fn (BranchRequest $record): string => BranchRequestResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('Sin solicitudes pendientes')
            ->emptyStateDescription('No hay solicitudes de sucursal por revisar.');
    }
}
