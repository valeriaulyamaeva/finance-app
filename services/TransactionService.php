<?php

namespace app\services;

use app\models\Budget;
use app\models\Category;
use app\models\Goal;
use app\models\Transaction;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

class TransactionService
{
    private CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * @throws Exception
     */
    public function create(array $data, int $userId): Transaction
    {
        $transaction = new Transaction([
            'user_id' => $userId,
            'category_id' => $data['category_id'] ?? null,
            'goal_id' => $data['goal_id'] ?? null,
        ]);

        $this->updateRelatedEntities($transaction, $data);

        if (!$transaction->validate() || !$transaction->save()) {
            Yii::error('Validation errors: ' . json_encode($transaction->errors, JSON_UNESCAPED_UNICODE), __METHOD__);
            throw new Exception('Ошибка при создании транзакции: ' . json_encode($transaction->errors, JSON_UNESCAPED_UNICODE));
        }

        return $transaction;
    }

    /**
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function update(int $id, array $data): Transaction
    {
        $transaction = Transaction::findOne($id);
        if (!$transaction) {
            throw new NotFoundHttpException('Транзакция не найдена');
        }

        $this->updateRelatedEntities($transaction, $data);

        if (!$transaction->validate() || !$transaction->save()) {
            throw new Exception('Ошибка при обновлении транзакции: ' . json_encode($transaction->errors, JSON_UNESCAPED_UNICODE));
        }

        return $transaction;
    }

    /**
     * @throws Exception
     */
    private function resolveTypeByCategory(?int $categoryId, ?int $goalId): string
    {
        if ($goalId !== null && !is_int($goalId)) {
            $goalId = (int)$goalId;
        }
        if ($categoryId !== null && !is_int($categoryId)) {
            $categoryId = (int)$categoryId;
        }

        if ($goalId !== null) {
            return Transaction::TYPE_GOAL;
        }

        if ($categoryId === null) {
            return Transaction::TYPE_EXPENSE;
        }

        $category = Category::findOne($categoryId);
        if (!$category) {
            throw new Exception('Категория не найдена');
        }

        return match ($category->type ?? 'expense') {
            'income' => Transaction::TYPE_INCOME,
            'goal' => Transaction::TYPE_GOAL,
            default => Transaction::TYPE_EXPENSE,
        };
    }


    /**
     * @throws Exception
     */
    public function delete(int $id): void
    {
        $transaction = Transaction::findOne($id);
        if (!$transaction) {
            return;
        }

        if ($transaction->category_id) {
            $budget = Budget::findOne(['category_id' => $transaction->category_id, 'user_id' => $transaction->user_id]);
            if ($budget) {
                if ($transaction->isTypeExpense()) {
                    $budget->spent = max(0, $budget->spent - $transaction->amount);
                } elseif ($transaction->isTypeIncome()) {
                    $budget->spent += $transaction->amount;
                }
                $budget->updated_at = date('Y-m-d H:i:s');
                $budget->save(false);
            }
        }

        if ($transaction->goal_id) {
            $goal = Goal::findOne($transaction->goal_id);
            if ($goal) {
                $goal->current_amount = max(0, $goal->current_amount - $transaction->amount);
                $goal->status = $goal->current_amount >= $goal->target_amount
                    ? Goal::STATUS_COMPLETED
                    : ($goal->current_amount > 0 ? Goal::STATUS_ACTIVE : Goal::STATUS_ACTIVE);
                $goal->updated_at = date('Y-m-d H:i:s');
                $goal->save(false);
            }
        }

        try {
            $transaction->delete();
        } catch (Throwable $e) {
            Yii::error('Ошибка при удалении транзакции: ' . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * @throws \Exception
     */
    public function getSummary(int $userId): array
    {
        $transactions = Transaction::find()->where(['user_id' => $userId])->all();
        $income = 0;
        $expense = 0;

        $userCurrency = Yii::$app->user->identity->currency ?? 'BYN';

        foreach ($transactions as $t) {
            $amount = $t->amount;

            if ($t->currency !== $userCurrency) {
                $amount = $this->currencyService->fromBase(
                    $this->currencyService->toBase($amount, $t->currency),
                    $userCurrency
                );
            }

            if ($t->isTypeIncome()) {
                $income += $amount;
            } else {
                $expense += $amount;
            }
        }

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'currency' => $userCurrency,
        ];
    }


    /**
     * @param Transaction $transaction
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function updateRelatedEntities(Transaction $transaction, array $data): void
    {
        $isNew = $transaction->isNewRecord;

        $oldAmount = $isNew ? 0 : $transaction->amount;
        $oldCategoryId = $isNew ? null : $transaction->category_id;
        $oldGoalId = $isNew ? null : $transaction->goal_id;

        $transaction->load($data);
        $transaction->goal_id = $data['goal_id'] ?? $data['Transaction']['goal_id'] ?? null;
        $transaction->category_id = $data['category_id'] ?? $data['Transaction']['category_id'] ?? null;

        $categoryId = $transaction->category_id ? (int)$transaction->category_id : null;
        $goalId = $transaction->goal_id ? (int)$transaction->goal_id : null;

        $transaction->type = $this->resolveTypeByCategory($categoryId, $goalId);

        $user = Yii::$app->user->identity;
        if (!$user) {
            throw new Exception('Пользователь не найден для транзакции');
        }
        $transaction->currency = $user->currency;

        if ($oldCategoryId) {
            $oldBudget = Budget::findOne(['category_id' => $oldCategoryId, 'user_id' => $transaction->user_id]);
            if ($oldBudget && $transaction->isTypeExpense()) {
                $oldBudget->spent = max(0, $oldBudget->spent - $oldAmount);
                $oldBudget->updated_at = date('Y-m-d H:i:s');
                $oldBudget->save(false);
            }
        }

        if ($categoryId) {
            $budget = Budget::findOne(['category_id' => $categoryId, 'user_id' => $transaction->user_id]);
            if ($budget && $transaction->isTypeExpense()) {
                $budget->spent += $transaction->amount;
                $budget->updated_at = date('Y-m-d H:i:s');
                $budget->save(false);
                $transaction->budget_id = $budget->id;
            }
        }

        if ($oldGoalId) {
            $goal = Goal::findOne($oldGoalId);
            if ($goal) {
                $goal->current_amount = max(0, $goal->current_amount - $oldAmount);
                $goal->status = $goal->current_amount >= $goal->target_amount ? Goal::STATUS_COMPLETED : Goal::STATUS_ACTIVE;
                $goal->updated_at = date('Y-m-d H:i:s');
                $goal->save(false);
            }
        }

        if ($goalId) {
            $goal = Goal::findOne($goalId);
            if ($goal) {
                $goal->current_amount += $transaction->amount;
                $goal->status = $goal->current_amount >= $goal->target_amount ? Goal::STATUS_COMPLETED : Goal::STATUS_ACTIVE;
                $goal->updated_at = date('Y-m-d H:i:s');
                $goal->save(false);
            }
        }
    }
}
