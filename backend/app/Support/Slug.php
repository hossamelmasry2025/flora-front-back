<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class Slug
{
    /**
     * Str::slug() يحذف الحروف العربية، فهنا بنحتفظ بالعربي والإنجليزي والأرقام
     * (جوجل بيتعامل مع روابط عربية عادي، وهي أفضل للبحث بالعربي).
     */
    public static function make(string $text): string
    {
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0640}]/u', '', $text); // تشكيل وتطويل
        $text = preg_replace('/[^\p{Arabic}\p{L}\p{N}]+/u', '-', $text);

        return mb_strtolower(trim($text, '-'));
    }

    public static function unique(string $text, string $modelClass, ?int $ignoreId = null, string $column = 'slug'): string
    {
        $base = self::make($text) ?: 'item';
        $slug = $base;
        $i = 2;

        while ($modelClass::query()
            ->where($column, $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
