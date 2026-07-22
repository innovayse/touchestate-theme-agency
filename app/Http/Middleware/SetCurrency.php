<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetCurrency
{
    public function handle(Request $request, Closure $next)
    {
        // Ensure the session always holds a valid display currency (defaults to config).
        $supported = config('currency.supported', []);
        $currency  = session('currency');

        if (!in_array($currency, $supported, true)) {
            session(['currency' => config('currency.default')]);
        }

        return $next($request);
    }
}
