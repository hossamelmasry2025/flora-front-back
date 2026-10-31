<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * تعديل الطلب: الحالة وبيانات التوصيل والكارت فقط (الأسعار والمنتجات ثابتة).
 */
class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('الحالة')->schema([
                    Select::make('status')->label('حالة الطلب')->options(Order::STATUSES)->required()->native(false),
                    Select::make('payment_status')->label('حالة الدفع')->options(Order::PAYMENT_STATUSES)->required()->native(false),
                    Textarea::make('admin_notes')->label('ملاحظات داخلية (لا تظهر للعميل)')->rows(3),
                ]),
                Section::make('التوصيل')->schema([
                    TextInput::make('recipient_name')->label('اسم المستلم')->required(),
                    TextInput::make('recipient_phone')->label('موبايل المستلم')->required(),
                    Textarea::make('address')->label('العنوان')->required()->rows(2),
                    DatePicker::make('delivery_date')->label('يوم التوصيل'),
                    TextInput::make('delivery_time')->label('الوقت'),
                ]),
                Section::make('الكارت')->schema([
                    Textarea::make('card_message')->label('رسالة الكارت')->rows(4),
                ])->columnSpanFull(),
            ]);
    }
}
