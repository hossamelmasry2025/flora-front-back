<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = [
        'pending' => 'جديد',
        'confirmed' => 'تم التأكيد',
        'preparing' => 'جاري التجهيز',
        'out_for_delivery' => 'خرج للتوصيل',
        'delivered' => 'تم التوصيل',
        'cancelled' => 'ملغي',
    ];

    public const PAYMENT_METHODS = [
        'cod' => 'الدفع عند الاستلام',
        'transfer' => 'تحويل (إنستاباي / محفظة)',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid' => 'لم يتم الدفع',
        'under_review' => 'إيصال قيد المراجعة',
        'paid' => 'مدفوع',
        'refunded' => 'مسترد',
    ];

    /** نفس الحالات بالإنجليزي (للعميل في النسخة الإنجليزي من الموقع) */
    private const LABELS_EN = [
        'pending' => 'Order received', 'confirmed' => 'Confirmed', 'preparing' => 'Being prepared',
        'out_for_delivery' => 'Out for delivery', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled',
        'cod' => 'Cash on delivery', 'transfer' => 'Bank / wallet transfer',
        'unpaid' => 'Unpaid', 'under_review' => 'Receipt under review', 'paid' => 'Paid', 'refunded' => 'Refunded',
    ];

    protected $fillable = [
        'number', 'locale', 'status', 'payment_method', 'payment_status', 'payment_proof',
        'sender_name', 'sender_phone', 'sender_email', 'hide_sender',
        'recipient_name', 'recipient_phone', 'delivery_zone_id', 'delivery_zone_name',
        'address', 'delivery_date', 'delivery_time',
        'card_message', 'notes', 'admin_notes',
        'subtotal', 'delivery_fee', 'total',
    ];

    protected function casts(): array
    {
        return [
            'hide_sender' => 'boolean',
            'delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->number ??= static::generateNumber();
        });

        // كل تغيير في الحالة بيتسجل بوقته عشان العميل يتابعه
        static::created(fn (Order $order) => $order->statusLogs()->create(['status' => $order->status ?? 'pending']));
        static::updated(function (Order $order) {
            if ($order->wasChanged('status')) {
                $order->statusLogs()->create(['status' => $order->status]);
            }
        });
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'FL'.now()->format('ymd').random_int(1000, 9999);
        } while (static::query()->where('number', $number)->exists());

        return $number;
    }

    /** اسم الحالة / طريقة الدفع باللغة الحالية */
    public static function label(string $value): string
    {
        if (app()->getLocale() === 'en') {
            return self::LABELS_EN[$value] ?? $value;
        }

        return self::STATUSES[$value] ?? self::PAYMENT_METHODS[$value] ?? self::PAYMENT_STATUSES[$value] ?? $value;
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('id');
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }
}
