<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Support\OrderStatus;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return 'طلب #'.$this->record->number;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirm')
                ->label('تأكيد الطلب')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (Order $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->action(fn (Order $record) => $record->update(['status' => 'confirmed'])),
            Action::make('markPaid')
                ->label('تأكيد استلام التحويل')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn (Order $record) => $record->payment_status === 'under_review')
                ->requiresConfirmation()
                ->action(fn (Order $record) => $record->update(['payment_status' => 'paid'])),
            OrderStatus::changeAction(),
            Action::make('whatsapp')
                ->label('واتساب المرسل')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('gray')
                ->url(fn (Order $record) => OrderStatus::whatsappUrl(
                    $record->sender_phone,
                    "أهلاً {$record->sender_name} 🌸\nبخصوص طلبك رقم {$record->number}"
                ), true),
            Action::make('print')
                ->label('طباعة')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (Order $record) => route('admin.orders.print', $record), true),
            EditAction::make(),
        ];
    }
}
