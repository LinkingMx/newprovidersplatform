<?php

namespace App\Filament\Resources\Suppliers\RelationManagers;

use App\Mail\SupplierDocumentStatusMailable;
use App\Models\DocumentState;
use App\Models\SupplierDocument;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

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
                    ->form(fn (SupplierDocument $record): array => [
                        Select::make('document_state_id')
                            ->label('Nuevo Estado')
                            ->options(function () use ($record): array {
                                $currentState = $record->documentState;
                                $allowed = $currentState->transiciones_permitidas ?? [];

                                return DocumentState::whereIn('nombre', $allowed)
                                    ->pluck('etiqueta', 'id')
                                    ->all();
                            })
                            ->prefixIcon('heroicon-o-flag')
                            ->required(),

                        Textarea::make('notas')
                            ->label('Notas')
                            ->placeholder('Motivo del cambio de estado (opcional)')
                            ->rows(3),
                    ])
                    ->action(function (array $data, SupplierDocument $record): void {
                        $record->update([
                            'document_state_id' => $data['document_state_id'],
                            'notas' => $data['notas'],
                            'reviewed_at' => now(),
                            'reviewed_by' => auth()->id(),
                        ]);

                        $newState = DocumentState::find($data['document_state_id']);

                        if ($newState && in_array($newState->nombre, ['Aprobado', 'Rechazado'])) {
                            $record->load(['supplier', 'documentType']);

                            Mail::to($record->supplier->email)->send(
                                new SupplierDocumentStatusMailable($record->supplier, $record, $newState)
                            );
                        }

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
