<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\RevalidatesFrontend;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasSlug, HasTranslations, RevalidatesFrontend;

    protected string $slugSource = 'title';

    protected $fillable = [
        'title', 'title_en', 'slug', 'slug_en', 'subtitle', 'subtitle_en', 'image',
        'content', 'content_en', 'sections', 'meta_title', 'meta_title_en', 'meta_description', 'meta_description_en',
        'is_active', 'show_in_footer', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_in_footer' => 'boolean',
            'sections' => 'array',
        ];
    }
}
