<?php

namespace App\Filament\Resources\BranchRequests\Pages;

use App\Filament\Resources\BranchRequests\Actions\ApproveBranchRequestAction;
use App\Filament\Resources\BranchRequests\Actions\RejectBranchRequestAction;
use App\Filament\Resources\BranchRequests\BranchRequestResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewBranchRequest extends ViewRecord
{
    protected static string $resource = BranchRequestResource::class;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información de la Solicitud')
                ->columns(2)
                ->schema([
                    TextEntry::make('supplier.name')
                        ->label('Proveedor'),

                    TextEntry::make('branch.name')
                        ->label('Sucursal'),

                    TextEntry::make('status')
                        ->label('Estado')
                        ->badge()
                        ->formatStateUsing(fn ($state) => $state->label())
                        ->color(fn ($state) => $state->color()),

                    TextEntry::make('requested_at')
                        ->label('Fecha de Solicitud')
                        ->dateTime('d M Y H:i'),

                    TextEntry::make('notas_proveedor')
                        ->label('Notas del Proveedor')
                        ->columnSpanFull()
                        ->placeholder('Sin notas'),
                ]),

            Section::make('Resolución')
                ->columns(2)
                ->visible(fn ($record) => $record->resolved_at !== null)
                ->schema([
                    TextEntry::make('resolvedBy.name')
                        ->label('Resuelto por'),

                    TextEntry::make('resolved_at')
                        ->label('Fecha de Resolución')
                        ->dateTime('d M Y H:i'),

                    TextEntry::make('notas_admin')
                        ->label('Notas del Administrador')
                        ->columnSpanFull()
                        ->placeholder('Sin notas'),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ApproveBranchRequestAction::make(),
            RejectBranchRequestAction::make(),
        ];
    }
}
