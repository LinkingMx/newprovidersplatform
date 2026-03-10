<?php

namespace App\Filament\Resources\BranchRequests\Actions;

use App\Enums\BranchRequestStatus;
use App\Mail\BranchRequestResolvedMailable;
use App\Models\BranchRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RejectBranchRequestAction
{
    public static function make(): Action
    {
        return Action::make('rejectBranchRequest')
            ->label('Rechazar')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Rechazar Solicitud')
            ->modalDescription('Indica el motivo del rechazo. El proveedor será notificado.')
            ->modalSubmitActionLabel('Rechazar')
            ->form([
                Textarea::make('notas_admin')
                    ->label('Motivo del Rechazo')
                    ->required()
                    ->maxLength(1000)
                    ->placeholder('Escribe el motivo del rechazo...')
                    ->rows(3),
            ])
            ->visible(fn (BranchRequest $record): bool => $record->isPending())
            ->action(function (BranchRequest $record, array $data): void {
                $record->update([
                    'status' => BranchRequestStatus::Rejected,
                    'notas_admin' => $data['notas_admin'],
                    'resolved_at' => now(),
                    'resolved_by' => Auth::id(),
                ]);

                Mail::to($record->supplier->email)->send(
                    new BranchRequestResolvedMailable($record->fresh())
                );

                Notification::make()
                    ->success()
                    ->icon('heroicon-o-check-circle')
                    ->title('Solicitud Rechazada')
                    ->body("Se notificó al proveedor {$record->supplier->name}")
                    ->send();
            });
    }
}
