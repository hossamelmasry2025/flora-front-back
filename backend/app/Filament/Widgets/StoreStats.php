<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $valid = fn () => Order::query()->where('status', '!=', 'cancelled');

        $todayRevenue = $valid()->whereDate('created_at', today())->sum('total');
        $monthRevenue = $valid()->where('created_at', '>=', now()->startOfMonth())->sum('total');

        $trend = collect(range(6, 0))->map(fn ($d) => $valid()->whereDate('created_at', today()->subDays($d))->count())->all();

        return [
            Stat::make('طلبات جديدة', Order::where('status', 'pending')->count())
                ->description('محتاجة تأكيد')
                ->descriptionIcon('heroicon-o-bell-alert')
                ->color('danger')
                ->url(route('filament.admin.resources.orders.index', ['tab' => 'pending'])),
            Stat::make('توصيل النهارده', $valid()->whereDate('delivery_date', today())->whereNotIn('status', ['delivered'])->count())
                ->description('طلب لسه ما اتسلمش')
                ->descriptionIcon('heroicon-o-truck')
                ->color('warning')
                ->url(route('filament.admin.resources.orders.index', ['tab' => 'today'])),
            Stat::make('مبيعات النهارده', number_format($todayRevenue).' ج.م')
                ->description('آخر 7 أيام')
                ->chart($trend)
                ->color('success'),
            Stat::make('مبيعات الشهر', number_format($monthRevenue).' ج.م')
                ->description(Product::where('is_active', true)->count().' منتج نشط')
                ->descriptionIcon('heroicon-o-gift')
                ->color('primary'),
        ];
    }
}
