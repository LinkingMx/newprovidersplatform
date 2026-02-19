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
            ['label' => 'Invitación enviada', 'icon' => 'heroicon-o-paper-airplane', 'done' => $supplier->invitations()->whereNotNull('sent_at')->exists()],
            ['label' => 'Contraseña establecida', 'icon' => 'heroicon-o-lock-closed', 'done' => $supplier->password_hash !== null],
            ['label' => 'Perfil completado', 'icon' => 'heroicon-o-user-circle', 'done' => in_array($supplier->status, [SupplierStatus::ProfileCompleted, SupplierStatus::Active])],
            ['label' => 'Documentos asignados', 'icon' => 'heroicon-o-document-check', 'done' => $supplier->documents()->exists()],
            ['label' => 'Cuenta activa', 'icon' => 'heroicon-o-check-badge', 'done' => $supplier->status === SupplierStatus::Active],
        ];

        $completedCount = collect($checks)->filter(fn (array $c): bool => $c['done'])->count();
        $progressPercent = (int) round(($completedCount / count($checks)) * 100);

        return [
            'invitation' => $invitationData,
            'checks' => $checks,
            'status' => $supplier->status,
            'completedCount' => $completedCount,
            'totalSteps' => count($checks),
            'progressPercent' => $progressPercent,
        ];
    }
}
