<?php

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(3)
                ->schema([
                    Section::make()
                        ->columnSpan(2)
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('causer_name')
                                        ->label('Usuario')
                                        ->disabled()
                                        ->dehydrated(false),

                                    TextInput::make('subject_display')
                                        ->label('Sujeto')
                                        ->disabled()
                                        ->dehydrated(false),
                                ]),

                            Textarea::make('description')
                                ->label('Descripción')
                                ->disabled()
                                ->dehydrated(false)
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),

                    Section::make()
                        ->columnSpan(1)
                        ->schema([
                            TextEntry::make('log_name')
                                ->label('Tipo')
                                ->placeholder('default'),

                            TextEntry::make('event')
                                ->label('Evento')
                                ->badge()
                                ->color(fn (?string $state): string => match ($state) {
                                    'created' => 'success',
                                    'updated' => 'warning',
                                    'deleted' => 'danger',
                                    'restored' => 'gray',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn (?string $state): string => match ($state) {
                                    'created' => 'Creado',
                                    'updated' => 'Actualizado',
                                    'deleted' => 'Eliminado',
                                    'restored' => 'Restaurado',
                                    default => $state ?? '—',
                                }),

                            TextEntry::make('created_at')
                                ->label('Fecha')
                                ->dateTime('d/m/Y H:i:s')
                                ->helperText(fn ($record): ?string => $record->created_at?->diffForHumans()),
                        ]),
                ]),

            Section::make('Propiedades')
                ->description('Cambios registrados para esta actividad.')
                ->columnSpanFull()
                ->schema([
                    KeyValue::make('properties_attributes')
                        ->hiddenLabel()
                        ->keyLabel('Campo')
                        ->valueLabel('Valor')
                        ->disabled()
                        ->dehydrated(false)
                        ->addable(false)
                        ->deletable(false)
                        ->editableKeys(false)
                        ->editableValues(false)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
