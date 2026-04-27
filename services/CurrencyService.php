<?php

declare(strict_types=1);

namespace app\services;

use Exception;
use Yii;
use yii\base\Component;
use yii\caching\Cache;

final class CurrencyService extends Component
{
    private string $apiKey = 'e8c2f4afec9e1abf33fd661d';
    private string $baseUrl = 'https://v6.exchangerate-api.com/v6';
    private const CACHE_DURATION = 86400; // 24 hours
    private const FALLBACK_CACHE_DURATION = 300; // 5 min for failed requests
    private const HTTP_TIMEOUT = 3; // seconds

    /** Hardcoded fallback rates (relative to BYN) — used when API is down */
    private const FALLBACK_RATES = [
        'BYN' => ['BYN' => 1.0, 'USD' => 0.31, 'EUR' => 0.28, 'RUB' => 28.5],
        'USD' => ['BYN' => 3.25, 'USD' => 1.0, 'EUR' => 0.92, 'RUB' => 92.0],
        'EUR' => ['BYN' => 3.55, 'USD' => 1.09, 'EUR' => 1.0, 'RUB' => 100.0],
        'RUB' => ['BYN' => 0.035, 'USD' => 0.011, 'EUR' => 0.01, 'RUB' => 1.0],
    ];

    public function convertTo(float $amount, string $from, string $to): float
    {
        return $this->convert($amount, $from, $to);
    }

    public function convert(float $amount, ?string $from, ?string $to): float
    {
        if (!$from || !$to || $from === $to || abs($amount) < 0.00001) {
            return $amount;
        }

        try {
            $rate = $this->getRate($from, $to);
            return round($amount * $rate, 2);
        } catch (Exception $e) {
            Yii::error("Conversion error: " . $e->getMessage(), __METHOD__);
            return $amount;
        }
    }

    public function getRate(string $from, string $to): float
    {
        if ($from === $to) {
            return 1.0;
        }

        $rates = $this->getRatesForCurrency($from);

        if (!isset($rates[$to])) {
            Yii::error("Rate for $to not found for base $from", __METHOD__);
            throw new Exception("Курс для валюты $to не найден.");
        }

        return (float)$rates[$to];
    }

    private function getRatesForCurrency(string $base): array
    {
        $cacheKey = "currency_rates_v2_$base";
        $cache = Yii::$app->cache;

        if ($cache instanceof Cache && ($cached = $cache->get($cacheKey)) !== false) {
            return $cached;
        }

        $url = "$this->baseUrl/$this->apiKey/latest/$base";

        try {
            $context = stream_context_create([
                'http' => ['timeout' => self::HTTP_TIMEOUT, 'ignore_errors' => true],
            ]);
            $response = @file_get_contents($url, false, $context);
            if (!$response) {
                throw new Exception("API request failed for $base");
            }

            $data = json_decode($response, true);
            if (($data['result'] ?? '') !== 'success') {
                throw new Exception($data['error-type'] ?? 'Unknown API error');
            }

            $rates = $data['conversion_rates'] ?? [];

            if ($cache instanceof Cache) {
                $cache->set($cacheKey, $rates, self::CACHE_DURATION);
            }

            return $rates;
        } catch (Exception $e) {
            Yii::error("Currency API Error: " . $e->getMessage(), __METHOD__);
            // Cache fallback rates briefly so we don't hammer the broken API
            $fallback = self::FALLBACK_RATES[$base] ?? [];
            if ($cache instanceof Cache && !empty($fallback)) {
                $cache->set($cacheKey, $fallback, self::FALLBACK_CACHE_DURATION);
            }
            return $fallback;
        }
    }

    public function toBase(float $amount, string $userCurrency): float
    {
        return $this->convert($amount, $userCurrency, 'BYN');
    }

    public function fromBase(float $amount, string $userCurrency): float
    {
        return $this->convert($amount, 'BYN', $userCurrency);
    }
}