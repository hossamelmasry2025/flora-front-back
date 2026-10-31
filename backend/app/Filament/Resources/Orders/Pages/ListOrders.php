<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        $counts = Order::query()->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        $tabs = ['all' => Tab::make('الكل')];

        $tabs['today'] = Tab::make('توصيل النهارده')
            ->icon('heroicon-o-calendar')
            ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('delivery_date', today())->where('status', '!=', 'cancelled'));

        foreach (Order::STATUSES as $status => $label) {
            $tabs[$status] = Tab::make($label)
                ->badge($counts[$status] ?? null)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', $status));
        }

        return $tabs;
    }
}
