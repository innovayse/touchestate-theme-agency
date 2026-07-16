<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SuggestController;
use App\Http\Controllers\CentralDistrictController;
use App\Http\Controllers\NearbyController;

// ─────────────────────────────────────────────
// Geocoding proxy routes (server-side to avoid CORS)
// ─────────────────────────────────────────────

Route::get('/api/suggest', SuggestController::class);
Route::get('/api/central-district', CentralDistrictController::class);
Route::get('/api/nearby', NearbyController::class);


// ─────────────────────────────────────────────
// Contacts API
// ─────────────────────────────────────────────
Route::get('/api/contacts', [ContactController::class, 'index']);
Route::get('/api/contacts/{id}', [ContactController::class, 'show']);
Route::post('/api/contact', [ContactController::class, 'inquiry']);


// ─────────────────────────────────────────────
// Default routes (no locale prefix) → Armenian
// ─────────────────────────────────────────────

// Home
Route::get('/', [HomeController::class, 'index']);
Route::get('/home/data', [HomeController::class, 'data']); // skeleton-first: async listings data

// Property listing + single (API-driven)
Route::get('/property', [PropertyController::class, 'index']);
Route::get('/property/results', [PropertyController::class, 'results']); // skeleton-first: async results
Route::get('/property/{slug}', [PropertyController::class, 'show']);
Route::get('/property/{slug}/extras', [PropertyController::class, 'extras']); // skeleton-first: similar + comments
Route::get('/property/{slug}/content', [PropertyController::class, 'content']); // skeleton-first: async main content
Route::post('/api/property/{slug}/view',    [PropertyController::class, 'recordView']);
Route::post('/api/property/{slug}/enquire', [PropertyController::class, 'enquire']);

// Map
Route::get('/map',      [PropertyController::class, 'map']);
Route::get('/map/data', [PropertyController::class, 'mapData']);

// All simple static pages (default to Armenian locale)
$defaultRoutes = [
    'contact-us', // 'about-us' temporarily disabled (page kept); 'our-team' removed
    'faq', 'privacy-policy', 'terms-condition', /* 'testimonial', */
    'cart', 'checkout',
    'maintenance', 'error-404', 'error-500',
];

foreach ($defaultRoutes as $route) {
    Route::get('/' . $route, fn () => view($route));
}


// ─────────────────────────────────────────────
// Localized routes: /en/* /ru/* /hy/*
// ─────────────────────────────────────────────
Route::group(
    ['prefix' => '{locale}', 'where' => ['locale' => 'en|ru|hy'], 'middleware' => 'setlocale'],
    function () {

        // Home (API-driven)
        Route::get('/', [HomeController::class, 'index'])->name('index');
        Route::get('/home/data', [HomeController::class, 'data'])->name('home.data'); // skeleton-first: async listings data

        // Property listing + single (API-driven)
        Route::get('/property',         [PropertyController::class, 'index'])->name('property');
        Route::get('/property/results', [PropertyController::class, 'results'])->name('property.results'); // skeleton-first: async results
        Route::get('/property/{slug}',  [PropertyController::class, 'show'])->name('property.single');
        Route::get('/property/{slug}/extras',  [PropertyController::class, 'extras'])->name('property.extras'); // skeleton-first: similar + comments
        Route::get('/property/{slug}/content', [PropertyController::class, 'content'])->name('property.content'); // skeleton-first: async main content

        // Map
        Route::get('/map',        [PropertyController::class, 'map'])->name('map');
        Route::get('/map/data',   [PropertyController::class, 'mapData'])->name('map.data');
        Route::get('/map/coords', [PropertyController::class, 'mapCoords'])->name('map.coords');

        // Favorites
        Route::get('/favorites', [FavoritesController::class, 'index'])->name('favorites');
        Route::post('/favorites/load', [FavoritesController::class, 'load'])->name('favorites.load');

        // Compare
        Route::get('/compare', [CompareController::class, 'index'])->name('compare');
        Route::post('/compare/load', [CompareController::class, 'load'])->name('compare.load');

        // Cache invalidation
        Route::post('/property/uncache', [App\Http\Controllers\PropertyCacheController::class, 'forget'])->name('property.uncache');

        // Static pages
        // Route::get('/about-us',        fn () => view('about-us'))->name('about-us'); // temporarily disabled (page kept)
        Route::get('/contact-us',      fn () => view('contact-us'))->name('contact-us');
        Route::get('/faq',             fn () => view('faq'))->name('faq');
        Route::get('/privacy-policy',  fn () => view('privacy-policy'))->name('privacy-policy');
        Route::get('/terms-condition', fn () => view('terms-condition'))->name('terms-condition');
        // Route::get('/testimonial',     fn () => view('testimonial'))->name('testimonial');
        Route::get('/cart',            fn () => view('cart'))->name('cart');
        Route::get('/checkout',        fn () => view('checkout'))->name('checkout');

        // Error / utility pages
        Route::get('/maintenance', fn () => view('maintenance'))->name('maintenance');
        Route::get('/error-404',   fn () => view('error-404'))->name('error-404');
        Route::get('/error-500',   fn () => view('error-500'))->name('error-500');
    }
);
