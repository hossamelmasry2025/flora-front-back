<?php

namespace App\Models\Concerns;

use App\Support\Slug;
use Illuminate\Support\Str;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            $field = $model->slugSource ?? 'name';

            $model->slug = Slug::unique(
                filled($model->slug) ? $model->slug : $model->{$field},
                static::class,
                $model->getKey(),
            );

            // رابط إنجليزي للنسخة الإنجليزي من الموقع
            $source = filled($model->slug_en) ? $model->slug_en : $model->{$field.'_en'};
            $model->slug_en = filled($source) ? Slug::unique(Str::slug($source) ?: $source, static::class, $model->getKey(), 'slug_en') : null;
        });
    }
}
