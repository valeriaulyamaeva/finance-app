<?php

declare(strict_types=1);

namespace app\models\queries;

use yii\db\ActiveQuery;

final class BudgetQuery extends ActiveQuery
{
    public function forUser(int $userId): self
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    public function active(): self
    {
        $today = date('Y-m-d');
        return $this->andWhere(['<=', 'start_date', $today])
            ->andWhere([
                'or',
                ['>=', 'end_date', $today],
                ['end_date' => null]
            ]);
    }

    public function forCategory(int $categoryId): self
    {
        return $this->andWhere(['category_id' => $categoryId]);
    }
}