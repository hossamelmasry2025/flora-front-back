<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Present;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function categories()
    {
        $categories = Category::active()
            ->whereNull('parent_id')
            ->withCount(['products' => fn ($q) => $q->active()])
            ->with(['children' => fn ($q) => $q->active()])
            ->orderBy('sort_order')
            ->get();

        return [
            'data' => $categories->map(fn ($c) => Present::categoryCard($c) + [
                'children' => $c->children->map(fn ($child) => Present::categoryCard($child))->values(),
            ]),
        ];
    }

    public function category(string $slug)
    {
        $category = Category::active()
            ->whereSlug($slug)
            ->with(['parent', 'children'])
            ->firstOrFail();

        return ['data' => Present::category($category)];
    }

    /**
     * قائمة المنتجات: بحث + فلترة بالتصنيف والسعر + ترتيب + باجنيشن.
     */
    public function products(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:200'],
            'min' => ['nullable', 'numeric', 'min:0'],
            'max' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'in:default,newest,price_asc,price_desc,popular'],
            'featured' => ['nullable', 'boolean'],
            'sale' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:48'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $priceSql = 'CASE WHEN sale_price IS NOT NULL AND sale_price > 0 AND sale_price < price THEN sale_price ELSE price END';

        $query = Product::active()
            ->when($data['q'] ?? null, function (Builder $q, string $term) {
                $term = trim($term);
                $q->where(fn (Builder $w) => $w
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('name_en', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhere('sku', $term)
                    ->orWhereHas('categories', fn ($c) => $c->where('name', 'like', "%{$term}%")->orWhere('name_en', 'like', "%{$term}%")));
            })
            ->when($data['category'] ?? null, function (Builder $q, string $slug) {
                $category = Category::active()->whereSlug($slug)->with('children')->first();
                $ids = $category ? $category->children->pluck('id')->push($category->id) : collect([0]);
                $q->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $ids));
            })
            ->when(isset($data['min']), fn ($q) => $q->whereRaw("{$priceSql} >= ?", [$data['min']]))
            ->when(isset($data['max']), fn ($q) => $q->whereRaw("{$priceSql} <= ?", [$data['max']]))
            ->when($data['featured'] ?? false, fn ($q) => $q->where('is_featured', true))
            ->when($data['sale'] ?? false, fn ($q) => $q->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price'));

        match ($data['sort'] ?? 'default') {
            'newest' => $query->latest('id'),
            'price_asc' => $query->orderByRaw("{$priceSql} asc"),
            'price_desc' => $query->orderByRaw("{$priceSql} desc"),
            'popular' => $query->orderByDesc('views'),
            default => $query->orderByDesc('in_stock')->orderBy('sort_order')->latest('id'),
        };

        $paginator = $query->paginate($data['per_page'] ?? 12)->withQueryString();

        return [
            'data' => collect($paginator->items())->map(fn ($p) => Present::productCard($p)),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    public function product(string $slug)
    {
        $product = Product::active()->whereSlug($slug)->with('categories')->firstOrFail();

        $related = Product::active()
            ->whereKeyNot($product->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $product->categories->pluck('id')))
            ->inRandomOrder()
            ->limit(8)
            ->get();

        return [
            'data' => Present::product($product),
            'related' => $related->map(fn ($p) => Present::productCard($p)),
            'sections' => \App\Support\HomeSections::banners('product_page'),
        ];
    }

    /** بيتسجل من صفحة المنتج (من غير ما يأثر على الكاش) عشان ترتيب "الأكثر طلباً". */
    public function view(string $slug)
    {
        Product::whereSlug($slug)->increment('views');

        return response()->noContent();
    }
}
