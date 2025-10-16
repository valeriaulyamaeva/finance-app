<?php

namespace app\commands;

use app\models\Notification;
use app\models\Transaction;
use app\models\User;
use app\services\RecurringTransactionService;
use Exception;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class RecurringTransactionController extends Controller
{
    private RecurringTransactionService $service;

    public function __construct($id, $module, RecurringTransactionService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

    public function actionGenerate(): int
    {
        try {
            $transactions = $this->service->getDueRecurringTransactions();
            $count = count($transactions);
            $this->stdout("Найдено $count повторяющихся транзакций для обработки.\n");

            foreach ($transactions as $recurring) {
                $transaction = $this->service->createTransactionFromRecurring($recurring);
                if ($transaction) {
                    $this->stdout("Создана транзакция для повторяющейся транзакции ID $recurring->id\n");
                    $this->sendNotification($recurring->user, $transaction);
                } else {
                    $this->stderr("Ошибка при создании транзакции для ID $recurring->id: " . json_encode($recurring->errors) . "\n");
                }
            }
            $this->stdout("Обработано $count повторяющихся транзакций.\n");
            return ExitCode::OK;
        } catch (Exception $e) {
            $this->stderr("Ошибка: {$e->getMessage()}\n");
            Yii::error("Ошибка в actionGenerate: {$e->getMessage()}", __METHOD__);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * @param User $user
     * @param Transaction $transaction
     * @throws \yii\db\Exception
     */
    private function sendNotification(User $user, Transaction $transaction): void
    {
        $notification = new Notification();
        $notification->user_id = $user->id;
        $notification->message = "Создана повторяющаяся транзакция: $transaction->amount $user->currency ($transaction->description)";
        $notification->type = Notification::TYPE_REMINDER;
        $notification->read_status = 0;
        if (!$notification->save()) {
            Yii::error("Ошибка сохранения уведомления: " . json_encode($notification->errors), __METHOD__);
        } else {
            Yii::info("Уведомление создано для пользователя $user->id: $notification->message", __METHOD__);
        }
    }
}