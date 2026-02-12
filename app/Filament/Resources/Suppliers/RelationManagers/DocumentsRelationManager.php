<?php

namespace App\Filament\Resources\Suppliers\RelationManagers;

use App\Models\DocumentState;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documentos del Expediente';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('documentType.nombre')
            ->columns([
                TextColumn::make('documentType.nombre')
                    ->label('Tipo de Documento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('documentState.etiqueta')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pendiente' => 'gray',
                        'En Revisión' => 'info',
                        'Aprobado' => 'success',
                        'Rechazado' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('archivo_nombre')
                    ->label('Archivo')
                    ->placeholder('Sin archivo'),

                TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date('d M Y')
                    ->placeholder('Sin vencimiento'),

                TextColumn::make('uploaded_at')
                    ->label('Cargado')
                    ->dateTime('d M Y H:i')
                    ->placeholder('No cargado'),
            ])
            ->recordActions([
                Action::make('cambiarEstado')
                    ->label('Cambiar Estado')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Select::make('document_state_id')
                            ->label('Nuevo Estado')
                            ->options(DocumentState::pluck('etiqueta', 'id'))
                            ->prefixIcon('heroicon-o-flag')
                            ->required(),

                        Textarea::make('notas')
                            ->label('Notas')
                            ->placeholder('Motivo del cambio de estado (opcional)')
                            ->rows(3),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->update([
                            'document_state_id' => $data['document_state_id'],
                            'notas' => $data['notas'],
                            'reviewed_at' => now(),
                            'reviewed_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->success()
                            ->icon('heroicon-o-check-circle')
                            ->title('Estado Actualizado')
                            ->body('El estado del documento fue actualizado correctamente')
                            ->send();
                    }),
            ]);
    }
}
