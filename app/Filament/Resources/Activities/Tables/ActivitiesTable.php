<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn () => Activity::query()->with(['causer', 'subject']))
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d M Y H:i')
                    ->description(fn (Activity $r): ?string => $r->created_at?->diffForHumans())
                    ->sortable(),

                TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->icon(fn (?string $state): string => match ($state) {
                        'created' => 'heroicon-o-plus-circle',
                        'updated' => 'heroicon-o-pencil-square',
                        'deleted' => 'heroicon-o-trash',
                        'restored' => 'heroicon-o-arrow-uturn-left',
                        default => 'heroicon-o-bolt',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'restored' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'created' => 'Creado',
                        'updated' => 'Actualizado',
                        'deleted' => 'Eliminado',
                        'restored' => 'Restaurado',
                        default => $state ?? '—',
                    })
                    ->sortable(),

                TextColumn::make('subject_type')
                    ->label('Modelo')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->description(fn (Activity $r): ?string => $r->subject_id ? "#{$r->subject_id}" : null)
                    ->icon('heroicon-o-cube')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('subject_label')
                    ->label('Sujeto')
                    ->state(function (Activity $r): string {
                        $s = $r->subject;
                        if (! $s) {
                            return $r->subject_id ? 'Registro eliminado' : '—';
                        }

                        return $s->name
                            ?? $s->nombre
                            ?? $s->title
                            ?? $s->email
                            ?? "#{$r->subject_id}";
                    })
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('causer.name')
                    ->label('Usuario')
                    ->default('Sistema')
                    ->icon(fn (Activity $r): string => $r->causer ? 'heroicon-o-user' : 'heroicon-o-cog')
                    ->description(fn (Activity $r): ?string => $r->causer?->email)
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('log_name')
                    ->label('Canal')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Evento')
                    ->multiple()
                    ->options([
                        'created' => 'Creado',
                        'updated' => 'Actualizado',
                        'deleted' => 'Eliminado',
                        'restored' => 'Restaurado',
                    ]),

                SelectFilter::make('subject_type')
                    ->label('Modelo')
                    ->multiple()
                    ->options(fn (): array => Activity::query()
                        ->distinct()
                        ->pluck('subject_type')
                        ->filter()
                        ->mapWithKeys(fn (string $cls) => [$cls => class_basename($cls)])
                        ->all()),

                Filter::make('rango')
                    ->label('Rango de fechas')
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('desde')->label('Desde'),
                        \Filament\Forms\Components\DatePicker::make('hasta')->label('Hasta'),
                    ])
                    ->query(function (Builder $q, array $data): Builder {
                        return $q
                            ->when($data['desde'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['hasta'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton(),
            ])
            ->toolbarActions([])
            ->paginationPageOptions([25, 50, 100, 250]);
    }
}
