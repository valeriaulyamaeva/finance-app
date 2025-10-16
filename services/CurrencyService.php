<?php

namespace app\services;

use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\Cache;

class CurrencyService
{
    private string $apiKey = 'e8c2f4afec9e1abf33fd661d';
    private string $baseUrl = 'https://v6.exchangerate-api.com/v6';

    /**
     * @throws Exception
     */
    public function getRate(string $from, string $to): float
    {
        if ($from === $to) {
            return 1.0;
        }

        $cacheKey = "currency_rate_{$from}_$to";
        $cached = $this->getFromCache($cacheKey);

        if ($cached !== null && $cached > 0) {
            Yii::info("Retrieved rate from cache: $cached for $from to $to", __METHOD__);
            return $cached;
        }

        $url = "$this->baseUrl/$this->apiKey/latest/$from";

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'method' => 'GET',
                'header' => [
                    'User-Agent: Mozilla/5.0 (compatible; CurrencyBot/1.0)'
                ]
            ]
        ]);

        $json = @file_get_contents($url, false, $context);

        if ($json === false) {
            $error = error_get_last();
            Yii::error("Failed to fetch data from API for $from: " . ($error['message'] ?? 'Unknown error'), __METHOD__);
            throw new Exception("Не удалось получить данные с API для валюты $from");
        }

        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            Yii::error("Invalid API response format for $from: " . json_last_error_msg(), __METHOD__);
            throw new Exception("Неверный формат ответа API для валюты $from");
        }

        if (!isset($data['conversion_rates'][$to])) {
            Yii::error("Conversion rate for $to not found in API response: " . json_encode($data), __METHOD__);
            throw new Exception("Не удалось получить курс для $to из $from");
        }

        $rate = (float) $data['conversion_rates'][$to];

        if ($rate <= 0 || $rate > 10000) {
            Yii::error("Invalid exchange rate $rate for $from to $to", __METHOD__);
            throw new Exception("Некорректный курс валюты: $rate");
        }

        Yii::info("Fetched rate from API: $rate for $from to $to", __METHOD__);
        $this->setToCache($cacheKey, $rate);
        return $rate;
    }

    /**
     * @throws InvalidConfigException
     */
    private function getFromCache(string $key)
    {
        if (!Yii::$app->has('cache') || !($cache = Yii::$app->get('cache')) instanceof Cache) {
            Yii::warning("Cache component not available", __METHOD__);
            return null;
        }

        try {
            $value = $cache->get($key);
            if ($value === false) {
                Yii::info("Cache miss for key: $key", __METHOD__);
                return null;
            }
            return $value;
        } catch (Exception $e) {
            Yii::warning("Cache retrieval error: " . $e->getMessage(), __METHOD__);
            return null;
        }
    }

    /**
     * @throws InvalidConfigException
     */
    private function setToCache(string $key, float $value): void
    {
        if (!Yii::$app->has('cache') || !($cache = Yii::$app->get('cache')) instanceof Cache) {
            Yii::warning("Cache component not available for storing", __METHOD__);
            return;
        }

        try {
            $cache->set($key, $value, 3600);
            Yii::info("Cached rate for key: $key, value: $value", __METHOD__);
        } catch (Exception $e) {
            Yii::warning("Cache storage error: " . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * @throws Exception
     */
    public function toBase(float $amount, string $userCurrency): float
    {
        return $userCurrency === 'BYN'
            ? $amount
            : $amount * $this->getRate($userCurrency, 'BYN');
    }

    /**
     * @throws Exception
     */
    public function fromBase(float $amount, string $userCurrency): float
    {
        return $userCurrency === 'BYN'
            ? $amount
            : $amount * $this->getRate('BYN', $userCurrency);
    }

}