<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\CbaRatesService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use TouchEstate\Sdk\TouchEstateClient;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Share workspace data with ALL views (cached 1 hour)
        View::composer('*', function ($view) {
            static $workspace = null;
            if ($workspace === null) {
                $workspace = Cache::remember('te_workspace', 3600, function () {
                    try {
                        return app(TouchEstateClient::class)->workspace()->retrieve();
                    } catch (\Throwable $e) {
                        return [];
                    }
                });
            }
            $view->with('workspace', $workspace);
        });

        View::share('cbaRates', app(CbaRatesService::class)->getRates());
    }
}
