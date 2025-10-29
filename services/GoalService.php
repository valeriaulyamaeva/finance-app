<?php

namespace app\services;

use app\models\Goal;
use Yii;
use yii\db\Exception;
use app\models\Notification;

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
        $goal->current_amount = $data['current_amount'] ?? 0;
        $goal->deadline = $data['deadline'] ?? date('Y-m-d');
        $goal->status = $data['status'] ?? Goal::STATUS_ACTIVE;

        $goal->currency = $data['currency'] ?? Yii::$app->user->identity->currency;

        if (!$goal->save()) {
            Yii::error('Goal validation errors: ' . json_encode($goal->errors, JSON_UNESCAPED_UNICODE), __METHOD__);
            throw new Exception('Failed to create goal: ' . json_encode($goal->errors, JSON_UNESCAPED_UNICODE));
        }

        return $goal;
    }

    /**
     * @throws Exception
     */
    public function update(Goal $goal, array $data): Goal
    {
        $goal->name = $data['name'] ?? $goal->name;
        $goal->target_amount = $data['target_amount'] ?? $goal->target_amount;
        $goal->current_amount = $data['current_amount'] ?? $goal->current_amount;
        $goal->deadline = $data['deadline'] ?? $goal->deadline;
        $goal->status = $data['status'] ?? $goal->status;

        if (isset($data['currency'])) {
            $goal->currency = $data['currency'];
        }

        if (!$goal->save()) {
            Yii::error('Goal update errors: ' . json_encode($goal->errors, JSON_UNESCAPED_UNICODE), __METHOD__);
            throw new Exception('Failed to update goal: ' . json_encode($goal->errors, JSON_UNESCAPED_UNICODE));
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

        $wasCompleted = $goal->isStatusCompleted();

        if ($goal->current_amount >= $goal->target_amount && !$wasCompleted) {
            $goal->setStatusToCompleted();

            Notification::createForUser(
                $goal->user_id,
                "Цель '$goal->name' достигнута! Сумма: " . number_format($goal->current_amount, 2) . " $goal->currency",
                Notification::TYPE_GOAL_REACHED
            );
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
