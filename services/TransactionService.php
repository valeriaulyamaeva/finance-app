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
        // защитное приведение
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

        if (isset($data['goal_id']) && $data['goal_id'] !== '') {
            $transaction->goal_id = (int)$data['goal_id'];
        } elseif (isset($data['Transaction']['goal_id']) && $data['Transaction']['goal_id'] !== '') {
            $transaction->goal_id = (int)$data['Transaction']['goal_id'];
        }

        if (isset($data['category_id']) && $data['category_id'] !== '') {
            $transaction->category_id = (int)$data['category_id'];
        } elseif (isset($data['Transaction']['category_id']) && $data['Transaction']['category_id'] !== '') {
            $transaction->category_id = (int)$data['Transaction']['category_id'];
        }

        $categoryId = $transaction->category_id !== null && $transaction->category_id !== '' ? (int)$transaction->category_id : null;
        $goalId = $transaction->goal_id !== null && $transaction->goal_id !== '' ? (int)$transaction->goal_id : null;

        Yii::debug("Category ID: $categoryId, Goal ID: $goalId", __METHOD__);

        $transaction->type = $this->resolveTypeByCategory($categoryId, $goalId);

        Yii::debug("Transaction Type: $transaction->type, Amount: $transaction->amount", __METHOD__);
        if ($categoryId) {
            $budget = Budget::findOne(['category_id' => $categoryId, 'user_id' => $transaction->user_id]);
            if ($budget) {
                $oldBudgetAmount = $budget->amount;

                if ($transaction->isTypeExpense()) {
                    $budget->spent += $transaction->amount;
                } elseif ($transaction->isTypeIncome()) {
                    $budget->spent = max(0, $budget->spent - $transaction->amount);
                }


                $budget->updated_at = date('Y-m-d H:i:s');
                $budget->save(false);

                Yii::debug("Budget updated: ID={$budget->id}, Old={$oldBudgetAmount}, New={$budget->amount}", __METHOD__);
            } else {
                Yii::debug("No budget found for Category={$categoryId}, User={$transaction->user_id}", __METHOD__);
            }
        }

        if ($goalId) {
            $goal = Goal::findOne($goalId);

            if ($goal) {
                $oldGoalAmount = $goal->current_amount;
                $goal->current_amount += $transaction->amount;

                if ($goal->current_amount >= $goal->target_amount) {
                    $goal->status = Goal::STATUS_COMPLETED;
                } elseif ($goal->current_amount < $oldGoalAmount) {
                    $goal->status = Goal::STATUS_ACTIVE;
                }

                $goal->updated_at = date('Y-m-d H:i:s');
                $goal->save(false);

                Yii::debug("Goal updated: ID={$goal->id}, Old={$oldGoalAmount}, New={$goal->current_amount}, Status={$goal->status}", __METHOD__);
            } else {
                Yii::debug("Goal not found: ID={$goalId}", __METHOD__);
            }
        }
    }
}
