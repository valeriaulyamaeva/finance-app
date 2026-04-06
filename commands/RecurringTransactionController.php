<?php

declare(strict_types=1);

namespace app\commands;

use app\models\Notification;
use app\models\RecurringTransaction;
use app\models\Transaction;
use app\models\User;
use app\services\RecurringTransactionService;
use app\services\TransactionService;
use DateInterval;
use DateTime;
use Exception;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use Throwable;

final class RecurringTransactionController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly RecurringTransactionService $service,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionRun(): int
    {
        $this->stdout("--- Запуск обработки автоплатежей [" . date('Y-m-d H:i:s') . "] ---\n");

        try {
            $tasks = RecurringTransaction::find()->active()->due()->all();
            $count = count($tasks);

            if ($count === 0) {
                $this->stdout("Нет задач для обработки.\n");
                return ExitCode::OK;
            }

            foreach ($tasks as $task) {
                try {

                    if ($this->processTaskWithNotification($task)) {
                        $this->stdout("ID $task->id: Успешно обработано.\n");
                    }
                } catch (Throwable $e) {
                    $this->stderr("ID $task->id: Ошибка: {$e->getMessage()}\n");
                }
            }

            $this->stdout("Обработка завершена. Задач: $count\n");
            return ExitCode::OK;
        } catch (Exception $e) {
            $this->stderr("Критическая ошибка: {$e->getMessage()}\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    private function processTaskWithNotification(RecurringTransaction $task): bool
    {
        return Yii::$app->db->transaction(function() use ($task) {
            $transaction = Yii::$container->get(TransactionService::class)->create([
                'amount' => $task->amount,
                'currency' => $task->currency,
                'date' => $task->next_date,
                'category_id' => $task->category_id,
                'goal_id' => $task->goal_id,
                'description' => "Авто: " . ($task->description ?: "Платеж"),
                'recurring_id' => $task->id,
            ], $task->user_id);

            $task->next_date = $this->calculateNewDate($task->next_date, $task->frequency);
            $task->save(false);

            $this->sendNotification($task->user, $transaction, $task->id);

            return true;
        });
    }

    private function calculateNewDate(string $date, string $freq): string
    {
        $dt = new DateTime($date);
        $interval = match($freq) {
            RecurringTransaction::FREQUENCY_DAILY => 'P1D',
            RecurringTransaction::FREQUENCY_WEEKLY => 'P7D',
            RecurringTransaction::FREQUENCY_MONTHLY => 'P1M',
            default => 'P1M'
        };
        return $dt->add(new DateInterval($interval))->format('Y-m-d');
    }

    private function sendNotification(User $user, Transaction $transaction, int $recurringId): void
    {
        $amountFormatted = number_format($transaction->amount, 2, '.', ' ');
        $categoryName = $transaction->category->name ?? 'Без категории';
        $typeLabel = $transaction->type === Transaction::TYPE_INCOME ? 'Зачисление' : 'Списание';
        $symbol = $transaction->type === Transaction::TYPE_INCOME ? '+' : '-';

        $message = "$typeLabel по автоплатежу\n$symbol$amountFormatted $transaction->currency\nКатегория: $categoryName";

        $notification = new Notification([
            'user_id' => $user->id,
            'message' => $message,
            'type' => Notification::TYPE_REMINDER,
            'related_type' => 'recurring_transaction',
            'related_id' => $recurringId,
            'read_status' => 0,
        ]);

        $notification->save(false);
    }
}