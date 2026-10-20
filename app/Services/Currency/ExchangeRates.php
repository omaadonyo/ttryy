<?php

namespace App\Services\Currency;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Supplies live foreign-exchange rates so the Business Tools UI can convert
 * figures between currencies on the fly. Rates are quoted against USD and
 * cached for a few hours; when the upstream provider is unreachable we fall
 * back to a sensible table so the app never breaks, and a deployment can pin
 * its own "standard" rates through config/fx.php.
 */
class ExchangeRates
{
    public const BASE = 'USD';

    public const CACHE_KEY = 'fx.usd.snapshot';

    public const CACHE_HOURS = 6;

    /** Currencies the switcher offers. */
    public const SUPPORTED = ['USD', 'UGX', 'KES', 'TZS', 'EUR', 'GBP'];

    /** Currencies that are never shown with fractional units. */
    public const WHOLE = ['UGX', 'KES', 'TZS', 'JPY'];

    /**
     * The current rate table, quoted as "1 USD = X".
     *
     * @return array{base: string, rates: array<string, float>, fetched_at: ?string, source: string, stale: bool}
     */
    public function snapshot(bool $fresh = false): array
    {
        if ($fresh) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, now()->addHours(self::CACHE_HOURS), fn () => $this->fetch());
    }

    /**
     * Convert an amount between two currencies using the current table.
     */
    public function convert(float|int $amount, string $from, string $to, ?array $snapshot = null): float
    {
        $snapshot ??= $this->snapshot();
        $rates = $snapshot['rates'] ?? [];

        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to || ! isset($rates[$from], $rates[$to]) || $rates[$from] <= 0) {
            return (float) $amount;
        }

        $usd = (float) $amount / (float) $rates[$from];

        return $usd * (float) $rates[$to];
    }

    protected function fetch(): array
    {
        $fallback = $this->table($this->fallbackRates());

        if (app()->runningUnitTests() && ! config('fx.allow_in_tests', false)) {
            return $this->shape($fallback, null, 'fallback', true);
        }

        $endpoint = (string) config('fx.endpoint', '');
        $live = [];

        if ($endpoint !== '') {
            try {
                $response = Http::timeout(6)->acceptJson()->get($endpoint);
                $payload = $response->ok() ? $response->json() : null;
                $rates = is_array($payload) ? ($payload['rates'] ?? $payload['conversion_rates'] ?? []) : [];

                foreach ((array) $rates as $code => $value) {
                    if (is_numeric($value) && (float) $value > 0) {
                        $live[strtoupper((string) $code)] = (float) $value;
                    }
                }
            } catch (\Throwable $e) {
                $live = [];
            }
        }

        if ($live === []) {
            return $this->shape($fallback, null, 'fallback', true);
        }

        $merged = array_replace($fallback, $this->table($live));

        return $this->shape($merged, now()->toIso8601String(), 'live', false);
    }

    protected function shape(array $rates, ?string $fetchedAt, string $source, bool $stale): array
    {
        return [
            'base' => self::BASE,
            'rates' => $rates,
            'fetched_at' => $fetchedAt,
            'source' => $source,
            'stale' => $stale,
        ];
    }

    /** Restrict an arbitrary rate map to the supported currencies, keeping USD = 1. */
    protected function table(array $rates): array
    {
        $out = [self::BASE => 1.0];

        foreach (self::SUPPORTED as $code) {
            if ($code === self::BASE) {
                continue;
            }
            $value = $rates[$code] ?? config("fx.overrides.$code");
            if (is_numeric($value) && (float) $value > 0) {
                $out[$code] = round((float) $value, 6);
            }
        }

        return $out;
    }

    protected function fallbackRates(): array
    {
        return [
            'USD' => 1.0,
            'UGX' => 3800.0,
            'KES' => 129.0,
            'TZS' => 2500.0,
            'EUR' => 0.92,
            'GBP' => 0.79,
        ];
    }
}
