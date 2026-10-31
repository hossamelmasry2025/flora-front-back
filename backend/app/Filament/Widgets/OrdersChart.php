<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class OrdersChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'المبيعات آخر 30 يوم';

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 2];

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $from = today()->subDays(29);

        $rows = Order::query()
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $from)
            ->get(['total', 'created_at'])
            ->groupBy(fn (Order $o) => $o->created_at->toDateString());

        $days = collect(range(0, 29))->map(fn ($i) => $from->copy()->addDays($i));

        return [
            'datasets' => [
                [
                    'label' => 'المبيعات (ج.م)',
                    'data' => $days->map(fn (Carbon $d) => (float) ($rows[$d->toDateString()] ?? collect())->sum('total'))->all(),
                    'borderColor' => '#b02f59',
                    'backgroundColor' => 'rgba(204, 70, 112, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                    'pointRadius' => 0,
                ],
                [
                    'label' => 'عدد الطلبات',
                    'data' => $days->map(fn (Carbon $d) => ($rows[$d->toDateString()] ?? collect())->count())->all(),
                    'borderColor' => '#b8935a',
                    'backgroundColor' => 'transparent',
                    'tension' => 0.35,
                    'pointRadius' => 0,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $days->map(fn (Carbon $d) => $d->format('d/m'))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => true, 'position' => 'bottom']],
            'scales' => [
                'y' => ['beginAtZero' => true, 'grid' => ['color' => 'rgba(0,0,0,0.04)']],
                'y1' => ['beginAtZero' => true, 'position' => 'right', 'grid' => ['display' => false], 'ticks' => ['precision' => 0]],
                'x' => ['grid' => ['display' => false]],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
