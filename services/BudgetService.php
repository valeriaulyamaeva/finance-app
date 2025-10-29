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

        if ($remaining < 0) {
            Notification::createForUser(
                $budget->user_id,
                "Бюджет '$budget->name' превышен на " . number_format(abs($remaining), 2) . " $budget->currency",
                Notification::TYPE_BUDGET_EXCEED
            );
        }

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
}