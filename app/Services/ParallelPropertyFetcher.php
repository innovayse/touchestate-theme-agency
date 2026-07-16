<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use Illuminate\Support\Facades\Cache;
use TouchEstate\Sdk\Signer\SignatureV4Signer;

/**
 * Fetches multiple properties concurrently using Guzzle async requests + TE-SigV4 signing.
 *
 * The SDK only exposes a synchronous retrieve($slug) method, so fetching N properties
 * sequentially takes N × API_latency. This service fires all uncached requests in
 * parallel (same timestamp, independent promises) and settles when the slowest completes —
 * wall-clock time ≈ 1 × API_latency regardless of N.
 *
 * Cache semantics: identical to the controllers' Cache::remember('te_prop:{slug}', 86400).
 * Cached slugs skip the HTTP round-trip entirely.
 */
class ParallelPropertyFetcher
{
    private Client $guzzle;
    private SignatureV4Signer $signer;
    private string $publicKey;
    private string $secretKey;
    private string $baseUrl;
    private string $host;

    public function __construct()
    {
        $cfg = config('touchestate');
        $this->publicKey = $cfg['public_key'] ?? '';
        $this->secretKey = $cfg['secret_key'] ?? '';
        $this->baseUrl   = rtrim($cfg['base_url'] ?? 'https://touchestate.io', '/');
        $this->signer    = new SignatureV4Signer();
        $this->host      = $this->buildHost();

        $this->guzzle = new Client([
            'verify'          => $cfg['verify_ssl'] ?? true,
            'timeout'         => $cfg['timeout'] ?? 30,
            'connect_timeout' => $cfg['connect_timeout'] ?? 10,
            'http_errors'     => false,
        ]);
    }

    /**
     * Returns an associative array [slug => propertyArray] for all valid slugs.
     * Preserves original slug order. Silently skips failed/not-found slugs.
     *
     * @param  array<string> $slugs
     * @param  int           $cacheTtl  seconds — must match controllers' Cache::remember TTL
     * @return array<string, array<string, mixed>>
     */
    public function fetchMany(array $slugs, int $cacheTtl = 86400): array
    {
        $cached  = [];
        $toFetch = [];

        foreach ($slugs as $slug) {
            $hit = Cache::get('te_prop:' . $slug);
            if ($hit !== null) {
                $cached[$slug] = $hit;
            } else {
                $toFetch[] = $slug;
            }
        }

        if (!empty($toFetch)) {
            // All parallel requests share one timestamp — valid within the 5-min server window.
            $timestamp = gmdate('Ymd\THis\Z');

            $promises = [];
            foreach ($toFetch as $slug) {
                $promises[$slug] = $this->signedGetAsync(
                    '/api/external/properties/' . $slug,
                    $timestamp,
                );
            }

            // settle() never rejects — each entry is ['state' => 'fulfilled'|'rejected', ...]
            $settled = Utils::settle($promises)->wait();

            foreach ($settled as $slug => $outcome) {
                if ($outcome['state'] !== 'fulfilled') {
                    continue;
                }
                try {
                    $response = $outcome['value'];
                    if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                        continue;
                    }
                    $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
                    if (!is_array($data)) {
                        continue;
                    }
                    $data['slug'] = $slug;
                    Cache::put('te_prop:' . $slug, $data, $cacheTtl);
                    $cached[$slug] = $data;
                } catch (\Throwable) {
                    // malformed JSON or unexpected structure — skip silently
                }
            }
        }

        // Restore original order
        $ordered = [];
        foreach ($slugs as $slug) {
            if (isset($cached[$slug])) {
                $ordered[$slug] = $cached[$slug];
            }
        }

        return $ordered;
    }

    // ── TE-SigV4 signing (mirrors ApiRequestor::request) ───────────────────

    private function signedGetAsync(string $path, string $timestamp): \GuzzleHttp\Promise\PromiseInterface
    {
        $bodyHash = $this->signer->sha256Hex('');

        $headers = [
            'host'               => $this->host,
            'x-te-content-sha256'=> $bodyHash,
            'x-te-date'          => $timestamp,
        ];

        $names         = array_keys($headers);
        sort($names);
        $signedHeaders = implode(';', array_map('strtolower', $names));

        $signature = $this->signer->calculateSignature(
            'GET', $path, '', $headers, $signedHeaders, $bodyHash, $timestamp, $this->secretKey,
        );

        $dateStamp       = substr($timestamp, 0, 8);
        $credentialScope = $this->signer->getCredentialScope($dateStamp);

        $headers['authorization'] = sprintf(
            'TE-HMAC-SHA256 Credential=%s/%s, SignedHeaders=%s, Signature=%s',
            $this->publicKey, $credentialScope, $signedHeaders, $signature,
        );

        return $this->guzzle->getAsync($this->baseUrl . $path, ['headers' => $headers]);
    }

    private function buildHost(): string
    {
        $parts   = parse_url($this->baseUrl);
        $host    = $parts['host']   ?? '';
        $port    = $parts['port']   ?? null;
        $scheme  = $parts['scheme'] ?? 'https';
        $default = $scheme === 'https' ? 443 : 80;

        return ($port !== null && $port !== $default) ? "{$host}:{$port}" : $host;
    }
}
