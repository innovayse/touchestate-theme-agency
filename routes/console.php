<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Refresh CBA exchange rates every 12h (the local store is kept/used between runs).
Schedule::command('currency:refresh')->twiceDaily(3, 15);
