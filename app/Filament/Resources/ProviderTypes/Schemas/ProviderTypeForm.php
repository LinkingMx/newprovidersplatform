<?php

namespace App\Filament\Resources\ProviderTypes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProviderTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Tipo de Proveedor')
                    ->description('Detalles básicos del tipo de proveedor')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->prefixIcon('heroicon-o-building-library')
                            ->placeholder('Ej: Persona Física')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Describe el tipo de proveedor y sus características')
                            ->rows(3),
                    ])
                    ->columns(1),

                Section::make('Configuración')
                    ->description('Estado del tipo de proveedor')
                    ->schema([
                        Toggle::make('activo')
                            ->label('Activo')
                            ->helperText('Los tipos inactivos no aparecerán en los formularios')
                            ->default(true),
                    ])
                    ->columns(1),
            ]);
    }
}
