<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\SupplierStatus;
use App\Models\Supplier;
use Filament\Widgets\ChartWidget;

class SuppliersByStatusChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Proveedores por Estado';

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        $colors = [];

        foreach (SupplierStatus::cases() as $status) {
            $count = Supplier::where('status', $status)->count();
            $data[] = $count;
            $labels[] = $status->label();
            $colors[] = $this->statusToHex($status);
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getOptions(): ?array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }

    private function statusToHex(SupplierStatus $status): string
    {
        return match ($status) {
            SupplierStatus::Created => '#6b7280',
            SupplierStatus::Invited => '#eab308',
            SupplierStatus::Registered => '#3b82f6',
            SupplierStatus::ProfileCompleted => '#f97316',
            SupplierStatus::Active => '#22c55e',
        };
    }
}
