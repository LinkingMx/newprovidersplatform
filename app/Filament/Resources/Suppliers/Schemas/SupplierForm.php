<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Básica')
                    ->description('Datos de identidad del proveedor')
                    ->icon(Heroicon::OutlinedUser)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->prefixIcon('heroicon-o-user')
                            ->placeholder('Nombre del Proveedor')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->prefixIcon('heroicon-o-envelope')
                            ->placeholder('proveedor@empresa.com')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Se enviará invitación a este correo'),

                        Select::make('provider_type_id')
                            ->label('Tipo de Proveedor')
                            ->relationship('providerType', 'nombre')
                            ->prefixIcon('heroicon-o-cube')
                            ->placeholder('Selecciona un tipo de proveedor')
                            ->searchable()
                            ->preload()
                            ->helperText('Determina los documentos requeridos para el expediente'),
                    ])
                    ->columns(1),

                Section::make('Estado')
                    ->description('Estado actual del proceso de onboarding')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options([
                                'created' => 'Creado',
                                'invited' => 'Invitado',
                                'registered' => 'Registrado',
                                'profile_completed' => 'Perfil Completo',
                                'active' => 'Activo',
                            ])
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(1),
            ]);
    }
}
