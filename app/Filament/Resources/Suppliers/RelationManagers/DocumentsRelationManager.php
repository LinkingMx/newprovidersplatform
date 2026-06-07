<?php

namespace App\Filament\Resources\Suppliers\RelationManagers;

use App\Models\DocumentState;
use App\Models\SupplierDocument;
use App\Services\DocumentReviewService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

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
                    ->placeholder('Sin archivo')
                    ->icon(fn (SupplierDocument $record): ?string => $record->archivo_path ? 'heroicon-o-arrow-top-right-on-square' : null)
                    ->color(fn (SupplierDocument $record): ?string => $record->archivo_path ? 'primary' : null)
                    ->url(function (SupplierDocument $record): ?string {
                        if (! $record->archivo_path) {
                            return null;
                        }

                        return Storage::disk('s3')->temporaryUrl(
                            $record->archivo_path,
                            now()->addMinutes(15)
                        );
                    })
                    ->openUrlInNewTab(),

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
                    ->action(function (array $data, SupplierDocument $record, DocumentReviewService $service): void {
                        $service->changeState($record, (int) $data['document_state_id'], $data['notas'] ?? null);

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
