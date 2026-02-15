<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\SupplierStatus;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use Carbon\CarbonImmutable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuppliersStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $now = CarbonImmutable::now();

        return [
            Stat::make('Total Proveedores', Supplier::count())
                ->description('Registrados en el sistema')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart($this->getSupplierTrend())
                ->color('primary'),

            Stat::make('Proveedores Activos', Supplier::where('status', SupplierStatus::Active)->count())
                ->description('Con expediente completo')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart($this->getActiveSupplierTrend())
                ->color('success'),

            Stat::make('Pendientes de Registro', Supplier::whereIn('status', [
                SupplierStatus::Created,
                SupplierStatus::Invited,
                SupplierStatus::Registered,
            ])->count())
                ->description('En proceso de incorporación')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Documentos por Vencer', SupplierDocument::whereBetween('fecha_vencimiento', [
                $now,
                $now->addDays(30),
            ])->count())
                ->description('En los próximos 30 días')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }

    /**
     * @return array<int>
     */
    private function getSupplierTrend(): array
    {
        return $this->getDailyCountsForLastWeek(Supplier::query());
    }

    /**
     * @return array<int>
     */
    private function getActiveSupplierTrend(): array
    {
        return $this->getDailyCountsForLastWeek(
            Supplier::where('status', SupplierStatus::Active),
        );
    }

    /**
     * @return array<int>
     */
    private function getDailyCountsForLastWeek(\Illuminate\Database\Eloquent\Builder $query): array
    {
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = CarbonImmutable::now()->subDays($i);
            $counts[] = (clone $query)
                ->whereDate('created_at', '<=', $date)
                ->count();
        }

        return $counts;
    }
}
