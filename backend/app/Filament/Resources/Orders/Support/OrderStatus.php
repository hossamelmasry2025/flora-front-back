<?php

namespace App\Filament\Resources\Orders\Support;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class OrderStatus
{
    public static function color(string $status): string
    {
        return match ($status) {
            'pending' => 'danger',
            'confirmed' => 'info',
            'preparing' => 'warning',
            'out_for_delivery' => 'primary',
            'delivered' => 'success',
            default => 'gray',
        };
    }

    public static function paymentColor(string $status): string
    {
        return match ($status) {
            'paid' => 'success',
            'under_review' => 'warning',
            'refunded' => 'gray',
            default => 'danger',
        };
    }

    /** زرار تغيير الحالة (بيستخدم في الجدول وصفحة الطلب) */
    public static function changeAction(): Action
    {
        return Action::make('changeStatus')
            ->label('تغيير الحالة')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->fillForm(fn (Order $record) => [
                'status' => $record->status,
                'payment_status' => $record->payment_status,
            ])
            ->schema([
                Select::make('status')->label('حالة الطلب')->options(Order::STATUSES)->required()->native(false),
                Select::make('payment_status')->label('حالة الدفع')->options(Order::PAYMENT_STATUSES)->required()->native(false),
            ])
            ->action(function (Order $record, array $data) {
                $record->update($data);
                Notification::make()->title('تم تحديث الطلب')->success()->send();
            });
    }

    /** رابط واتساب جاهز للتواصل مع العميل */
    public static function whatsappUrl(string $phone, string $message = ''): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '0')) {
            $digits = '2'.$digits; // رقم مصري محلي → دولي
        }

        return 'https://wa.me/'.$digits.($message ? '?text='.rawurlencode($message) : '');
    }
}
