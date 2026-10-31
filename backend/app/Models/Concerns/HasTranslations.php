<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * المحتوى الإنجليزي متخزن في أعمدة بنهاية _en.
 * لو النسخة الإنجليزي فاضية بيرجع العربي.
 */
trait HasTranslations
{
    public function tr(string $field): mixed
    {
        if (app()->getLocale() === 'en' && filled($this->{$field.'_en'} ?? null)) {
            return $this->{$field.'_en'};
        }

        return $this->{$field};
    }

    /** الرابط باللغة الحالية */
    public function localizedSlug(): string
    {
        return app()->getLocale() === 'en' && filled($this->slug_en) ? $this->slug_en : $this->slug;
    }

    /** @return array{ar: string, en: string} */
    public function slugs(): array
    {
        return ['ar' => $this->slug, 'en' => $this->slug_en ?: $this->slug];
    }

    /** البحث بالرابط العربي أو الإنجليزي */
    public function scopeWhereSlug(Builder $query, string $slug): void
    {
        $query->where(fn (Builder $q) => $q->where('slug', $slug)->orWhere('slug_en', $slug));
    }
}
