<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * بيستورد المنتجات والتصنيفات من أي متجر WooCommerce عن طريق الـ Store API العام.
 * التشغيل أكتر من مرة بيحدّث نفس المنتجات (المفتاح هو SKU = PREFIX-رقم المنتج هناك).
 */
class ImportWooCommerce extends Command
{
    protected $signature = 'store:import-woocommerce
        {url : رابط المتجر، مثلاً https://example.com}
        {--prefix=WC : بادئة الـ SKU للمنتجات المستوردة}
        {--translations= : ملف PHP فيه ترجمة المنتجات (رقم المنتج هناك ← [اسم عربي, اسم إنجليزي, وصف عربي, وصف إنجليزي])}';

    protected $description = 'استيراد المنتجات والتصنيفات من متجر WooCommerce';

    /** تصنيفات الموقع التاني (بالـ slug) ← اسم التصنيف عندنا بالعربي والإنجليزي والوصف الإنجليزي */
    private const CATEGORY_MAP = [
        'khotoba' => ['ورد خطوبة', 'Engagement Flowers', 'Flower bouquets and gift ideas for engagements, perfect for special occasions and weddings.'],
        'graduation' => ['ورد تخرج', 'Graduation Flowers', null],
        'eid-al-om' => ['ورد عيد الأم', "Mother's Day Flowers", "Elegant bouquets and gifts to show love and gratitude to our mothers on Mother's Day."],
        'eid-alhob' => ['ورد عيد الحب', "Valentine's Day Flowers", "Romantic bouquets and special gifts to celebrate Valentine's Day, featuring red roses and romantic flowers."],
    ];

    /** @var array<int, array<int, string>> */
    private array $translations = [];

    public function handle(): int
    {
        $api = rtrim($this->argument('url'), '/').'/wp-json/wc/store/v1';
        $prefix = $this->option('prefix');
        $this->translations = $this->option('translations') ? require base_path($this->option('translations')) : [];

        $categories = $this->importCategories($this->fetchAll("$api/products/categories"));
        $products = $this->fetchAll("$api/products");

        $bar = $this->output->createProgressBar(count($products));

        foreach ($products as $i => $item) {
            $this->importProduct($item, $categories, $prefix, $i);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info(count($products).' منتج و '.count($categories).' تصنيف اتستوردوا.');

        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function fetchAll(string $url): array
    {
        $items = [];
        $page = 1;

        do {
            $response = Http::timeout(60)->retry(3, 1000)->get($url, ['per_page' => 100, 'page' => $page])->throw();
            $items = [...$items, ...$response->json()];
            $pages = (int) $response->header('X-WP-TotalPages') ?: 1;
        } while ($page++ < $pages);

        return $items;
    }

    /** @return array<int, int> رقم التصنيف هناك ← رقمه عندنا */
    private function importCategories(array $remote): array
    {
        $map = [];

        foreach ($remote as $item) {
            if (! isset(self::CATEGORY_MAP[$item['slug']])) {
                continue;
            }

            [$name, $nameEn, $descriptionEn] = self::CATEGORY_MAP[$item['slug']];
            $category = Category::firstOrNew(['name' => $name]);

            $category->fill([
                'name_en' => $category->name_en ?: $nameEn,
                'description' => $category->description ?: $this->text($item['description'] ?? ''),
                'description_en' => $category->description_en ?: $descriptionEn,
                'image' => $category->image ?: $this->download($item['image']['src'] ?? null, 'categories'),
                'is_active' => true,
            ])->save();

            $map[$item['id']] = $category->id;
        }

        return $map;
    }

    private function importProduct(array $item, array $categories, string $prefix, int $index): void
    {
        $original = $this->text($item['name']);
        $isArabic = (bool) preg_match('/\p{Arabic}/u', $original);
        $t = $this->translations[$item['id']] ?? [];

        $name = $t[0] ?? $original;
        $nameEn = $t[1] ?? ($isArabic ? null : $original);
        $images = collect($item['images'])->map(fn ($img) => $this->download($img['src'], 'products'))->filter()->values();

        $price = $this->money($item['prices']['regular_price'], $item['prices']['currency_minor_unit']);
        $sale = $this->money($item['prices']['sale_price'], $item['prices']['currency_minor_unit']);

        $product = Product::firstOrNew(['sku' => "$prefix-{$item['id']}"]);

        $product->fill([
            // لو الاسم اتغير (مثلاً اتترجم) الرابط يتعمل من جديد من الاسم الجديد
            'slug' => $product->name === $name ? $product->slug : null,
            'slug_en' => $product->name_en === $nameEn ? $product->slug_en : null,
            'name' => $name,
            'name_en' => $nameEn,
            'short_description' => $t[2] ?? ($this->text($item['short_description']) ?: null),
            'short_description_en' => $t[3] ?? null,
            // الوصف هناك سطر واحد بالإنجليزي، فلو الترجمة فيها وصف بيغني عنه
            'description' => isset($t[2]) ? null : (trim($item['description']) ?: null),
            'price' => $price,
            'sale_price' => $sale < $price ? $sale : null,
            'image' => $images->first(),
            'gallery' => $images->slice(1)->values()->all(),
            'in_stock' => (bool) $item['is_in_stock'],
            'is_active' => true,
            'sort_order' => $product->exists ? $product->sort_order : 100 + $index,
        ])->save();

        $product->categories()->syncWithoutDetaching(
            collect($item['categories'])->map(fn ($c) => $categories[$c['id']] ?? null)->filter()->all()
        );
    }

    /** بينزل الصورة على الـ public disk مرة واحدة بس، ويرجع المسار */
    private function download(?string $url, string $folder): ?string
    {
        if (blank($url)) {
            return null;
        }

        $path = "$folder/imported/".basename(parse_url($url, PHP_URL_PATH));

        if (! Storage::disk('public')->exists($path)) {
            $response = Http::timeout(60)->retry(3, 1000)->get($url);

            if (! $response->successful()) {
                $this->warn("تعذر تحميل الصورة: $url");

                return null;
            }

            Storage::disk('public')->put($path, $response->body());
        }

        return $path;
    }

    private function money(string $amount, int $minorUnit): float
    {
        return (int) $amount / (10 ** $minorUnit);
    }

    private function text(string $html): string
    {
        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
