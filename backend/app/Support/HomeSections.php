<?php

namespace App\Support;

use App\Http\Resources\Present;
use App\Models\Banner;
use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Slide;

/**
 * بيجهز أقسام الصفحة الرئيسية والبانرات للفرونت إند.
 * كل قسم: ['type' => ..., 'title' => ..., 'subtitle' => ..., 'style' => [...], 'data' => [...]]
 */
class HomeSections
{
    /** أقسام الصفحة الرئيسية بالترتيب */
    public static function home(): array
    {
        return HomeSection::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (HomeSection $s) => self::section($s))
            ->filter(fn ($s) => $s && self::hasContent($s))
            ->values()
            ->all();
    }

    /** بانرات مكان معين (فوق منتجات التصنيف، صفحة المنتج، السلة...) كقسم واحد */
    public static function banners(string $placement, ?Category $category = null): array
    {
        $banners = Banner::visible()
            ->placement($placement)
            ->when($placement === 'category', fn ($q) => $q->where(fn ($w) => $w->whereNull('category_id')->orWhere('category_id', $category?->id)))
            ->get();

        if ($banners->isEmpty()) {
            return [];
        }

        return [[
            'type' => 'banners',
            'title' => null,
            'subtitle' => null,
            'style' => ['bg' => 'none', 'spacing' => 'sm', 'align' => 'start'],
            'data' => ['items' => $banners->map->toFrontend()->values()],
        ]];
    }

    /** إعدادات السلايدر (من زرار "إعدادات السلايدر") */
    public static function sliderSettings(): array
    {
        $s = Setting::allCached();

        return [
            'autoplay' => (bool) ($s['slider_autoplay'] ?? true),
            'interval' => max(2, (int) ($s['slider_interval'] ?? 6)),
            'height' => $s['slider_height'] ?? 'lg',
            'arrows' => (bool) ($s['slider_arrows'] ?? true),
            'dots' => (bool) ($s['slider_dots'] ?? true),
            'effect' => $s['slider_effect'] ?? 'fade',
        ];
    }

    private static function section(HomeSection $s): ?array
    {
        $data = $s->settings ?? [];

        $resolved = match ($s->type) {
            'slider' => ['items' => Slide::visible()->get()->map->toFrontend()->values(), 'settings' => self::sliderSettings()],
            'banners' => ['items' => self::bannerItems()],
            'categories' => self::categories($data),
            'products' => self::products($data),
            'features' => ['items' => self::items($data, ['icon'], ['title', 'text'])],
            'text' => ['content' => Locale::pick($data, 'content', '')],
            'testimonials' => ['items' => self::items($data, ['rating'], ['name', 'text'])],
            'faq' => ['items' => self::items($data, [], ['question', 'answer'])],
            'cta' => [
                'button_text' => Locale::pick($data, 'button_text'),
                'link' => $data['link'] ?? null,
                'secondary_text' => Locale::pick($data, 'secondary_text'),
                'secondary_link' => $data['secondary_link'] ?? null,
            ],
            'video' => ['youtube_id' => self::youtubeId($data['url'] ?? '')],
            default => null,
        };

        if ($resolved === null) {
            return null;
        }

        return [
            'type' => $s->type,
            'title' => $s->tr('title'),
            'subtitle' => $s->tr('subtitle'),
            'style' => ['bg' => $s->bg ?? 'none', 'spacing' => $s->spacing ?? 'md', 'align' => $s->align ?? 'start'],
            'data' => $resolved,
        ];
    }

    /** كل البانرات اللي مكانها "الصفحة الرئيسية" بترتيب صفحة البانرات */
    private static function bannerItems(): array
    {
        return Banner::visible()->placement('home')->get()->map->toFrontend()->values()->all();
    }

    /** عناصر مكررة (مميزات / آراء / أسئلة) بالنصوص المترجمة */
    private static function items(array $data, array $plain, array $translated): array
    {
        return collect($data['items'] ?? [])->map(fn ($item) => collect($plain)
            ->mapWithKeys(fn ($k) => [$k => $item[$k] ?? null])
            ->merge(collect($translated)->mapWithKeys(fn ($k) => [$k => Locale::pick($item, $k)]))
            ->all())->values()->all();
    }

    private static function hasContent(array $s): bool
    {
        return match ($s['type']) {
            'slider', 'banners', 'features', 'testimonials', 'faq', 'categories', 'products' => count($s['data']['items'] ?? []) > 0,
            'video' => filled($s['data']['youtube_id'] ?? null),
            'cta' => filled($s['title']) || filled($s['data']['button_text']),
            default => true,
        };
    }

    private static function youtubeId(string $url): ?string
    {
        preg_match('~(?:youtu\.be/|v=|embed/|shorts/)([A-Za-z0-9_-]{11})~', $url, $m);

        return $m[1] ?? null;
    }

    private static function categories(array $data): array
    {
        $ids = $data['category_ids'] ?? [];

        $categories = Category::active()
            ->withCount(['products' => fn ($q) => $q->active()])
            ->when($ids, fn ($q) => $q->whereIn('id', $ids), fn ($q) => $q->where('show_on_home', true)->whereNull('parent_id'))
            ->orderBy('sort_order')
            ->get();

        if ($ids) {
            $categories = $categories->sortBy(fn ($c) => array_search($c->id, $ids))->values();
        }

        return [
            'style' => $data['style'] ?? 'grid',
            'items' => $categories->map(fn ($c) => Present::categoryCard($c))->values(),
        ];
    }

    private static function products(array $data): array
    {
        $source = $data['source'] ?? 'featured';
        $limit = (int) ($data['limit'] ?? 8);
        $link = '/products';

        $query = Product::active();

        match ($source) {
            'featured' => $query->where('is_featured', true)->orderBy('sort_order'),
            'latest' => $query->latest('id'),
            'sale' => $query->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price')->latest('id'),
            'popular' => $query->orderByDesc('views'),
            'category' => $query->whereHas('categories', fn ($q) => $q->where('categories.id', $data['category_id'] ?? 0))->orderBy('sort_order'),
            'manual' => $query->whereIn('id', $data['product_ids'] ?? [0]),
            default => null,
        };

        $products = $query->limit($limit)->get();

        if ($source === 'manual') {
            $ids = $data['product_ids'] ?? [];
            $products = $products->sortBy(fn ($p) => array_search($p->id, $ids))->values();
        }

        if ($source === 'category' && ($category = Category::find($data['category_id'] ?? 0))) {
            $link = '/categories/'.$category->localizedSlug();
        } elseif (in_array($source, ['latest', 'sale', 'popular'])) {
            $link = '/products?sort='.['latest' => 'newest', 'sale' => 'default', 'popular' => 'popular'][$source]
                .($source === 'sale' ? '&sale=1' : '');
        }

        return [
            'style' => $data['style'] ?? 'carousel',
            'link' => ($data['show_more_link'] ?? true) ? $link : null,
            'items' => $products->map(fn ($p) => Present::productCard($p))->values(),
        ];
    }
}
