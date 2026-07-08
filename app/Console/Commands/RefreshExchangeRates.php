<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class RefreshExchangeRates extends Command
{
    protected $signature = 'currency:refresh';

    protected $description = 'Fetch the latest CBA exchange rates into the local store (kept for 12h).';

    public function handle(ExchangeRateService $rates): int
    {
        if ($rates->refresh()) {
            $this->info('Exchange rates refreshed from CBA.');
            return self::SUCCESS;
        }

        $this->warn('CBA fetch failed — previous stored rates kept.');
        return self::FAILURE;
    }
}
