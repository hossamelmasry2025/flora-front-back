<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\Support\OrderStatus;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOrders extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'آخر الطلبات';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest('id')->limit(10))
            ->paginated(false)
            ->poll('30s')
            ->recordUrl(fn (Order $r) => route('filament.admin.resources.orders.view', $r))
            ->columns([
                TextColumn::make('number')->label('رقم الطلب')->weight('bold'),
                TextColumn::make('sender_name')->label('المرسل'),
                TextColumn::make('recipient_name')->label('المستلم')->description(fn (Order $r) => $r->delivery_zone_name),
                TextColumn::make('delivery_date')->label('التوصيل')->date('d/m')->description(fn (Order $r) => $r->delivery_time),
                TextColumn::make('total')->label('الإجمالي')->money('EGP', locale: 'en'),
                TextColumn::make('payment_method')->label('الدفع')->badge()
                    ->formatStateUsing(fn ($state) => Order::PAYMENT_METHODS[$state] ?? $state)
                    ->color(fn (Order $r) => OrderStatus::paymentColor($r->payment_status)),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn ($state) => Order::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => OrderStatus::color($state)),
                TextColumn::make('created_at')->label('من')->since(),
            ]);
    }
}
