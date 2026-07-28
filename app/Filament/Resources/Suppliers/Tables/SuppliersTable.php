<?php

namespace App\Filament\Resources\Suppliers\Tables;

use App\Enums\SupplierStatus;
use App\Filament\Exports\SupplierExporter;
use App\Http\Controllers\Admin\ImpersonateSupplierController;
use App\Models\Supplier;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

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

                TextColumn::make('rfc')
                    ->label('RFC')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),

                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors(
                        collect(SupplierStatus::cases())
                            ->mapWithKeys(fn (SupplierStatus $s) => [$s->color() => $s->value])
                            ->all()
                    )
                    ->formatStateUsing(fn (SupplierStatus|string $state): string => $state instanceof SupplierStatus ? $state->label() : (SupplierStatus::tryFrom($state)?->label() ?? $state))
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
            ->headerActions([
                ExportAction::make()
                    ->label('Exportar')
                    ->exporter(SupplierExporter::class)
                    // El disco 's3' apunta al bucket de Supabase Storage, que tiene
                    // whitelist de MIME types y rechaza los archivos de trabajo del
                    // export (headers.csv, etc. con 415 Unsupported Media Type).
                    // Estos son archivos temporales, no documentos de proveedores.
                    ->fileDisk('local'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Por Estado')
                    ->options(SupplierStatus::options()),

                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('impersonate')
                    ->label('Ver como proveedor')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->modalHeading(fn (Supplier $record): string => "Ver dashboard como {$record->name}")
                    ->modalDescription('Quedarás autenticado como el proveedor. Verás todo lo que él ve. Para volver al admin usa el botón "Salir de la vista" del banner superior.')
                    ->modalSubmitActionLabel('Iniciar vista')
                    ->visible(fn (): bool => auth()->user()?->can('impersonate_supplier') ?? false)
                    ->action(function (Supplier $record) {
                        $admin = auth()->user();

                        if (! $admin?->can('impersonate_supplier')) {
                            Notification::make()
                                ->danger()
                                ->title('No tienes permiso para esta acción')
                                ->send();

                            return;
                        }

                        if (session()->has(ImpersonateSupplierController::SESSION_KEY)) {
                            Notification::make()
                                ->warning()
                                ->title('Ya estás impersonando a otro proveedor')
                                ->body('Sal de la vista actual antes de iniciar una nueva.')
                                ->send();

                            return;
                        }

                        session()->put(ImpersonateSupplierController::SESSION_KEY, $admin->id);
                        Auth::guard('supplier')->login($record);

                        activity()
                            ->causedBy($admin)
                            ->performedOn($record)
                            ->withProperties([
                                'admin_email' => $admin->email,
                                'supplier_email' => $record->email,
                            ])
                            ->log('impersonation_started');

                        return redirect()->route('dashboard');
                    }),

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
