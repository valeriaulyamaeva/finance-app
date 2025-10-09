<?php

namespace app\services;

use app\models\Goal;
use yii\db\Exception;

class GoalService
{
    /**
     * @param array $data
     * @param int $userId
     * @return Goal
     * @throws Exception
     */
    public function create(array $data, int $userId): Goal
    {
        $goal = new Goal();
        $goal->user_id = $userId;
        $goal->name = $data['name'] ?? '';
        $goal->target_amount = $data['target_amount'] ?? 0;
        $goal->deadline = $data['deadline'] ?? date('Y-m-d');
        $goal->current_amount = $data['current_amount'] ?? 0;
        $goal->status = Goal::STATUS_ACTIVE;

        if (!$goal->save()) {
            throw new Exception('Failed to create goal: ' . json_encode($goal->errors));
        }

        return $goal;
    }

    /**
     * @param Goal $goal
     * @param array $data
     * @return Goal
     * @throws Exception
     */
    public function update(Goal $goal, array $data): Goal
    {
        $goal->name = $data['name'] ?? $goal->name;
        $goal->target_amount = $data['target_amount'] ?? $goal->target_amount;
        $goal->deadline = $data['deadline'] ?? $goal->deadline;
        $goal->current_amount = $data['current_amount'] ?? $goal->current_amount;
        $goal->status = $data['status'] ?? $goal->status;

        if (!$goal->save()) {
            throw new Exception('Failed to update goal: ' . json_encode($goal->errors));
        }

        return $goal;
    }

    /**
     * @param Goal $goal
     * @param float $amount
     * @return Goal
     * @throws Exception
     */
    public function addProgress(Goal $goal, float $amount): Goal
    {
        $goal->current_amount += $amount;

        if ($goal->current_amount >= $goal->target_amount) {
            $goal->setStatusToCompleted();
        }

        if (!$goal->save()) {
            throw new Exception('Failed to add progress: ' . json_encode($goal->errors));
        }

        return $goal;
    }

    /**
     * @param Goal $goal
     * @return float
     */
    public function getProgressPercent(Goal $goal): float
    {
        if ($goal->target_amount == 0) {
            return 0;
        }

        return min(100, ($goal->current_amount / $goal->target_amount) * 100);
    }
}
