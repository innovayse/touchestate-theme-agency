<?php

declare(strict_types=1);

namespace App\Providers;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\RequestInterface;
use TouchEstate\Sdk\HttpClient\GuzzleClient;
use TouchEstate\Sdk\TouchEstateClient;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Override the SDK's default client binding so every API request carries the
        // active UI language via the X-Language header (CRM-441). The header selects
        // the content language: the catalogue is filtered/localized to it, so listing
        // and detail Title/Description come back translated. Applied globally here to
        // avoid threading RequestOptions::language() through every call site.
        $this->app->singleton(TouchEstateClient::class, function ($app) {
            $config = $app['config']['touchestate'];

            $stack = HandlerStack::create();
            $stack->push(Middleware::mapRequest(
                fn (RequestInterface $request) => $request->withHeader('X-Language', App::getLocale())
            ));

            return new TouchEstateClient([
                'public_key'      => $config['public_key'],
                'secret_key'      => $config['secret_key'],
                'base_url'        => $config['base_url'],
                'timeout'         => $config['timeout'] ?? 30,
                'connect_timeout' => $config['connect_timeout'] ?? 10,
                'http_client'     => new GuzzleClient([
                    'verify'          => $config['verify_ssl'] ?? true,
                    'timeout'         => $config['timeout'] ?? 30,
                    'connect_timeout' => $config['connect_timeout'] ?? 10,
                    'handler'         => $stack,
                ]),
            ]);
        });
    }

    public function boot(): void
    {
        // Share workspace data with ALL views (cached 1 hour)
        View::composer('*', function ($view) {
            static $workspace = null;
            static $faviconUrl = null;
            if ($workspace === null) {
                $workspace = Cache::remember('te_workspace', 3600, function () {
                    try {
                        return app(TouchEstateClient::class)->workspace()->retrieve();
                    } catch (\Throwable $e) {
                        return [];
                    }
                });
            }
            // Favicon = the workspace logo from the API, but only if the URL actually
            // resolves to an image (the stored logoUrl can 404 on the CDN). Otherwise the
            // view falls back to the bundled icon. Validated once per hour.
            if ($faviconUrl === null) {
                $faviconUrl = Cache::remember('te_favicon_url', 3600, function () use ($workspace) {
                    $logo = $workspace['logoUrl'] ?? '';
                    if (! $logo) {
                        return '';
                    }
                    try {
                        $resp = Http::withoutVerifying()->timeout(4)->head($logo);
                        $type = (string) $resp->header('Content-Type');
                        if ($resp->successful() && str_starts_with($type, 'image/')) {
                            return $logo;
                        }
                    } catch (\Throwable $e) {
                        // network error → fall back
                    }

                    return '';
                });
            }
            $view->with('workspace', $workspace);
            $view->with('faviconUrl', $faviconUrl);
        });
    }
}
