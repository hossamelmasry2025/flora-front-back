<?php

namespace App\Support;

class Locale
{
    public static function isEn(): bool
    {
        return app()->getLocale() === 'en';
    }

    /** يختار القيمة الإنجليزي من مصفوفة (إعدادات / سكاشن) لو موجودة */
    public static function pick(array $data, string $key, mixed $default = null): mixed
    {
        if (self::isEn() && filled($data[$key.'_en'] ?? null)) {
            return $data[$key.'_en'];
        }

        return $data[$key] ?? $default;
    }
}
