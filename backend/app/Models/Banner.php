<?php

namespace App\Models;

use App\Models\Concerns\IsVisualItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** بانر إعلاني — بيظهر في المكان اللي اتختار له */
class Banner extends Model
{
    use IsVisualItem {
        toFrontend as visualToFrontend;
    }

    public const PLACEMENTS = [
        'home' => 'الصفحة الرئيسية',
        'category' => 'صفحات التصنيفات (فوق المنتجات)',
        'products_page' => 'صفحة كل المنتجات',
        'product_page' => 'صفحة المنتج (تحت التفاصيل)',
        'cart' => 'صفحة السلة',
        'all_pages_top' => 'أعلى كل الصفحات (تحت القائمة)',
    ];

    public const SIZES = ['full' => 'عرض كامل', 'half' => 'نص العرض', 'third' => 'تلت العرض'];

    public const HEIGHTS = ['sm' => 'قصير', 'md' => 'متوسط', 'lg' => 'طويل'];

    public function getFillable(): array
    {
        return [...$this->visualFillable(), 'placement', 'category_id', 'size', 'height'];
    }

    protected function casts(): array
    {
        return $this->visualCasts();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePlacement(Builder $query, string $placement): void
    {
        $query->where('placement', $placement);
    }

    public function toFrontend(): array
    {
        return $this->visualToFrontend() + [
            'size' => $this->size ?? 'full',
            'height' => $this->height ?? 'md',
        ];
    }
}
