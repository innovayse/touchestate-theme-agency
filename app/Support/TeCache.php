<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\App;

/**
 * Central cache-key builder for TouchEstate content that is localized by the
 * X-Language header (CRM-441). A property's Title/Description differ per language,
 * so cached copies must be namespaced by locale — otherwise the first-visited
 * language would be served to everyone.
 *
 * Language-independent caches (workspace, favicon, comments) intentionally do NOT
 * use this and stay shared across locales.
 */
final class TeCache
{
    /** Resolve the effective locale (defaults to the active app locale). */
    public static function locale(?string $locale = null): string
    {
        return $locale ?? App::getLocale();
    }

    /** Cache key for a single property's localized detail payload. */
    public static function prop(string $slug, ?string $locale = null): string
    {
        return 'te_prop:' . $slug . ':' . self::locale($locale);
    }

    /** Suffix an arbitrary content cache key with the active locale. */
    public static function key(string $base, ?string $locale = null): string
    {
        return $base . ':' . self::locale($locale);
    }
}
