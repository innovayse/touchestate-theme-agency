<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Provides exchange rates (quoted against AMD: 1 unit of a foreign currency = Rate/Amount
 * AMD, so rates['AMD'] === 1.0) and converts amounts between currencies.
 *
 * Rates come from the Central Bank of Armenia (CBA) SOAP API and are persisted to a local
 * JSON store (storage/app/private/exchange-rates.json). The store is the source of truth
 * for 12h; after that a fresh fetch is attempted (proactively by the scheduled
 * `currency:refresh` command, or lazily on request). If CBA is unreachable, the last
 * stored rates are kept; if nothing was ever stored, approximate config fallbacks are used.
 * This keeps prices/search working regardless of CBA availability, and never blocks a
 * request on repeated timeouts (a short cooldown guards lazy refetches).
 */
class ExchangeRateService
{
    private const STORE = 'exchange-rates.json';
    private const COOLDOWN_KEY = 'cba_fetch_cooldown';

    /** Per-request memo: a single request resolves rates at most once. */
    private static ?array $memo = null;

    /**
     * ISO code → AMD value per 1 unit.
     *
     * @return array<string, float>
     */
    public function rates(): array
    {
        return self::$memo ??= $this->resolveRates();
    }

    private function resolveRates(): array
    {
        $store = $this->readStore();
        $ttl   = (int) config('currency.cba.cache_ttl', 43200);

        // Fresh stored rates → use as-is.
        if ($store !== null && (time() - $store['fetched_at']) < $ttl) {
            return $store['rates'];
        }

        // Stale/missing → try to refresh, but only if we're not in the post-failure cooldown
        // (so an unreachable CBA can't make every request pay the HTTP timeout).
        if (!Cache::has(self::COOLDOWN_KEY)) {
            $fetched = $this->fetch();
            if (!empty($fetched)) {
                return $this->store($fetched);
            }
            // Failed → back off before retrying.
            Cache::put(self::COOLDOWN_KEY, 1, (int) config('currency.cba.retry_after', 300));
        }

        // Keep last known rates if we have them; otherwise approximate config fallbacks.
        return $store['rates'] ?? $this->fallbackRates();
    }

    /**
     * Force a fresh CBA fetch and persist it. Returns true on success (used by the
     * scheduled `currency:refresh` command). On failure the existing store is untouched.
     */
    public function refresh(): bool
    {
        $fetched = $this->fetch();
        if (empty($fetched)) {
            return false;
        }
        $this->store($fetched);
        self::$memo = null; // let the next rates() call pick up the fresh values
        Cache::forget(self::COOLDOWN_KEY);
        return true;
    }

    /** @return array<string, float> */
    private function store(array $rates): array
    {
        $rates['AMD'] = 1.0;
        Storage::disk('local')->put(self::STORE, json_encode([
            'rates'      => $rates,
            'fetched_at' => time(),
        ]));
        return $rates;
    }

    /** @return array{rates: array<string,float>, fetched_at: int}|null */
    private function readStore(): ?array
    {
        try {
            if (!Storage::disk('local')->exists(self::STORE)) {
                return null;
            }
            $data = json_decode(Storage::disk('local')->get(self::STORE), true);
        } catch (\Throwable $e) {
            return null;
        }

        if (!is_array($data) || empty($data['rates']) || !isset($data['fetched_at'])) {
            return null;
        }

        return ['rates' => $data['rates'], 'fetched_at' => (int) $data['fetched_at']];
    }

    /** @return array<string, float> */
    private function fallbackRates(): array
    {
        $rates = ['AMD' => 1.0];
        foreach ((array) config('currency.fallback', []) as $iso => $rate) {
            $rate = (float) $rate;
            if ($rate > 0) {
                $rates[$iso] = $rate;
            }
        }
        return $rates;
    }

    /**
     * Convert $amount from one currency to another. Returns null when a needed
     * rate is unavailable (caller should then show the original amount as-is).
     */
    public function convert(float $amount, string $from, string $to): ?float
    {
        if ($from === $to) {
            return $amount;
        }

        $rates = $this->rates();
        if (!isset($rates[$from], $rates[$to]) || $rates[$to] <= 0) {
            return null;
        }

        // from → AMD → to
        return ($amount * $rates[$from]) / $rates[$to];
    }

    /**
     * POST the SOAP 1.1 ExchangeRatesLatest envelope and parse the response.
     *
     * @return array<string, float>
     */
    private function fetch(): array
    {
        try {
            $response = Http::connectTimeout(2)->timeout(4)
                ->withHeaders(['SOAPAction' => config('currency.cba.soap_action')])
                ->withBody($this->buildEnvelope(), 'text/xml; charset=utf-8')
                ->post(config('currency.cba.endpoint'));
        } catch (\Throwable $e) {
            return [];
        }

        if (!$response->successful()) {
            return [];
        }

        return $this->parse($response->body());
    }

    private function buildEnvelope(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <ExchangeRatesLatest xmlns="http://www.cba.am/" />
  </soap:Body>
</soap:Envelope>
XML;
    }

    /**
     * Parse the SOAP XML into an ISO → AMD-per-unit map.
     * CBA rates: 1 unit of foreign currency = Rate/Amount AMD.
     *
     * @return array<string, float>
     */
    private function parse(string $xml): array
    {
        preg_match_all('/<ISO>([^<]+)<\/ISO>/', $xml, $isoMatches);
        preg_match_all('/<Rate>([^<]+)<\/Rate>/', $xml, $rateMatches);
        preg_match_all('/<Amount>([^<]+)<\/Amount>/', $xml, $amountMatches);

        $rates = [];
        foreach ($isoMatches[1] as $i => $iso) {
            $iso    = trim($iso);
            $rate   = (float) ($rateMatches[1][$i] ?? 0);
            $amount = (float) ($amountMatches[1][$i] ?? 1);
            if ($iso !== '' && $rate > 0 && $amount > 0) {
                $rates[$iso] = $rate / $amount;
            }
        }

        return $rates;
    }
}
