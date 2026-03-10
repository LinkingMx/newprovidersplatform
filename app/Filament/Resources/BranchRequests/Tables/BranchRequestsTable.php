<?php

namespace App\Filament\Resources\BranchRequests\Tables;

use App\Enums\BranchRequestStatus;
use App\Filament\Resources\BranchRequests\Actions\ApproveBranchRequestAction;
use App\Filament\Resources\BranchRequests\Actions\RejectBranchRequestAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BranchRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('requested_at', 'desc')
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors(
                        collect(BranchRequestStatus::cases())
                            ->mapWithKeys(fn (BranchRequestStatus $s) => [$s->color() => $s->value])
                            ->all()
                    )
                    ->formatStateUsing(fn (BranchRequestStatus|string $state): string => $state instanceof BranchRequestStatus ? $state->label() : (BranchRequestStatus::tryFrom($state)?->label() ?? $state))
                    ->sortable(),

                TextColumn::make('notas_proveedor')
                    ->label('Notas del Proveedor')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('requested_at')
                    ->label('Solicitada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('resolved_at')
                    ->label('Resuelta')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Por Estado')
                    ->options(BranchRequestStatus::options()),
            ])
            ->recordActions([
                ViewAction::make(),
                ApproveBranchRequestAction::make(),
                RejectBranchRequestAction::make(),
            ]);
    }
}
