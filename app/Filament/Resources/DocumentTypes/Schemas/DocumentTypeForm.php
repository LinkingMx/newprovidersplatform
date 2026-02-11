<?php

namespace App\Filament\Resources\DocumentTypes\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DocumentTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Tipo de Documento')
                    ->description('Detalles básicos del tipo de documento')
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->prefixIcon('heroicon-o-document-text')
                            ->placeholder('Ej: Constancia de Situación Fiscal')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Describe el propósito y contenido de este tipo de documento')
                            ->rows(3),

                        TextInput::make('validez_dias')
                            ->label('Validez (días)')
                            ->prefixIcon('heroicon-o-calendar')
                            ->placeholder('Ej: 365')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Cantidad de días que el documento permanece válido'),
                    ])
                    ->columns(1),

                Section::make('Configuración')
                    ->description('Estado del tipo de documento')
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
