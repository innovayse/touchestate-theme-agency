<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CbaRatesService
{
    private const FALLBACK = ['USD' => 390.0, 'EUR' => 420.0, 'RUB' => 4.3, 'AMD' => 1.0];

    public function getRates(): array
    {
        $cached = Cache::get('cba_rates');
        if ($cached) return $cached;
        $fresh = $this->fetchFromApi();
        if ($fresh !== self::FALLBACK) {
            Cache::put('cba_rates', $fresh, 43200);
        }
        return $fresh;
    }

    public function convert(float $amount, string $from, string $to): float
    {
        $rates = $this->getRates();
        $amd = $amount * ($rates[$from] ?? 1.0);
        return $amd / ($rates[$to] ?? 1.0);
    }

    private function fetchFromApi(): array
    {
        $soap = '<?xml version="1.0" encoding="utf-8"?>'
            . '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
            . ' xmlns:xsd="http://www.w3.org/2001/XMLSchema"'
            . ' xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
            . '<soap:Body><ExchangeRatesLatest xmlns="http://www.cba.am/" /></soap:Body>'
            . '</soap:Envelope>';

        $ch = curl_init('http://api.cba.am/exchangerates.asmx');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $soap,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: "http://www.cba.am/ExchangeRatesLatest"',
            ],
        ]);
        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        curl_close($ch);

        if ($errno || !$response) {
            return Cache::get('cba_rates') ?? self::FALLBACK;
        }

        return $this->parseResponse($response);
    }

    private function parseResponse(string $xml): array
    {
        $rates = ['AMD' => 1.0];
        try {
            $doc = new \SimpleXMLElement($xml);
            $doc->registerXPathNamespace('ns', 'http://www.cba.am/');
            $items = $doc->xpath('//ns:ExchangeRate') ?: [];
            foreach ($items as $item) {
                $iso    = trim((string) ($item->ISO ?? ''));
                $rate   = (float) ($item->Rate ?? 0);
                $amount = (float) ($item->Amount ?? 1);
                if ($iso && $rate > 0 && $amount > 0) {
                    $rates[$iso] = $rate / $amount;
                }
            }
        } catch (\Throwable) {
            return Cache::get('cba_rates') ?? self::FALLBACK;
        }

        if (empty($rates['USD']) || empty($rates['EUR'])) {
            return Cache::get('cba_rates') ?? self::FALLBACK;
        }

        return $rates;
    }
}
