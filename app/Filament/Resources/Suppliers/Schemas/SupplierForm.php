<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use App\Enums\SupplierStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
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

                                TextInput::make('rfc')
                                    ->label('RFC')
                                    ->prefixIcon('heroicon-o-identification')
                                    ->placeholder('ABCD010101AB1')
                                    ->maxLength(13)
                                    ->nullable()
                                    ->unique(ignoreRecord: true)
                                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? strtoupper(trim($state)) : null)
                                    ->rule('regex:/^[A-ZÑ&]{3,4}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])[A-Z0-9]{3}$/i')
                                    ->validationMessages([
                                        'regex' => 'El RFC no tiene un formato válido.',
                                    ])
                                    ->helperText('Registro Federal de Contribuyentes (12 caracteres persona moral, 13 persona física). Opcional por ahora.'),

                                Select::make('provider_type_id')
                                    ->label('Tipo de Proveedor')
                                    ->relationship('providerType', 'nombre')
                                    ->prefixIcon('heroicon-o-cube')
                                    ->placeholder('Selecciona un tipo de proveedor')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Determina los documentos requeridos para el expediente'),
                            ])
                            ->columns(1)
                            ->extraAttributes(['style' => 'height: 100%;']),

                        Section::make('Información General')
                            ->description('Datos capturados por el proveedor durante su registro')
                            ->icon(Heroicon::OutlinedInformationCircle)
                            ->schema([
                                View::make('filament.resources.suppliers.components.supplier-info'),
                            ])
                            ->extraAttributes(['style' => 'height: 100%;'])
                            ->visibleOn('edit'),
                    ]),

                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
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
