<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;
use App\Support\Frontend;
use App\Support\HomeSections;
use App\Support\Locale;

class SiteController extends Controller
{
    /** إعدادات الموقع العامة: الاسم واللوجو والتواصل والـ SEO والدفع (باللغة المطلوبة). */
    public function settings()
    {
        $s = Setting::allCached();
        $en = Locale::isEn();

        return ['data' => [
            'site_name' => Locale::pick($s, 'site_name', $en ? 'Flora' : 'فلورا'),
            'tagline' => Locale::pick($s, 'tagline'),
            'logo' => Frontend::media($s['logo'] ?? null),
            'favicon' => Frontend::media($s['favicon'] ?? null),
            'og_image' => Frontend::media($s['og_image'] ?? null),
            'meta_title' => Locale::pick($s, 'meta_title'),
            'meta_description' => Locale::pick($s, 'meta_description'),
            'meta_keywords' => Locale::pick($s, 'meta_keywords'),
            'google_verification' => $s['google_verification'] ?? null,
            'google_analytics_id' => $s['google_analytics_id'] ?? null,
            'facebook_pixel_id' => $s['facebook_pixel_id'] ?? null,
            'phone' => $s['phone'] ?? null,
            'whatsapp' => $s['whatsapp'] ?? null,
            'email' => $s['email'] ?? null,
            'address' => Locale::pick($s, 'address'),
            'city' => Locale::pick($s, 'city'),
            'working_hours' => Locale::pick($s, 'working_hours'),
            'social' => collect($s['social'] ?? [])->filter(fn ($l) => filled($l['url'] ?? null))->values(),
            'announcement' => Locale::pick($s, 'announcement'),
            'footer_about' => Locale::pick($s, 'footer_about'),
            'currency' => $en ? ($s['currency_en'] ?? 'EGP') : ($s['currency'] ?? 'ج.م'),
            'payment' => [
                'cod_enabled' => (bool) ($s['cod_enabled'] ?? true),
                'transfer_enabled' => (bool) ($s['transfer_enabled'] ?? true),
                'transfer_instructions' => Locale::pick($s, 'transfer_instructions'),
                'transfer_accounts' => collect($s['transfer_accounts'] ?? [])
                    ->filter(fn ($a) => filled($a['number'] ?? null))
                    ->map(fn ($a) => ['name' => Locale::pick($a, 'name'), 'number' => $a['number'], 'holder' => $a['holder'] ?? null])
                    ->values(),
            ],
            'checkout' => [
                // الصيغة القديمة كانت نصوص، والجديدة {label, label_en}
                'time_slots' => collect($s['time_slots'] ?? [])
                    ->map(fn ($slot) => is_array($slot) ? Locale::pick($slot, 'label') : $slot)
                    ->filter()->values(),
                'min_order' => (float) ($s['min_order'] ?? 0),
                'card_max_length' => (int) ($s['card_max_length'] ?? 300),
                'same_day_cutoff' => $s['same_day_cutoff'] ?? null,
            ],
            'theme' => [
                'brand' => $s['brand_color'] ?? '#b02f59',
                'accent' => $s['accent_color'] ?? '#b8935a',
                'background' => $s['background_color'] ?? '#fbf7f3',
                'corners' => $s['corner_style'] ?? 'rounded',
            ],
            'header' => [
                'menu' => collect($s['header_menu'] ?? [])->filter(fn ($i) => filled($i['link'] ?? null))
                    ->map(fn ($i) => ['label' => Locale::pick($i, 'label'), 'link' => $i['link'], 'highlight' => (bool) ($i['highlight'] ?? false)])->values(),
                'show_search' => (bool) ($s['header_show_search'] ?? true),
                'show_track' => (bool) ($s['header_show_track'] ?? true),
                'show_language' => (bool) ($s['header_show_language'] ?? true),
                'sticky' => (bool) ($s['header_sticky'] ?? true),
            ],
            'whatsapp_button' => (bool) ($s['whatsapp_button'] ?? true),
            'footer' => [
                'links' => collect($s['footer_links'] ?? [])->filter(fn ($i) => filled($i['link'] ?? null))
                    ->map(fn ($i) => ['label' => Locale::pick($i, 'label'), 'link' => $i['link']])->values(),
                'show_categories' => (bool) ($s['footer_show_categories'] ?? true),
                'show_pages' => (bool) ($s['footer_show_pages'] ?? true),
            ],
            'footer_pages' => Page::where('is_active', true)->where('show_in_footer', true)
                ->orderBy('sort_order')->get()
                ->map(fn ($p) => ['title' => $p->tr('title'), 'slug' => $p->localizedSlug()]),
            'menu_categories' => Category::active()->whereNull('parent_id')->orderBy('sort_order')
                ->limit(8)->get()
                ->map(fn ($c) => ['name' => $c->tr('name'), 'slug' => $c->localizedSlug()]),
        ]];
    }

    public function home()
    {
        return ['data' => HomeSections::home()];
    }

    public function page(string $slug)
    {
        $page = Page::where('is_active', true)->whereSlug($slug)->firstOrFail();

        return ['data' => [
            'title' => $page->tr('title'),
            'subtitle' => $page->tr('subtitle'),
            'image' => Frontend::media($page->image),
            'slug' => $page->localizedSlug(),
            'slugs' => $page->slugs(),
            'content' => $page->tr('content'),
            'sections' => [],
            'meta_title' => $page->tr('meta_title'),
            'meta_description' => $page->tr('meta_description'),
            'updated_at' => $page->updated_at?->toIso8601String(),
        ]];
    }

    /** سكاشن ثابتة لصفحات المتجر (من صفحة "بانرات صفحات المتجر") */
    public function sections(string $key)
    {
        $placements = ['products_page' => 'products_page', 'cart_page' => 'cart', 'all_pages_top' => 'all_pages_top'];
        abort_unless(isset($placements[$key]), 404);

        return ['data' => HomeSections::banners($placements[$key])];
    }

    public function deliveryZones()
    {
        return ['data' => DeliveryZone::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get()
            ->map(fn ($z) => ['id' => $z->id, 'name' => $z->tr('name'), 'fee' => (float) $z->fee])];
    }

    /** كل الروابط (بالعربي والإنجليزي) لملف sitemap.xml */
    public function sitemap()
    {
        $row = fn ($m) => ['slugs' => $m->slugs(), 'updated_at' => $m->updated_at];

        return ['data' => [
            'categories' => Category::active()->get()->map($row),
            'products' => Product::active()->get()->map(fn ($p) => $row($p) + ['image' => Frontend::media($p->image)]),
            'pages' => Page::where('is_active', true)->get()->map($row),
        ]];
    }
}
