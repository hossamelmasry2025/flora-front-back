<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** لغة الـ API من ?lang=en (الافتراضي عربي) */
class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $lang = $request->query('lang', $request->header('X-Locale', 'ar'));
        app()->setLocale(in_array($lang, ['ar', 'en'], true) ? $lang : 'ar');

        return $next($request);
    }
}
