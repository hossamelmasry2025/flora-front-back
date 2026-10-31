<?php

namespace App\Models\Concerns;

use App\Support\Frontend;
use Illuminate\Database\Eloquent\Builder;

/**
 * مشترك بين السلايدر والبانرات: صورة + كلام + زرار + شكل الكلام + جدولة الظهور.
 */
trait IsVisualItem
{
    use HasTranslations, RevalidatesFrontend;

    public const POSITIONS = ['start' => 'على الجنب', 'center' => 'في النص', 'end' => 'الجنب التاني'];

    public const TEXT_COLORS = ['light' => 'أبيض', 'dark' => 'غامق'];

    protected function visualFillable(): array
    {
        return [
            'image', 'mobile_image', 'title', 'title_en', 'subtitle', 'subtitle_en',
            'button_text', 'button_text_en', 'link', 'text_position', 'text_color', 'overlay',
            'is_active', 'sort_order', 'starts_at', 'ends_at',
        ];
    }

    protected function visualCasts(): array
    {
        return [
            'is_active' => 'boolean',
            'overlay' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /** ظاهر دلوقتي: مفعّل وفي فترة الظهور لو متحددة */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** شكل موحد للفرونت إند */
    public function toFrontend(): array
    {
        return [
            'id' => $this->id,
            'image' => Frontend::media($this->image),
            'mobile_image' => Frontend::media($this->mobile_image) ?? Frontend::media($this->image),
            'title' => $this->tr('title'),
            'subtitle' => $this->tr('subtitle'),
            'button_text' => $this->tr('button_text'),
            'link' => $this->link,
            'text_position' => $this->text_position ?? 'start',
            'text_color' => $this->text_color ?? 'light',
            'overlay' => (int) ($this->overlay ?? 45),
        ];
    }
}
