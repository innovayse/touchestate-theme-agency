<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Home Hero Stats
    |--------------------------------------------------------------------------
    |
    | Marketing figures shown in the home page hero. There is no admin panel,
    | so edit them here or override via .env. They are intentionally NOT pulled
    | from the API (which only counts what has been entered so far).
    |
    */

    'stats' => [
        'deals'  => env('SITE_STATS_DEALS', 1000),   // successful deals (shown as "1000+")
        'active' => env('SITE_STATS_ACTIVE', 200),   // active listings  (shown as "200+")
    ],

];
