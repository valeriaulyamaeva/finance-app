<?php

namespace app\services;

use app\models\Category;
use app\models\Transaction;
use Throwable;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

class TransactionService
{
    /**
     * Создание транзакции.
     *
     * @throws Exception
     */
    public function create(array $data, int $userId): Transaction
    {
        $transaction = new Transaction([
            'user_id' => $userId,
        ]);
        $transaction->load($data);
        $transaction->type = $this->resolveTypeByCategory($transaction->category_id);

        if (!$transaction->validate() || !$transaction->save()) {
            throw new Exception('Ошибка при создании транзакции: ' . json_encode($transaction->errors, JSON_UNESCAPED_UNICODE));
        }

        return $transaction;
    }

    /**
     * Обновление транзакции.
     *
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function update(int $id, array $data): Transaction
    {
        $transaction = Transaction::findOne($id);
        if (!$transaction) {
            throw new NotFoundHttpException('Транзакция не найдена');
        }

        $transaction->load($data);
        $transaction->type = $this->resolveTypeByCategory($transaction->category_id);

        if (!$transaction->validate() || !$transaction->save()) {
            throw new Exception('Ошибка при обновлении транзакции: ' . json_encode($transaction->errors, JSON_UNESCAPED_UNICODE));
        }

        return $transaction;
    }

    /**
     * Определение типа по категории.
     */
    private function resolveTypeByCategory(?int $categoryId): string
    {
        $category = Category::findOne($categoryId);
        return match ($category->type ?? null) {
            'income' => Transaction::TYPE_INCOME,
            'goal' => Transaction::TYPE_GOAL,
            default => Transaction::TYPE_EXPENSE,
        };
    }

    /**
     * Удаление транзакции.
     */
    public function delete(int $id): void
    {
        if ($transaction = Transaction::findOne($id)) {
            try {
                $transaction->delete();
            } catch (Throwable) {
            }
        }
    }

    /**
     * Получение сводки (доход, расход, баланс).
     */
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
}
