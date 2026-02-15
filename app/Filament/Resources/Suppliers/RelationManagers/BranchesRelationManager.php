<?php

namespace App\Filament\Resources\Suppliers\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'branches';

    protected static ?string $title = 'Sucursales';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('pivot.assigned_at')
                    ->label('Asignada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Agregar Sucursal')
                    ->modalHeading('Vincular Sucursal')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name']),
            ])
            ->actions([
                DetachAction::make()
                    ->label('Desasociar'),
                DeleteAction::make(),
            ]);
    }
}
