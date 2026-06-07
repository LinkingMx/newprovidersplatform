<?php

namespace App\Filament\Resources\DocumentReviews\Pages;

use App\Filament\Resources\DocumentReviews\DocumentReviewResource;
use App\Models\DocumentState;
use App\Models\SupplierDocument;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDocumentReviews extends ListRecords
{
    protected static string $resource = DocumentReviewResource::class;

    public function getTitle(): string
    {
        return 'Revisión de Documentos';
    }

    public function getHeading(): string
    {
        return 'Bandeja de Revisión';
    }

    public function getSubheading(): ?string
    {
        return 'Documentos subidos por proveedores pendientes de aprobación.';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'pending';
    }

    public function getTabs(): array
    {
        $pendingStates = [DocumentState::PENDIENTE, DocumentState::EN_REVISION];

        $countByStates = fn (array $stateIds): int => SupplierDocument::query()
            ->whereIn('document_state_id', $stateIds)
            ->count();

        return [
            'pending' => Tab::make('Por revisar')
                ->icon('heroicon-o-clock')
                ->badge($countByStates($pendingStates))
                ->badgeColor('warning')
                ->modifyQueryUsing(function (Builder $query) use ($pendingStates): Builder {
                    return $query->whereIn('document_state_id', $pendingStates);
                }),

            'approved' => Tab::make('Aprobados')
                ->icon('heroicon-o-check-circle')
                ->badge($countByStates([DocumentState::APROBADO]))
                ->badgeColor('success')
                ->modifyQueryUsing(function (Builder $query): Builder {
                    return $query->where('document_state_id', DocumentState::APROBADO);
                }),

            'rejected' => Tab::make('Rechazados')
                ->icon('heroicon-o-x-circle')
                ->badge($countByStates([DocumentState::RECHAZADO]))
                ->badgeColor('danger')
                ->modifyQueryUsing(function (Builder $query): Builder {
                    return $query->where('document_state_id', DocumentState::RECHAZADO);
                }),

            'all' => Tab::make('Todos')
                ->icon('heroicon-o-rectangle-stack'),
        ];
    }
}
