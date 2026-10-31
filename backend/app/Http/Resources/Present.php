<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Product;
use App\Support\Frontend;
use App\Support\HomeSections;
use App\Support\Locale;

/**
 * تحويل الموديلز لـ JSON نضيف للفرونت إند (باللغة الحالية).
 */
class Present
{
    public static function productCard(Product $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->tr('name'),
            'slug' => $p->localizedSlug(),
            'short_description' => $p->tr('short_description'),
            'price' => (float) $p->price,
            'sale_price' => $p->hasDiscount() ? (float) $p->sale_price : null,
            'final_price' => $p->finalPrice(),
            'image' => Frontend::media($p->image),
            // صورة تانية بتظهر لما الماوس يقف على الكارت
            'hover_image' => Frontend::media($p->gallery[0] ?? null),
            'in_stock' => $p->in_stock,
            'is_featured' => $p->is_featured,
        ];
    }

    public static function product(Product $p): array
    {
        return self::productCard($p) + [
            'slugs' => $p->slugs(),
            'sku' => $p->sku,
            'description' => $p->tr('description'),
            'gallery' => collect($p->gallery ?? [])->map(fn ($img) => Frontend::media($img))->filter()->values(),
            'details' => collect($p->details ?? [])
                ->filter(fn ($d) => filled($d['label'] ?? null))
                ->map(fn ($d) => ['label' => Locale::pick($d, 'label'), 'value' => Locale::pick($d, 'value')])
                ->values(),
            'categories' => $p->categories->where('is_active', true)->map(fn ($c) => [
                'name' => $c->tr('name'),
                'slug' => $c->localizedSlug(),
            ])->values(),
            'meta_title' => $p->tr('meta_title'),
            'meta_description' => $p->tr('meta_description'),
            'updated_at' => $p->updated_at?->toIso8601String(),
        ];
    }

    public static function categoryCard(Category $c): array
    {
        return [
            'id' => $c->id,
            'name' => $c->tr('name'),
            'slug' => $c->localizedSlug(),
            'description' => $c->tr('description'),
            'image' => Frontend::media($c->image),
            'products_count' => $c->products_count ?? null,
        ];
    }

    public static function category(Category $c): array
    {
        return self::categoryCard($c) + [
            'slugs' => $c->slugs(),
            'content' => $c->tr('content'),
            'banner_image' => Frontend::media($c->banner_image),
            'meta_title' => $c->tr('meta_title'),
            'meta_description' => $c->tr('meta_description'),
            'parent' => $c->parent?->is_active ? ['name' => $c->parent->tr('name'), 'slug' => $c->parent->localizedSlug()] : null,
            'children' => $c->children->where('is_active', true)->map(fn ($child) => self::categoryCard($child))->values(),
            // سكاشن التصنيف نفسه + السكاشن العامة لكل التصنيفات
            'sections_top' => HomeSections::banners('category', $c),
            'sections_bottom' => [],
        ];
    }
}
