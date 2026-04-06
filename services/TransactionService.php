<?php

declare(strict_types=1);

namespace app\services;

use app\models\Budget;
use app\models\Category;
use app\models\Goal;
use app\models\Transaction;
use app\models\forms\TransactionForm;
use Exception;
use Yii;
use yii\web\NotFoundHttpException;

readonly class TransactionService
{
    public function __construct(
        private CurrencyService $currencyService
    ) {}

    public function create(array $data, int $userId): Transaction
    {
        $form = new TransactionForm();
        if (!$form->load($data, '') || !$form->validate()) {
            throw new Exception('Ошибка валидации: ' . implode(', ', $form->getErrorSummary(true)));
        }

        return Yii::$app->db->transaction(function() use ($form, $userId) {
            $transaction = new Transaction();
            $transaction->user_id = $userId;
            $transaction->attributes = $form->attributes;

            // 1. Устанавливаем тип на основе логики категорий
            $transaction->type = $this->resolveTypeByCategory($transaction->category_id, $transaction->goal_id);

            // 2. Применяем изменения к бюджету/целям
            $this->syncRelatedEntities($transaction);

            if (!$transaction->save()) {
                throw new Exception('Ошибка сохранения: ' . json_encode($transaction->getErrors()));
            }
            return $transaction;
        });
    }

    public function update(int $id, array $data): Transaction
    {
        $transaction = Transaction::findOne($id);
        if (!$transaction) throw new NotFoundHttpException('Транзакция не найдена');

        $oldTransaction = clone $transaction;

        $form = new TransactionForm();
        if (!$form->load($data, '') || !$form->validate()) {
            throw new Exception('Ошибка валидации');
        }

        return Yii::$app->db->transaction(function() use ($transaction, $oldTransaction, $form) {
            $this->revertRelatedEntities($oldTransaction);

            $transaction->attributes = $form->attributes;
            $transaction->type = $this->resolveTypeByCategory($transaction->category_id, $transaction->goal_id);

            $this->syncRelatedEntities($transaction);

            if (!$transaction->save()) {
                throw new Exception('Ошибка обновления');
            }
            return $transaction;
        });
    }

    public function delete(int $id): void
    {
        $transaction = Transaction::findOne($id);
        if (!$transaction) return;

        Yii::$app->db->transaction(function() use ($transaction) {
            $this->revertRelatedEntities($transaction);
            $transaction->delete();
        });
    }

    private function revertRelatedEntities(Transaction $t): void
    {
        if ($t->category_id && ($t->type === Transaction::TYPE_EXPENSE || $t->type === Transaction::TYPE_GOAL)) {
            $budget = Budget::findOne(['category_id' => $t->category_id, 'user_id' => $t->user_id]);
            if ($budget) {
                $budget->spent = max(0, $budget->spent - $t->amount);
                $budget->save(false);
            }
        }

        if ($t->goal_id) {
            $goal = Goal::findOne($t->goal_id);
            if ($goal) {
                $amount = $this->convertToGoalCurrency($t->amount, $t->currency, $goal->currency);
                $goal->current_amount = max(0, $goal->current_amount - $amount);
                if (method_exists($goal, 'updateStatus')) { $goal->updateStatus(); }
                $goal->save(false);
            }
        }
    }

    private function syncRelatedEntities(Transaction $t): void
    {
        if ($t->category_id && ($t->type === Transaction::TYPE_EXPENSE || $t->type === Transaction::TYPE_GOAL)) {
            $budget = Budget::find()
                ->where(['category_id' => $t->category_id, 'user_id' => $t->user_id])
                ->andWhere(['<=', 'start_date', $t->date])
                ->andWhere(['>=', 'end_date', $t->date])
                ->one();

            if ($budget) {
                $budget->spent = (float)$budget->spent + $t->amount;
                $budget->save(false);
                $t->budget_id = $budget->id;
            }
        }

        if ($t->goal_id) {
            $goal = Goal::findOne($t->goal_id);
            if ($goal) {
                $amount = $this->convertToGoalCurrency($t->amount, $t->currency, $goal->currency);
                $goal->current_amount = $goal->current_amount + $amount;
                if (method_exists($goal, 'updateStatus')) { $goal->updateStatus(); }
                $goal->save(false);
            }
        }
    }

    private function convertToGoalCurrency(float $amount, string $from, string $to): float
    {
        return ($from === $to) ? $amount : $this->currencyService->fromBase($this->currencyService->toBase($amount, $from), $to);
    }

    public function resolveTypeByCategory($categoryId, $goalId): string
    {
        if ($goalId) return Transaction::TYPE_GOAL;
        if (!$categoryId) return Transaction::TYPE_EXPENSE;

        $category = Category::findOne($categoryId);
        return match ($category?->type) {
            Category::TYPE_INCOME => Transaction::TYPE_INCOME,
            Category::TYPE_GOAL => Transaction::TYPE_GOAL,
            default => Transaction::TYPE_EXPENSE,
        };
    }

    public function getMonthlySummary(int $userId, ?string $start = null, ?string $end = null): array
    {
        $start = $start ?? date('Y-m-01');
        $end = $end ?? date('Y-m-t');
        $userCurrency = Yii::$app->user->identity->currency ?? 'BYN';

        $transactions = Transaction::find()
            ->where(['user_id' => $userId])
            ->andWhere(['<=', 'date', $end])
            ->all();

        $prevBalance = 0.0;
        $income = 0.0;
        $expense = 0.0;

        foreach ($transactions as $t) {
            $amount = $this->currencyService->convertTo((float)$t->amount, $t->currency, $userCurrency);

            if ($t->date < $start) {
                $prevBalance += ($t->type === Transaction::TYPE_INCOME) ? $amount : -$amount;
            } else {
                if ($t->type === Transaction::TYPE_INCOME) {
                    $income += $amount;
                } else {
                    $expense += $amount;
                }
            }
        }

        return [
            'previousBalance' => round($prevBalance, 2),
            'income' => round($income, 2),
            'expense' => round($expense, 2),
            'balance' => round($prevBalance + $income - $expense, 2),
            'currency' => $userCurrency,
        ];
    }
}