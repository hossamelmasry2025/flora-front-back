<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\RevalidatesFrontend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasSlug, HasTranslations, RevalidatesFrontend;

    protected $fillable = [
        'name', 'name_en', 'slug', 'slug_en', 'sku', 'short_description', 'short_description_en', 'description', 'description_en', 'price', 'sale_price',
        'image', 'gallery', 'details', 'in_stock', 'is_active', 'is_featured',
        'meta_title', 'meta_title_en', 'meta_description', 'meta_description_en', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'gallery' => 'array',
            'details' => 'array',
            'in_stock' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** السعر الفعلي اللي العميل بيدفعه */
    public function finalPrice(): float
    {
        return (float) ($this->hasDiscount() ? $this->sale_price : $this->price);
    }

    public function hasDiscount(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price > 0 && (float) $this->sale_price < (float) $this->price;
    }
}
