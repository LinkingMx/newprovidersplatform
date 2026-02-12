<?php

namespace App\Filament\Resources\ProviderTypes\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentTypesRelationManager extends RelationManager
{
    protected static string $relationship = 'documentTypes';

    protected static ?string $title = 'Tipos de Documento Requeridos';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Tipo de Documento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50),

                TextColumn::make('validez_dias')
                    ->label('Validez (días)')
                    ->placeholder('Sin vencimiento'),

                IconColumn::make('obligatorio')
                    ->label('Obligatorio')
                    ->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['nombre', 'descripcion'])
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Toggle::make('obligatorio')
                            ->label('¿Obligatorio?')
                            ->default(true)
                            ->helperText('Si es obligatorio, el proveedor debe cargarlo para completar su expediente'),
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
