<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Frontend
{
    private static bool $queued = false;

    /** يطلب من Next.js إعادة بناء الصفحات المخزنة (مرة واحدة في نهاية الطلب). */
    public static function revalidate(): void
    {
        if (self::$queued || app()->runningUnitTests() || ! config('services.frontend.revalidate_secret')) {
            return;
        }

        self::$queued = true;

        app()->terminating(function () {
            try {
                Http::timeout(3)->post(rtrim(config('services.frontend.url'), '/').'/api/revalidate', [
                    'secret' => config('services.frontend.revalidate_secret'),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Frontend revalidate failed: '.$e->getMessage());
            } finally {
                self::$queued = false;
            }
        });
    }

    public static function url(string $path = ''): string
    {
        return rtrim(config('services.frontend.url'), '/').'/'.ltrim($path, '/');
    }

    public static function media(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
