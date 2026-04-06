<?php

declare(strict_types=1);

namespace app\models\queries;

use app\models\Goal;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * @see Goal
 */
final class GoalQuery extends ActiveQuery
{
    public function forUser(int $userId): self
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    public function active(): self
    {
        return $this->andWhere(['status' => Goal::STATUS_ACTIVE]);
    }

    public function overdue(): self
    {
        return $this->active()->andWhere(['<', 'deadline', new Expression('CURDATE()')]);
    }
}