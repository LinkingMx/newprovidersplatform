<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\DocumentState;
use App\Models\SupplierDocument;
use Filament\Widgets\ChartWidget;

class DocumentsByStateChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Documentos por Estado';

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = '30s';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $states = DocumentState::all();

        $data = [];
        $labels = [];
        $colors = [];

        foreach ($states as $state) {
            $count = SupplierDocument::where('document_state_id', $state->id)->count();
            $data[] = $count;
            $labels[] = $state->nombre;
            $colors[] = $this->colorToHex($state->color);
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

    private function colorToHex(string $color): string
    {
        return match ($color) {
            'gray' => '#6b7280',
            'blue' => '#3b82f6',
            'green' => '#22c55e',
            'red' => '#ef4444',
            'yellow' => '#eab308',
            'orange' => '#f97316',
            default => '#6b7280',
        };
    }
}
