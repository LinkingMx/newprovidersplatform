<?php

namespace App\Filament\Resources\DocumentStates\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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

                TextColumn::make('transiciones_permitidas')
                    ->label('Transiciones permitidas')
                    ->formatStateUsing(function ($state) {
                        if (is_string($state)) {
                            $state = json_decode($state, true);
                        }

                        if (! $state) {
                            return '—';
                        }

                        $badges = array_map(
                            fn (string $t) => "<span class='inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800'>$t</span>",
                            $state
                        );

                        return implode(' <span class="text-gray-400">→</span> ', $badges);
                    })
                    ->html()
                    ->wrap(),
            ]);
    }
}
