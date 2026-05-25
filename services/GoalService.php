<?php

declare(strict_types=1);

namespace app\services;

use app\models\Goal;
use app\models\forms\GoalForm;
use app\models\Notification;
use DomainException;

final class GoalService
{
    public function create(int $userId, GoalForm $form, string $defaultCurrency): Goal
    {
        $goal = new Goal();
        $goal->user_id = $userId;
        $this->mapFormToModel($form, $goal);

        $goal->currency = $form->currency ?: $defaultCurrency;
        $goal->current_amount = (float)($form->current_amount ?? 0);

        if (!$goal->save()) {
            throw new DomainException('Ошибка при создании цели: ' . json_encode($goal->errors));
        }

        return $goal;
    }

    public function update(int $id, int $userId, GoalForm $form): Goal
    {
        $goal = $this->findById($id, $userId);
        $this->mapFormToModel($form, $goal);

        $goal->updateStatus();

        if (!$goal->save()) {
            throw new DomainException('Ошибка при обновлении цели.');
        }

        return $goal;
    }

    public function addProgress(int $goalId, int $userId, float $amount): Goal
    {
        $goal = $this->findById($goalId, $userId);
        $goal->current_amount += $amount;

        $wasCompleted = $goal->isCompleted();
        $goal->updateStatus();

        if (!$goal->save()) {
            throw new DomainException('Не удалось обновить прогресс цели.');
        }

        if ($goal->isCompleted() && !$wasCompleted) {
            Notification::createForUser(
                $goal->user_id,
                "Цель «{$goal->name}» достигнута! Накоплено: {$goal->current_amount} {$goal->currency}",
                Notification::TYPE_GOAL_REACHED
            );
        }

        return $goal;
    }

    public function delete(int $id, int $userId): void
    {
        $goal = $this->findById($id, $userId);
        if (!$goal->delete()) {
            throw new DomainException('Ошибка при удалении цели.');
        }
    }

    public function findById(int $id, int $userId): Goal
    {
        $goal = Goal::find()->forUser($userId)->andWhere(['id' => $id])->one();

        if (!$goal) {
            throw new DomainException('Цель не найдена или доступ запрещен.');
        }

        return $goal;
    }

    private function mapFormToModel(GoalForm $form, Goal $goal): void
    {
        $goal->name = $form->name;
        $goal->target_amount = $form->target_amount;
        $goal->deadline = $form->deadline;
        $goal->category_id = $form->category_id;
        if ($form->currency) {
            $goal->currency = $form->currency;
        }
    }
}