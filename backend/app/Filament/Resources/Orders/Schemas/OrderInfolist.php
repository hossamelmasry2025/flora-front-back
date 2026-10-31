<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Filament\Resources\Orders\Support\OrderStatus;
use App\Models\Order;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make([
                    Section::make('المنتجات')->icon('heroicon-o-gift')->schema([
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->schema([
                                ImageEntry::make('product_image')->hiddenLabel()->disk('public')->square()->imageSize(60),
                                TextEntry::make('product_name')->label('المنتج')->weight('bold'),
                                TextEntry::make('quantity')->label('الكمية'),
                                TextEntry::make('price')->label('السعر')->money('EGP', locale: 'en'),
                                TextEntry::make('total')->label('الإجمالي')->money('EGP', locale: 'en'),
                            ])
                            ->columns(5),
                        Grid::make(3)->schema([
                            TextEntry::make('subtotal')->label('المنتجات')->money('EGP', locale: 'en'),
                            TextEntry::make('delivery_fee')->label('التوصيل')->money('EGP', locale: 'en'),
                            TextEntry::make('total')->label('الإجمالي')->money('EGP', locale: 'en')->weight('bold')->size('lg')->color('primary'),
                        ]),
                    ]),

                    Section::make('رسالة الكارت')->icon('heroicon-o-envelope')->schema([
                        TextEntry::make('card_message')->hiddenLabel()
                            ->placeholder('بدون كارت')
                            ->extraAttributes(['style' => 'white-space: pre-line; font-size: 1.05rem; line-height: 1.9'])
                            ->copyable(),
                    ]),

                    Grid::make(2)->schema([
                        Section::make('المرسل')->icon('heroicon-o-user')->schema([
                            TextEntry::make('sender_name')->label('الاسم'),
                            TextEntry::make('sender_phone')->label('الموبايل')
                                ->url(fn (Order $r) => OrderStatus::whatsappUrl($r->sender_phone, "أهلاً {$r->sender_name}، بخصوص طلبك رقم {$r->number}"), true)
                                ->icon('heroicon-o-chat-bubble-left-right')->color('success'),
                            TextEntry::make('sender_email')->label('الإيميل')->placeholder('—'),
                            IconEntry::make('hide_sender')->label('إخفاء اسم المرسل عن المستلم')->boolean(),
                        ]),
                        Section::make('المستلم والتوصيل')->icon('heroicon-o-map-pin')->schema([
                            TextEntry::make('recipient_name')->label('الاسم'),
                            TextEntry::make('recipient_phone')->label('الموبايل')
                                ->url(fn (Order $r) => 'tel:'.$r->recipient_phone)
                                ->icon('heroicon-o-phone'),
                            TextEntry::make('delivery_zone_name')->label('المنطقة')->placeholder('—'),
                            TextEntry::make('address')->label('العنوان')->extraAttributes(['style' => 'white-space: pre-line']),
                            TextEntry::make('delivery_date')->label('يوم التوصيل')->date('l d/m/Y')->placeholder('أقرب وقت'),
                            TextEntry::make('delivery_time')->label('الوقت')->placeholder('—'),
                        ]),
                    ]),

                    Section::make('ملاحظات العميل')->schema([
                        TextEntry::make('notes')->hiddenLabel()->placeholder('لا يوجد'),
                    ])->collapsible(),
                ])->columnSpan(2),

                Group::make([
                    Section::make('الحالة')->schema([
                        TextEntry::make('number')->label('رقم الطلب')->weight('bold')->copyable(),
                        TextEntry::make('status')->label('حالة الطلب')->badge()
                            ->formatStateUsing(fn ($state) => Order::STATUSES[$state] ?? $state)
                            ->color(fn ($state) => OrderStatus::color($state)),
                        TextEntry::make('created_at')->label('تاريخ الطلب')->dateTime('d/m/Y h:i A'),
                        TextEntry::make('locale')->label('لغة العميل')->badge()->color('gray')
                            ->formatStateUsing(fn ($state) => $state === 'en' ? 'English' : 'عربي'),
                    ]),
                    Section::make('الدفع')->icon('heroicon-o-banknotes')->schema([
                        TextEntry::make('payment_method')->label('الطريقة')
                            ->formatStateUsing(fn ($state) => Order::PAYMENT_METHODS[$state] ?? $state),
                        TextEntry::make('payment_status')->label('الحالة')->badge()
                            ->formatStateUsing(fn ($state) => Order::PAYMENT_STATUSES[$state] ?? $state)
                            ->color(fn ($state) => OrderStatus::paymentColor($state)),
                        ImageEntry::make('payment_proof')
                            ->label('سكرين شوت التحويل')
                            ->disk('local')
                            ->visibility('private')
                            ->imageWidth('100%')
                            ->imageHeight('auto')
                            ->url(fn (Order $r) => $r->payment_proof ? route('admin.payment-proof', $r) : null, true)
                            ->visible(fn (Order $r) => filled($r->payment_proof)),
                    ]),
                    Section::make('سجل الحالة')->icon('heroicon-o-clock')->schema([
                        RepeatableEntry::make('statusLogs')
                            ->hiddenLabel()
                            ->contained(false)
                            ->schema([
                                TextEntry::make('status')->hiddenLabel()->badge()
                                    ->formatStateUsing(fn ($state) => Order::STATUSES[$state] ?? $state)
                                    ->color(fn ($state) => OrderStatus::color($state))
                                    ->inlineLabel(false),
                                TextEntry::make('created_at')->hiddenLabel()->dateTime('d/m h:i A')->color('gray')->size('sm'),
                            ])
                            ->columns(2),
                    ]),
                    Section::make('ملاحظات داخلية')->schema([
                        TextEntry::make('admin_notes')->hiddenLabel()->placeholder('لا يوجد'),
                    ]),
                ])->columnSpan(1),
            ]);
    }
}
