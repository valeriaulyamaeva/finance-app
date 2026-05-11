<?php

declare(strict_types=1);

namespace app\services;

use app\models\forms\RecurringForm;
use app\models\RecurringTransaction;
use DateTime;
use DateInterval;
use Exception;
use Yii;
use yii\web\NotFoundHttpException;

readonly class RecurringTransactionService
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    public function save(array $data, int $userId, ?int $id = null): RecurringTransaction
    {
        $form = new RecurringForm();
        if (!$form->load($data, '') || !$form->validate()) {
            throw new Exception('Ошибка валидации: ' . implode(', ', $form->getErrorSummary(true)));
        }

        $isNew = !$id;
        $model = $id ? RecurringTransaction::findOne($id) : new RecurringTransaction();
        if ($id && !$model) {
            throw new NotFoundHttpException('Шаблон не найден');
        }

        $model->user_id = $userId;
        $model->attributes = $form->attributes;

        if (!$model->save()) {
            throw new Exception('Не удалось сохранить шаблон повтора');
        }

        // If new template's next_date is today or in the past, execute immediately
        // so the first transaction appears in the list right away (no need to wait for cron).
        if ($isNew && $model->next_date <= date('Y-m-d')) {
            try {
                $this->executeTask($model);
            } catch (Exception $e) {
                Yii::error("Не удалось сразу выполнить новый шаблон {$model->id}: " . $e->getMessage());
            }
        }

        return $model;
    }

    public function runScheduledTasks(): int
    {
        $tasks = RecurringTransaction::find()->active()->due()->all();
        $processedCount = 0;

        foreach ($tasks as $task) {
            try {
                if ($this->executeTask($task)) {
                    $processedCount++;
                }
            } catch (Exception $e) {
                Yii::error("Ошибка выполнения задачи $task->id: " . $e->getMessage());
            }
        }

        return $processedCount;
    }

    private function executeTask(RecurringTransaction $task): bool
    {
        return Yii::$app->db->transaction(function () use ($task) {
            $this->transactionService->create([
                'amount' => $task->amount,
                'currency' => $task->currency,
                'date' => $task->next_date,
                'category_id' => $task->category_id,
                'goal_id' => $task->goal_id,
                'description' => $task->description ? "Авто: $task->description" : "Повторяющийся платеж",
                'recurring_id' => $task->id,
            ], $task->user_id);

            $task->next_date = $this->calculateNextDate($task->next_date, $task->frequency);

            if (!$task->save(false)) {
                throw new Exception("Не удалось обновить дату для шаблона $task->id");
            }

            return true;
        });
    }

    private function calculateNextDate(string $currentDate, string $frequency): string
    {
        $date = new DateTime($currentDate);
        $interval = match ($frequency) {
            RecurringTransaction::FREQUENCY_DAILY => 'P1D',
            RecurringTransaction::FREQUENCY_WEEKLY => 'P7D',
            RecurringTransaction::FREQUENCY_MONTHLY => 'P1M',
            default => throw new Exception("Неизвестная частота: $frequency"),
        };

        $date->add(new DateInterval($interval));
        return $date->format('Y-m-d');
    }

    public function delete(int $id): void
    {
        $model = RecurringTransaction::findOne($id);
        $model?->delete();
    }
}