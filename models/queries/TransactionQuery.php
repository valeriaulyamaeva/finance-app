<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\Transaction;
use yii\db\ActiveQuery;

final class TransactionQuery extends ActiveQuery
{
    public function forUser(int $userId): self
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    public function incomes(): self
    {
        return $this->andWhere(['type' => Transaction::TYPE_INCOME]);
    }

    public function expenses(): self
    {
        return $this->andWhere(['type' => [Transaction::TYPE_EXPENSE, Transaction::TYPE_GOAL]]);
    }

    public function forPeriod(string $start, string $end): self
    {
        return $this->andWhere(['between', 'date', $start, $end]);
    }

    public function latest(): self
    {
        return $this->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC]);
    }

    public function forBudget(int $budgetId): self
    {
        return $this->andWhere(['budget_id' => $budgetId]);
    }
}