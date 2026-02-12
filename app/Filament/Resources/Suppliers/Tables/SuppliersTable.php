<?php

namespace App\Filament\Resources\Suppliers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'gray' => 'created',
                        'yellow' => 'invited',
                        'blue' => 'registered',
                        'orange' => 'profile_completed',
                        'green' => 'active',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'Creado',
                        'invited' => 'Invitado',
                        'registered' => 'Registrado',
                        'profile_completed' => 'Perfil Completo',
                        'active' => 'Activo',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('branches_count')
                    ->label('Sucursales')
                    ->counts('branches')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Por Estado')
                    ->options([
                        'created' => 'Creado',
                        'invited' => 'Invitado',
                        'registered' => 'Registrado',
                        'profile_completed' => 'Perfil Completo',
                        'active' => 'Activo',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
