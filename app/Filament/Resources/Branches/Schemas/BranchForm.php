<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la Sucursal')
                    ->description('Completa los datos básicos de la sucursal')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->prefixIcon('heroicon-o-map-pin')
                            ->placeholder('Sucursal Centro')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(1),

                Section::make('Integración SAP')
                    ->description('Identificadores para sincronización con SAP. Opcionales por ahora.')
                    ->icon('heroicon-o-cube-transparent')
                    ->schema([
                        TextInput::make('sap_db')
                            ->label('SAP DB')
                            ->prefixIcon('heroicon-o-circle-stack')
                            ->placeholder('Nombre de la base SAP (e.g. SBO_COSTENO)')
                            ->maxLength(100)
                            ->nullable()
                            ->helperText('Identificador de la base de datos SAP donde reside esta sucursal.'),

                        TextInput::make('sap_bplid')
                            ->label('SAP BPLID')
                            ->prefixIcon('heroicon-o-building-office-2')
                            ->placeholder('ID de Branch Place (BPLID)')
                            ->maxLength(50)
                            ->nullable()
                            ->helperText('Branch Place ID de SAP Business One para esta sucursal.'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
