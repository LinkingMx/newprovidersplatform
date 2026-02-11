<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Usuario')
                    ->description('Datos básicos del usuario')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->prefixIcon('heroicon-o-user')
                            ->placeholder('Juan Pérez')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->prefixIcon('heroicon-o-envelope')
                            ->placeholder('usuario@ejemplo.com')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Este correo será utilizado para notificaciones y acceso al sistema'),

                        TextInput::make('password')
                            ->label('Contraseña')
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->placeholder('••••••••')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->helperText(fn (string $context): ?string => $context === 'edit' ? 'Dejar en blanco para mantener la contraseña actual' : null
                            ),
                    ])
                    ->columns(1),

                Section::make('Configuración')
                    ->description('Estado y permisos del usuario')
                    ->schema([
                        Toggle::make('active')
                            ->label('Activo')
                            ->helperText('Los usuarios inactivos no pueden acceder al sistema')
                            ->default(true),

                        Select::make('roles')
                            ->label('Roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload(),
                    ])
                    ->columns(1),
            ]);
    }
}
