<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // Get locale from URL parameter
        $locale = $request->route('locale');

        // If no locale in URL, get from session or use default
        if (!$locale) {
            $locale = session('locale', config('app.locale'));
        }

        // Validate and set locale
        if (in_array($locale, ['en', 'ru', 'hy'])) {
            App::setLocale($locale);
            session(['locale' => $locale]);
        }

        // Remove locale from route parameters so controllers only receive their own params
        $request->route()?->forgetParameter('locale');

        return $next($request);
    }
}
