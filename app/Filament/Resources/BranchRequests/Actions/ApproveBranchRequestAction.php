<?php

namespace App\Filament\Resources\BranchRequests\Actions;

use App\Enums\BranchRequestStatus;
use App\Mail\BranchRequestResolvedMailable;
use App\Models\BranchRequest;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ApproveBranchRequestAction
{
    public static function make(): Action
    {
        return Action::make('approveBranchRequest')
            ->label('Aprobar')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Aprobar Solicitud')
            ->modalDescription('Se asignará la sucursal al proveedor automáticamente.')
            ->modalSubmitActionLabel('Aprobar')
            ->visible(fn (BranchRequest $record): bool => $record->isPending())
            ->action(function (BranchRequest $record): void {
                $supplier = $record->supplier;

                if ($supplier->branches()->where('branches.id', $record->branch_id)->exists()) {
                    Notification::make()
                        ->warning()
                        ->title('Ya asignada')
                        ->body('El proveedor ya tiene esta sucursal asignada.')
                        ->send();

                    return;
                }

                $record->update([
                    'status' => BranchRequestStatus::Approved,
                    'resolved_at' => now(),
                    'resolved_by' => Auth::id(),
                ]);

                $supplier->branches()->attach($record->branch_id, [
                    'assigned_at' => now(),
                    'assigned_by' => Auth::id(),
                ]);

                Mail::to($supplier->email)->send(
                    new BranchRequestResolvedMailable($record->fresh())
                );

                Notification::make()
                    ->success()
                    ->icon('heroicon-o-check-circle')
                    ->title('Solicitud Aprobada')
                    ->body("Sucursal asignada a {$supplier->name}")
                    ->send();
            });
    }
}
