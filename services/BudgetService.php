<?php

namespace app\services;

use app\models\Budget;
use app\models\Notification;
use app\models\Transaction;
use RuntimeException;
use Throwable;
use yii\db\Exception;
use yii\db\StaleObjectException;

class BudgetService
{
    private CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * @throws Exception
     */
    public function create(array $data, int $userId): Budget
    {
        $budget = new Budget();
        $budget->load($data);
        $budget->user_id = $userId;
        $budget->currency = $data['currency'] ?? 'BYN';

        if (empty($budget->category_id) || !is_numeric($budget->category_id)) {
            throw new RuntimeException('Категория не выбрана.');
        }

        if (!$budget->save()) {
            throw new RuntimeException('Ошибка при создании бюджета: ' . json_encode($budget->errors, JSON_UNESCAPED_UNICODE));
        }

        return $budget;
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): Budget
    {
        $budget = Budget::findOne($id);
        if (!$budget) {
            throw new RuntimeException('Бюджет не найден');
        }

        $budget->load($data);

        $budget->currency = $data['currency'] ?? ($data['Budget']['currency'] ?? 'BYN');

        if (empty($budget->category_id) || !is_numeric($budget->category_id)) {
            throw new RuntimeException('Категория не выбрана.');
        }

        if (!$budget->save()) {
            throw new RuntimeException('Ошибка при обновлении: ' . json_encode($budget->errors, JSON_UNESCAPED_UNICODE));
        }

        return $budget;
    }

    public function delete(int $id): void
    {
        $budget = Budget::findOne($id);
        try {
            if ($budget && !$budget->delete()) {
                throw new RuntimeException('Ошибка при удалении бюджета');
            }
        } catch (StaleObjectException|Throwable) {
        }
    }

    /**
     * @throws \Exception
     */
    public function calculateSummary(Budget $budget): array
    {
        $query = Transaction::find()
            ->where(['budget_id' => $budget->id])
            ->andWhere(['type' => ['expense', 'goal']]);

        $query->andWhere(['>=', 'date', $budget->start_date]);
        if ($budget->end_date) {
            $query->andWhere(['<=', 'date', $budget->end_date]);
        }

        $transactions = $query->all();

        $spent = 0;
        foreach ($transactions as $t) {
            $amount = $t->amount;
            if ($t->currency !== $budget->currency) {
                $amount = $this->currencyService->fromBase(
                    $this->currencyService->toBase($amount, $t->currency),
                    $budget->currency
                );
            }
            $spent += $amount;
        }

        $remaining = $budget->amount - $spent;

        $this->handleBudgetExceedNotification($budget, $remaining);

        return [
            'spent' => $spent,
            'remaining' => $remaining,
            'total' => $budget->amount,
        ];
    }

    /**
     * @throws \Exception
     */
    public function getUserSummary(int $userId): array
    {
        $budgets = Budget::find()->where(['user_id' => $userId])->all();

        $totalBudget = 0;
        $totalSpent = 0;

        foreach ($budgets as $budget) {
            $sum = $this->calculateSummary($budget);
            $totalBudget += $sum['total'];
            $totalSpent += $sum['spent'];
        }

        return [
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
            'remaining' => $totalBudget - $totalSpent,
            ];
    }

    private function handleBudgetExceedNotification(Budget $budget, float $remaining): void
    {
        $userId = $budget->user_id;
        $budgetId = $budget->id;
        $type = Notification::TYPE_BUDGET_EXCEED;

        $existing = Notification::find()
            ->where([
                'user_id' => $userId,
                'type' => $type,
                'related_type' => 'budget',
                'related_id' => $budgetId,
            ])
            ->one();

        if ($remaining >= 0) {
            if ($existing) {
                $existing->delete();
            }
            return;
        }

        $exceedAmount = abs($remaining);
        $newMessage = "Бюджет '$budget->name' превышен на " . number_format($exceedAmount, 2) . " $budget->currency";

        if ($existing) {
            $pattern = '/на\s+([\d\.,]+)\s+' . preg_quote($budget->currency, '/') . '/u';
            $oldExceedMatch = preg_match($pattern, $existing->message, $matches);
            $oldExceedAmount = $oldExceedMatch ? (float)str_replace(',', '', $matches[1]) : null;

            $amountChanged = $oldExceedAmount === null || abs($oldExceedAmount - $exceedAmount) > 0.01;

            $existing->message = $newMessage;

            if ($amountChanged) {
                $existing->read_status = 0;
            }

            $existing->save(false);
        } else {
            Notification::createForUser(
                $userId,
                $newMessage,
                $type,
                'budget',
                $budgetId
            );
        }
    }
}