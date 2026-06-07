<?php

namespace App\Filament\Resources\DocumentReviews\Tables;

use App\Models\DocumentState;
use App\Models\DocumentType;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use App\Services\DocumentReviewService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DocumentReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('uploaded_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->description(fn (SupplierDocument $record): ?string => $record->supplier?->email)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('documentType.nombre')
                    ->label('Tipo de Documento')
                    ->icon('heroicon-o-document')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('documentState.etiqueta')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (SupplierDocument $record): string => $record->documentState?->color ?? 'gray')
                    ->icon(fn (SupplierDocument $record): ?string => $record->documentState?->icono)
                    ->sortable(),

                TextColumn::make('archivo_nombre')
                    ->label('Archivo')
                    ->placeholder('Sin archivo')
                    ->limit(30)
                    ->icon(fn (SupplierDocument $record): ?string => $record->archivo_path ? 'heroicon-o-paper-clip' : null)
                    ->color(fn (SupplierDocument $record): ?string => $record->archivo_path ? 'primary' : 'gray'),

                TextColumn::make('uploaded_at')
                    ->label('Subido')
                    ->dateTime('d M Y H:i')
                    ->description(fn (SupplierDocument $record): ?string => $record->uploaded_at?->diffForHumans())
                    ->placeholder('No subido')
                    ->sortable(),

                TextColumn::make('fecha_vencimiento')
                    ->label('Vence')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('reviewer.name')
                    ->label('Revisado por')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('reviewed_at')
                    ->label('Revisado el')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('document_state_id')
                    ->label('Estado')
                    ->multiple()
                    ->options(fn (): array => DocumentState::query()->pluck('etiqueta', 'id')->all()),

                SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->searchable()
                    ->preload()
                    ->options(fn (): array => Supplier::query()->orderBy('name')->pluck('name', 'id')->all()),

                SelectFilter::make('document_type_id')
                    ->label('Tipo de Documento')
                    ->multiple()
                    ->options(fn (): array => DocumentType::query()->orderBy('nombre')->pluck('nombre', 'id')->all()),

                Filter::make('uploaded_range')
                    ->label('Subido entre')
                    ->schema([
                        DatePicker::make('desde')->label('Desde'),
                        DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['desde'] ?? null, fn ($q, $d) => $q->whereDate('uploaded_at', '>=', $d))
                            ->when($data['hasta'] ?? null, fn ($q, $d) => $q->whereDate('uploaded_at', '<=', $d));
                    }),

                Filter::make('only_with_file')
                    ->label('Solo con archivo subido')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('archivo_path')),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('Vista previa')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->iconButton()
                    ->modalHeading(fn (SupplierDocument $record): string => "Vista previa — {$record->documentType?->nombre}")
                    ->modalDescription(fn (SupplierDocument $record): string => "Proveedor: {$record->supplier?->name}")
                    ->modalContent(fn (SupplierDocument $record) => view('filament.document-reviews.preview', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalWidth(Width::FiveExtraLarge),

                Action::make('approve')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->modalHeading('Aprobar documento')
                    ->modalDescription(fn (SupplierDocument $record): string => "Se notificará al proveedor {$record->supplier?->name}.")
                    ->visible(fn (SupplierDocument $record): bool => $record->archivo_path && $record->document_state_id !== DocumentState::APROBADO)
                    ->action(function (SupplierDocument $record, DocumentReviewService $service): void {
                        $service->approve($record);
                        Notification::make()
                            ->success()
                            ->icon('heroicon-o-check-circle')
                            ->title('Documento aprobado')
                            ->body("Se notificó a {$record->supplier?->name}")
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Rechazar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->iconButton()
                    ->visible(fn (SupplierDocument $record): bool => $record->archivo_path && $record->document_state_id !== DocumentState::RECHAZADO)
                    ->form([
                        Textarea::make('motivo')
                            ->label('Motivo del rechazo')
                            ->placeholder('Explica al proveedor qué debe corregir…')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (array $data, SupplierDocument $record, DocumentReviewService $service): void {
                        $service->reject($record, $data['motivo']);
                        Notification::make()
                            ->danger()
                            ->icon('heroicon-o-x-circle')
                            ->title('Documento rechazado')
                            ->body("Se notificó a {$record->supplier?->name}")
                            ->send();
                    }),

                Action::make('changeState')
                    ->label('Cambiar estado')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->iconButton()
                    ->form(fn (SupplierDocument $record): array => [
                        Select::make('document_state_id')
                            ->label('Nuevo estado')
                            ->prefixIcon('heroicon-o-flag')
                            ->options(function () use ($record): array {
                                $allowed = $record->documentState?->transiciones_permitidas ?? [];

                                return DocumentState::query()
                                    ->whereIn('nombre', $allowed)
                                    ->pluck('etiqueta', 'id')
                                    ->all();
                            })
                            ->required(),
                        Textarea::make('notas')
                            ->label('Notas')
                            ->placeholder('Opcional')
                            ->rows(3),
                    ])
                    ->action(function (array $data, SupplierDocument $record, DocumentReviewService $service): void {
                        $service->changeState($record, (int) $data['document_state_id'], $data['notas'] ?? null);
                        Notification::make()->success()->title('Estado actualizado')->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_approve')
                        ->label('Aprobar seleccionados')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalDescription('Cada proveedor recibirá una notificación por correo.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records, DocumentReviewService $service): void {
                            $ok = 0;
                            $fail = 0;
                            foreach ($records as $record) {
                                if (! $record->archivo_path) {
                                    $fail++;

                                    continue;
                                }
                                try {
                                    $service->approve($record);
                                    $ok++;
                                } catch (\Throwable) {
                                    $fail++;
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title("{$ok} documento(s) aprobado(s)")
                                ->body($fail > 0 ? "{$fail} se omitieron (sin archivo o transición inválida)." : null)
                                ->send();
                        }),

                    BulkAction::make('bulk_reject')
                        ->label('Rechazar seleccionados')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->deselectRecordsAfterCompletion()
                        ->form([
                            Textarea::make('motivo')
                                ->label('Motivo del rechazo')
                                ->placeholder('Se aplicará el mismo motivo a todos los documentos seleccionados.')
                                ->required()
                                ->rows(4),
                        ])
                        ->action(function (array $data, Collection $records, DocumentReviewService $service): void {
                            $ok = 0;
                            $fail = 0;
                            foreach ($records as $record) {
                                if (! $record->archivo_path) {
                                    $fail++;

                                    continue;
                                }
                                try {
                                    $service->reject($record, $data['motivo']);
                                    $ok++;
                                } catch (\Throwable) {
                                    $fail++;
                                }
                            }

                            Notification::make()
                                ->danger()
                                ->title("{$ok} documento(s) rechazado(s)")
                                ->body($fail > 0 ? "{$fail} se omitieron (sin archivo o transición inválida)." : null)
                                ->send();
                        }),
                ]),
            ]);
    }
}
