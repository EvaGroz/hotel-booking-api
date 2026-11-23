<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CurrencyService
{
    private const API_URL = 'https://api.frankfurter.dev/v2/rates';

    public const BASE_CURRENCY = 'EUR';

    public const SUPPORTED = [
        'EUR', 'USD', 'GBP', 'CHF', 'JPY', 'CAD', 'AUD', 'SEK',
        'NOK', 'DKK', 'PLN', 'CZK', 'HUF', 'TRY', 'BGN', 'RON',
    ];

    // Го враќа курсот EUR -> $currency. Се кешира 1 час.
    public function getRate(string $currency): float
    {
        if ($currency === self::BASE_CURRENCY) {
            return 1.0;
        }

        return Cache::remember("exchange_rate_EUR_{$currency}", now()->addHour(), function () use ($currency) {
            try {
                $response = Http::timeout(5)->get(self::API_URL, [
                    'base' => self::BASE_CURRENCY,
                    'quotes' => $currency,
                ]);
            } catch (ConnectionException) {
                throw new RuntimeException('Exchange rate service is unavailable.');
            }

            if ($response->failed()) {
                throw new RuntimeException('Exchange rate service is unavailable.');
            }

            $rate = collect($response->json())->firstWhere('quote', $currency)['rate'] ?? null;

            if ($rate === null) {
                throw new RuntimeException("No exchange rate found for {$currency}.");
            }

            return (float) $rate;
        });
    }

    // Претвора износ од евра во друга валута
    public function convert(float $amountInEur, string $currency): float
    {
        return round($amountInEur * $this->getRate($currency), 2);
    }
}