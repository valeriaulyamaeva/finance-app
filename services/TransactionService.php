<?php

namespace app\services;

use app\models\Budget;
use app\models\Category;
use app\models\Goal;
use app\models\Transaction;
use Throwable;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

class TransactionService
{
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

    private function resolveTypeByCategory(?int $categoryId, ?int $goalId): string
    {
        if ($goalId !== null) {
            return Transaction::TYPE_GOAL;
        }

        $category = Category::findOne($categoryId);
        return match ($category->type ?? null) {
            'income' => Transaction::TYPE_INCOME,
            default => Transaction::TYPE_EXPENSE,
        };
    }

    public function delete(int $id): void
    {
        if ($transaction = Transaction::findOne($id)) {
            try {
                $transaction->delete();
            } catch (Throwable) {
            }
        }
    }

    public function getSummary(int $userId): array
    {
        $income = (float)Transaction::find()
            ->where(['user_id' => $userId, 'type' => Transaction::TYPE_INCOME])
            ->sum('amount');

        $expense = (float)Transaction::find()
            ->where(['user_id' => $userId])
            ->andWhere(['in', 'type', [Transaction::TYPE_EXPENSE, Transaction::TYPE_GOAL]])
            ->sum('amount');

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
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
        $transaction->load($data);

        $categoryId = $transaction->category_id ?? $data['category_id'] ?? null;
        $goalId = $transaction->goal_id ?? $data['goal_id'] ?? null;

        $transaction->type = $this->resolveTypeByCategory($categoryId, $goalId);

        if ($categoryId) {
            $budget = Budget::findOne(['category_id' => $categoryId, 'user_id' => $transaction->user_id]);
            if ($budget) {
                if ($transaction->isTypeExpense()) {
                    $budget->amount -= $transaction->amount;
                } elseif ($transaction->isTypeIncome()) {
                    $budget->amount += $transaction->amount;
                }
                $budget->updated_at = date('Y-m-d H:i:s');
                $budget->save(false);
            }
        }

        if ($goalId) {
            $goal = Goal::findOne($goalId);
            if ($goal && $transaction->isTypeGoal()) {
                $goal->current_amount += $transaction->amount;
                if ($goal->current_amount >= $goal->target_amount) {
                    $goal->status = Goal::STATUS_COMPLETED;
                }
                $goal->updated_at = date('Y-m-d H:i:s');
                $goal->save(false);
            }
        }
    }
}
