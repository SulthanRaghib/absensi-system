<?php

namespace App\Filament\Widgets;

use App\Models\Absence;
use Filament\Widgets\ChartWidget;

class AdminLast7Chart extends ChartWidget
{
    protected static ?int $sort = 11;

    protected ?string $pollingInterval = null;

    // occupy 4 columns (right side)
    protected int | string | array $columnSpan = 11;

    // fix max height so it doesn't force the adjacent widget to stretch
    protected ?string $maxHeight = '20rem';

    // chart options to remove extra padding and make bars fill width nicely
    protected ?array $options = [
        'maintainAspectRatio' => false,
        'layout' => [
            'padding' => [
                'left' => 6,
                'right' => 6,
                'top' => 6,
                'bottom' => 6,
            ],
        ],
        'plugins' => [
            'legend' => [
                'display' => true,
                'position' => 'bottom',
            ],
        ],
        'scales' => [
            'x' => [
                'grid' => [
                    'display' => false,
                ],
                'ticks' => [
                    'padding' => 8,
                ],
            ],
            'y' => [
                'beginAtZero' => true,
                'ticks' => [
                    'stepSize' => 1,
                ],
            ],
        ],
        'elements' => [
            'bar' => [
                'borderWidth' => 0,
                'borderRadius' => 8,
                'barPercentage' => 0.7,
                'categoryPercentage' => 0.7,
            ],
        ],
    ];

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $labels[] = $d->format('d M');
            $data[] = Absence::whereDate('tanggal', $d->toDateString())->whereNotNull('jam_masuk')->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Hadir',
                    'data' => $data,
                    'backgroundColor' => 'rgba(250, 204, 21, 0.95)',
                    'borderRadius' => 6,
                ],
            ],
        ];
    }
}
