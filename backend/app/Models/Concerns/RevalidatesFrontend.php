<?php

namespace App\Models\Concerns;

use App\Support\Frontend;

/**
 * أي تعديل من الداش بورد بيبلغ Next.js يحدّث الصفحات فوراً بدل ما يستنى الكاش.
 */
trait RevalidatesFrontend
{
    protected static function bootRevalidatesFrontend(): void
    {
        static::saved(fn () => Frontend::revalidate());
        static::deleted(fn () => Frontend::revalidate());
    }
}
