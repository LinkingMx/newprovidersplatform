<?php

namespace App\Filament\Resources\DocumentStates\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class DocumentStatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('etiqueta')
                    ->label('Etiqueta')
                    ->badge(),

                IconColumn::make('por_defecto')
                    ->label('Por defecto')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('completado')
                    ->label('Completado')
                    ->boolean()
                    ->sortable(),

                ViewColumn::make('transiciones_permitidas')
                    ->label('Transiciones permitidas')
                    ->view('filament.tables.columns.state-transitions'),
            ]);
    }
}
