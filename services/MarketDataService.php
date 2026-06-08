<?php

declare(strict_types=1);

namespace app\services;

use app\models\Investment;
use Yii;
use yii\caching\Cache;

/**
 * Fetches live market prices.
 *  - Crypto via CoinGecko (no API key needed)
 *  - Stocks / ETF via Finnhub (free API key, set in params['finnhubApiKey'])
 *
 * Prices are returned in USD and cached for 5 minutes to respect rate limits.
 */
final readonly class MarketDataService
{
    private const CACHE_TTL = 300; // 5 min
    private const HTTP_TIMEOUT = 5;

    /** Common ticker → CoinGecko id map (CoinGecko uses ids, not symbols). */
    private const COINGECKO_IDS = [
        'BTC' => 'bitcoin',
        'ETH' => 'ethereum',
        'USDT' => 'tether',
        'BNB' => 'binancecoin',
        'SOL' => 'solana',
        'XRP' => 'ripple',
        'USDC' => 'usd-coin',
        'ADA' => 'cardano',
        'DOGE' => 'dogecoin',
        'TRX' => 'tron',
        'TON' => 'the-open-network',
        'AVAX' => 'avalanche-2',
        'DOT' => 'polkadot',
        'MATIC' => 'matic-network',
        'LTC' => 'litecoin',
        'SHIB' => 'shiba-inu',
        'LINK' => 'chainlink',
        'BCH' => 'bitcoin-cash',
        'XLM' => 'stellar',
        'NEAR' => 'near',
        'ATOM' => 'cosmos',
        'XMR' => 'monero',
    ];

    /**
     * Get current price for a ticker (in USD).
     *
     * @return array{success: bool, price?: float, currency?: string, message?: string}
     */
    public function getPrice(string $type, string $ticker): array
    {
        $ticker = strtoupper(trim($ticker));
        if ($ticker === '') {
            return ['success' => false, 'message' => 'Не указан тикер'];
        }

        $cacheKey = "market_price_{$type}_{$ticker}";
        $cache = Yii::$app->cache;
        if ($cache instanceof Cache && ($cached = $cache->get($cacheKey)) !== false) {
            return $cached;
        }

        $result = match ($type) {
            Investment::TYPE_CRYPTO => $this->fetchCrypto($ticker),
            Investment::TYPE_STOCKS, Investment::TYPE_FUND, Investment::TYPE_BONDS => $this->fetchStock($ticker),
            default => ['success' => false, 'message' => 'Для этого типа актива цена не подгружается автоматически'],
        };

        if ($result['success'] && $cache instanceof Cache) {
            $cache->set($cacheKey, $result, self::CACHE_TTL);
        }

        return $result;
    }

    private function fetchCrypto(string $ticker): array
    {
        $id = self::COINGECKO_IDS[$ticker] ?? strtolower($ticker);
        $url = "https://api.coingecko.com/api/v3/simple/price?ids={$id}&vs_currencies=usd";

        $data = $this->httpGetJson($url);
        if ($data === null) {
            return ['success' => false, 'message' => 'CoinGecko недоступен'];
        }

        $price = $data[$id]['usd'] ?? null;
        if ($price === null) {
            return ['success' => false, 'message' => "Тикер {$ticker} не найден в CoinGecko"];
        }

        return ['success' => true, 'price' => (float)$price, 'currency' => 'USD'];
    }

    private function fetchStock(string $ticker): array
    {
        $apiKey = Yii::$app->params['finnhubApiKey'] ?? '';
        if ($apiKey === '') {
            return ['success' => false, 'message' => 'Не настроен ключ Finnhub (params.finnhubApiKey)'];
        }

        $url = "https://finnhub.io/api/v1/quote?symbol={$ticker}&token={$apiKey}";
        $data = $this->httpGetJson($url);
        if ($data === null) {
            return ['success' => false, 'message' => 'Finnhub недоступен'];
        }

        // Finnhub: c = current price, 0 means unknown symbol
        $price = $data['c'] ?? 0;
        if (!$price) {
            return ['success' => false, 'message' => "Тикер {$ticker} не найден"];
        }

        return ['success' => true, 'price' => (float)$price, 'currency' => 'USD'];
    }

    private function httpGetJson(string $url): ?array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::HTTP_TIMEOUT,
            CURLOPT_USERAGENT => 'Vale-Finance/1.0',
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        $data = json_decode($response, true);
        return is_array($data) ? $data : null;
    }
}
