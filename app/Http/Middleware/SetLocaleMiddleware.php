<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rawLocale = $request->header('X-Locale')
            ?? $request->query('locale')
            ?? $request->query('lang')
            ?? $request->header('Accept-Language')
            ?? config('app.fallback_locale', 'en');

        // FE gửi dạng 'vi', 'vi-VN', 'zh-CN' (được dịch sang Tiếng Việt trong Vben) hoặc 'en', 'en-US'
        $locale = (str_starts_with(strtolower(trim($rawLocale)), 'vi') || str_contains(strtolower($rawLocale), 'vi') || str_contains(strtolower($rawLocale), 'zh'))
            ? 'vi'
            : 'en';

        App::setLocale($locale);
        $request->setLocale($locale);

        return $next($request);
    }
}
