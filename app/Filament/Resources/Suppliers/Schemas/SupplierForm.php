<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use App\Enums\SupplierStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;

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

                Section::make('Dirección')
                    ->description('Datos de dirección capturados por el proveedor')
                    ->icon(Heroicon::OutlinedMapPin)
                    ->schema([
                        TextInput::make('address_street')
                            ->label('Calle')
                            ->prefixIcon('heroicon-o-map-pin')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Sin capturar'),

                        TextInput::make('address_number')
                            ->label('Número')
                            ->prefixIcon('heroicon-o-hashtag')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Sin capturar'),

                        TextInput::make('address_neighborhood')
                            ->label('Colonia')
                            ->prefixIcon('heroicon-o-building-office')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Sin capturar'),

                        TextInput::make('address_city')
                            ->label('Ciudad')
                            ->prefixIcon('heroicon-o-building-library')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Sin capturar'),

                        TextInput::make('address_country')
                            ->label('País')
                            ->prefixIcon('heroicon-o-globe-americas')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Sin capturar'),

                        TextInput::make('address_zip')
                            ->label('Código Postal')
                            ->prefixIcon('heroicon-o-envelope')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Sin capturar'),
                    ])
                    ->columns(2)
                    ->visibleOn('edit'),

                Section::make('Datos Bancarios')
                    ->description('Información bancaria capturada por el proveedor')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->schema([
                        TextInput::make('clabe_interbancaria')
                            ->label('CLABE Interbancaria')
                            ->prefixIcon('heroicon-o-credit-card')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Sin capturar'),
                    ])
                    ->columns(1)
                    ->visibleOn('edit'),

                Group::make([
                    Section::make('Estado')
                        ->description('Estado actual del proveedor')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->schema([
                            Select::make('status')
                                ->label('Estado')
                                ->options(SupplierStatus::options())
                                ->disabled()
                                ->dehydrated(false),
                        ])
                        ->columns(1)
                        ->visibleOn('edit'),

                    Section::make('Cambiar Contraseña')
                        ->description('Asignar una nueva contraseña al proveedor')
                        ->icon(Heroicon::OutlinedKey)
                        ->collapsed()
                        ->schema([
                            TextInput::make('new_password')
                                ->label('Nueva Contraseña')
                                ->prefixIcon('heroicon-o-lock-closed')
                                ->placeholder('Dejar vacío para no cambiar')
                                ->password()
                                ->revealable()
                                ->minLength(8)
                                ->confirmed()
                                ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                ->dehydrated(fn (?string $state): bool => filled($state)),

                            TextInput::make('new_password_confirmation')
                                ->label('Confirmar Contraseña')
                                ->prefixIcon('heroicon-o-lock-closed')
                                ->placeholder('Repite la nueva contraseña')
                                ->password()
                                ->revealable()
                                ->dehydrated(false),
                        ])
                        ->columns(1)
                        ->visibleOn('edit'),
                ]),
            ]);
    }
}
