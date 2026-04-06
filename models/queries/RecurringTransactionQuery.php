<?php

declare(strict_types=1);

namespace app\models\queries;

use yii\db\ActiveQuery;

final class RecurringTransactionQuery extends ActiveQuery
{

    public function active(): self
    {
        return $this->andWhere(['active' => 1]);
    }

    public function due(?string $date = null): self
    {
        return $this->andWhere(['<=', 'next_date', $date ?? date('Y-m-d')]);
    }

    public function forUser(int $userId): self
    {
        return $this->andWhere(['user_id' => $userId]);
    }
}