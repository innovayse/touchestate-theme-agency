<?php

namespace App\Console\Commands;

use App\Services\CbaRatesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FetchRates extends Command
{
    protected $signature   = 'rates:fetch';
    protected $description = 'Fetch latest exchange rates from CBA Armenia and refresh cache';

    public function handle(CbaRatesService $service): int
    {
        Cache::forget('cba_rates');
        $rates = $service->getRates();

        $this->info('Rates updated:');
        foreach (['USD', 'EUR', 'RUB', 'AMD'] as $cur) {
            if (isset($rates[$cur])) {
                $this->line("  {$cur}: {$rates[$cur]}");
            }
        }

        return 0;
    }
}
