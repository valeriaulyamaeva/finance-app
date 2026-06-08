<?php

declare(strict_types=1);

namespace app\services;

use app\models\Investment;
use app\models\forms\InvestmentForm;
use DomainException;
use yii\db\Connection;

final readonly class InvestmentService
{
    public function __construct(
        private Connection $db,
        private CurrencyService $currencyService,
        private MarketDataService $marketData,
    ) {}

    public function create(int $userId, InvestmentForm $form): Investment
    {
        if (!$form->validate()) {
            throw new DomainException('Ошибка валидации: ' . implode(', ', $form->getErrorSummary(true)));
        }

        $model = new Investment();
        $model->user_id = $userId;
        $this->fill($model, $form);

        if (!$model->save()) {
            throw new DomainException('Не удалось сохранить актив: ' . json_encode($model->getErrors()));
        }

        return $model;
    }

    public function update(int $id, int $userId, InvestmentForm $form): Investment
    {
        $model = $this->findById($id, $userId);

        if (!$form->validate()) {
            throw new DomainException('Ошибка валидации.');
        }

        $this->fill($model, $form);

        if (!$model->save()) {
            throw new DomainException('Не удалось обновить актив.');
        }

        return $model;
    }

    public function delete(int $id, int $userId): void
    {
        $model = $this->findById($id, $userId);
        if (!$model->delete()) {
            throw new DomainException('Ошибка при удалении актива.');
        }
    }

    public function findById(int $id, int $userId): Investment
    {
        $model = Investment::findOne(['id' => $id, 'user_id' => $userId]);
        if (!$model) {
            throw new DomainException('Актив не найден.');
        }
        return $model;
    }

    /**
     * @return Investment[]
     */
    public function getForUser(int $userId): array
    {
        return Investment::find()
            ->where(['user_id' => $userId])
            ->orderBy(['current_value' => SORT_DESC])
            ->all();
    }

    /**
     * Portfolio summary in the user's currency.
     *
     * @return array{invested: float, current: float, profit: float, profitPercent: float, count: int}
     */
    public function getSummary(int $userId, string $userCurrency): array
    {
        $items = $this->getForUser($userId);

        $invested = 0.0;
        $current = 0.0;
        foreach ($items as $item) {
            $invested += $this->convert((float)$item->invested_amount, $item->currency, $userCurrency);
            $current += $this->convert((float)$item->current_value, $item->currency, $userCurrency);
        }

        $profit = $current - $invested;
        $profitPercent = $invested > 0 ? round(($profit / $invested) * 100, 2) : 0.0;

        return [
            'invested' => round($invested, 2),
            'current' => round($current, 2),
            'profit' => round($profit, 2),
            'profitPercent' => $profitPercent,
            'count' => count($items),
        ];
    }

    /** Total current portfolio value in user currency — used by net-worth / forecast. */
    public function getTotalCurrentValue(int $userId, string $userCurrency): float
    {
        return $this->getSummary($userId, $userCurrency)['current'];
    }

    /**
     * Refresh current value of all tickered assets from the market.
     *
     * @return array{updated: int, failed: int}
     */
    public function refreshPrices(int $userId, string $userCurrency): array
    {
        $items = $this->getForUser($userId);
        $updated = 0;
        $failed = 0;

        foreach ($items as $item) {
            if (!$item->supportsLivePrice() || !$item->quantity) {
                continue;
            }

            $res = $this->marketData->getPrice($item->type, $item->ticker);
            if (!$res['success']) {
                $failed++;
                continue;
            }

            // Price comes in USD; convert to the asset's own currency, then store value.
            $priceInAssetCurrency = $this->convert($res['price'], $res['currency'], $item->currency);
            $item->last_price = round($priceInAssetCurrency, 8);
            $item->current_value = round($priceInAssetCurrency * (float)$item->quantity, 2);
            $item->price_updated_at = date('Y-m-d H:i:s');
            $item->save(false);
            $updated++;
        }

        return ['updated' => $updated, 'failed' => $failed];
    }

    private function fill(Investment $model, InvestmentForm $form): void
    {
        $model->name = $form->name;
        $model->type = $form->type;
        $model->ticker = $form->ticker ? strtoupper(trim((string)$form->ticker)) : null;
        $model->quantity = $form->quantity !== null && $form->quantity !== '' ? (float)$form->quantity : null;
        $model->invested_amount = (float)$form->invested_amount;

        // If ticker + quantity given, try to compute current value from live price.
        $computed = null;
        if ($model->ticker && $model->quantity && $model->supportsLivePrice()) {
            $res = $this->marketData->getPrice($model->type, $model->ticker);
            if ($res['success']) {
                $priceInAssetCurrency = $this->convert($res['price'], $res['currency'], $form->currency ?: 'BYN');
                $model->last_price = round($priceInAssetCurrency, 8);
                $model->price_updated_at = date('Y-m-d H:i:s');
                $computed = round($priceInAssetCurrency * (float)$model->quantity, 2);
            }
        }

        $model->current_value = $computed
            ?? ($form->current_value !== null && $form->current_value !== ''
                ? (float)$form->current_value
                : (float)$form->invested_amount);

        $model->currency = $form->currency ?: 'BYN';
        $model->purchase_date = $form->purchase_date ?: null;
        $model->note = $form->note ?: null;
    }

    private function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }
        return $this->currencyService->fromBase(
            $this->currencyService->toBase($amount, $from),
            $to
        );
    }
}
