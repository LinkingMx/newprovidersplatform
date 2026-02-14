<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\Actions\AsignarDocumentosAction;
use App\Filament\Resources\Suppliers\SupplierResource;
use App\Models\SupplierInvitation;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            AsignarDocumentosAction::make(),

            Action::make('resendInvitation')
                ->label('Reenviar Invitación')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function (): void {
                    $supplier = $this->record;

                    if ($supplier->isActive()) {
                        Notification::make()
                            ->danger()
                            ->title('No se puede reenviar')
                            ->body('El proveedor ya está activo')
                            ->send();

                        return;
                    }

                    $token = bin2hex(random_bytes(32));

                    SupplierInvitation::create([
                        'supplier_id' => $supplier->id,
                        'token' => $token,
                        'sent_at' => now(),
                        'expires_at' => now()->addDays(7),
                    ]);

                    $supplier->update(['status' => 'invited']);

                    Notification::make()
                        ->success()
                        ->icon('heroicon-o-check-circle')
                        ->title('Invitación Enviada')
                        ->body('El proveedor recibirá un email con instrucciones')
                        ->send();
                }),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['new_password'])) {
            $data['password_hash'] = $data['new_password'];
            unset($data['new_password']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
