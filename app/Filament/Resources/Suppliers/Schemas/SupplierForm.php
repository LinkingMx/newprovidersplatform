<?php

namespace App\Filament\Resources\Suppliers\Schemas;

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

                Group::make([
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
