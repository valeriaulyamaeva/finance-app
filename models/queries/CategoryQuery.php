<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\Category;
use yii\db\ActiveQuery;

/**
 * @see Category
 */
final class CategoryQuery extends ActiveQuery
{
    public function forUser(int $userId): self
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    public function income(): self
    {
        return $this->andWhere(['type' => Category::TYPE_INCOME]);
    }

    public function expense(): self
    {
        return $this->andWhere(['type' => Category::TYPE_EXPENSE]);
    }
}