<?php

namespace App\Filament\Resources\Suppliers\Widgets;

use App\Enums\SupplierStatus;
use App\Models\Supplier;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class OnboardingLogWidget extends Widget
{
    protected string $view = 'filament.resources.suppliers.widgets.onboarding-log';

    public ?Model $record = null;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        /** @var Supplier $supplier */
        $supplier = $this->record;

        $invitation = $supplier->invitations()->latest('id')->first();

        $invitationData = null;
        if ($invitation) {
            $invitationData = [
                'sent' => $invitation->sent_at?->format('d M Y H:i') ?? 'No enviada',
                'expires' => $invitation->expires_at->format('d M Y H:i'),
                'is_expired' => $invitation->isExpired(),
                'is_accepted' => $invitation->isAccepted(),
                'accepted_at' => $invitation->accepted_at?->format('d M Y H:i'),
            ];
        }

        $checks = [
            ['label' => 'Invitación enviada', 'done' => $supplier->invitations()->whereNotNull('sent_at')->exists()],
            ['label' => 'Contraseña establecida', 'done' => $supplier->password_hash !== null],
            ['label' => 'Perfil completado', 'done' => in_array($supplier->status, [SupplierStatus::ProfileCompleted, SupplierStatus::Active])],
            ['label' => 'Documentos asignados', 'done' => $supplier->documents()->exists()],
            ['label' => 'Cuenta activa', 'done' => $supplier->status === SupplierStatus::Active],
        ];

        return [
            'invitation' => $invitationData,
            'checks' => $checks,
            'status' => $supplier->status,
        ];
    }
}
