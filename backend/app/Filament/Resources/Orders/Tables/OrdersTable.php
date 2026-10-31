<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\Support\OrderStatus;
use App\Models\Order;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->poll('30s')
            ->columns([
                TextColumn::make('number')->label('رقم الطلب')->searchable()->weight('bold')->copyable()
                    ->description(fn (Order $r) => $r->locale === 'en' ? '🇬🇧 English' : null),
                TextColumn::make('sender_name')->label('المرسل')->searchable(['sender_name', 'sender_phone'])
                    ->description(fn (Order $r) => $r->sender_phone),
                TextColumn::make('recipient_name')->label('المستلم')->searchable(['recipient_name', 'recipient_phone'])
                    ->description(fn (Order $r) => $r->delivery_zone_name),
                TextColumn::make('delivery_date')->label('موعد التوصيل')->date('d/m/Y')->sortable()
                    ->description(fn (Order $r) => $r->delivery_time),
                TextColumn::make('total')->label('الإجمالي')->money('EGP', locale: 'en')->sortable(),
                TextColumn::make('payment_method')->label('الدفع')
                    ->formatStateUsing(fn ($state) => Order::PAYMENT_METHODS[$state] ?? $state)
                    ->description(fn (Order $r) => Order::PAYMENT_STATUSES[$r->payment_status] ?? null)
                    ->badge()
                    ->color(fn (Order $r) => OrderStatus::paymentColor($r->payment_status)),
                TextColumn::make('status')->label('الحالة')->badge()
                    ->formatStateUsing(fn ($state) => Order::STATUSES[$state] ?? $state)
                    ->color(fn ($state) => OrderStatus::color($state)),
                TextColumn::make('created_at')->label('تاريخ الطلب')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('payment_method')->label('طريقة الدفع')->options(Order::PAYMENT_METHODS),
                SelectFilter::make('payment_status')->label('حالة الدفع')->options(Order::PAYMENT_STATUSES),
                SelectFilter::make('delivery_zone_id')->label('المنطقة')->relationship('deliveryZone', 'name'),
                Filter::make('delivery_date')
                    ->schema([DatePicker::make('date')->label('موعد التوصيل')])
                    ->query(fn (Builder $query, array $data) => $query->when($data['date'] ?? null, fn ($q, $d) => $q->whereDate('delivery_date', $d))),
            ])
            ->recordUrl(fn (Order $r) => route('filament.admin.resources.orders.view', $r))
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    OrderStatus::changeAction(),
                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
