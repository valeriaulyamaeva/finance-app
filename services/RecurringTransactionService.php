<?php

namespace app\services;

use app\models\RecurringTransaction;
use app\models\Transaction;
use DateMalformedStringException;
use InvalidArgumentException;
use Yii;
use DateTime;
use DateInterval;
use yii\db\Exception;

class RecurringTransactionService
{
    private CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * @throws Exception
     */
    public function saveRecurringTransaction(RecurringTransaction $model, array $data): bool
    {
        $model->load($data, '');
        return $model->save();
    }

    /**
     * @param RecurringTransaction $recurring
     * @return Transaction|null
     * @throws Exception
     * @throws DateMalformedStringException
     * @throws \Exception
     */
    public function createTransactionFromRecurring(RecurringTransaction $recurring): ?Transaction
    {
        if (!$recurring->active) {
            return null;
        }

        $userCurrency = $recurring->user->currency ?? 'BYN';
        $amount = $recurring->amount;

        if ($recurring->currency !== $userCurrency) {
            $rate = $this->currencyService->getRate($recurring->currency, $userCurrency);
            $amount = round($amount * $rate, 2);
        }

        $transaction = new Transaction();
        $transaction->user_id = $recurring->user_id;
        $transaction->amount = $amount;
        $transaction->currency = $userCurrency;
        $transaction->category_id = $recurring->category_id;
        $transaction->budget_id = $recurring->budget_id;
        $transaction->goal_id = $recurring->goal_id;
        $transaction->description = $recurring->description;
        $transaction->recurring_id = $recurring->id;
        $transaction->date = $recurring->next_date;

        if (!$transaction->save()) {
            Yii::error('Failed to create transaction from recurring: ' . json_encode($transaction->errors));
            return null;
        }

        $recurring->next_date = $this->getNextDate($recurring->next_date, $recurring->frequency);
        $recurring->save(false);

        return $transaction;
    }


    /**
     * @param string $currentDate
     * @param string $frequency
     * @return string
     * @throws DateMalformedStringException
     */
    public function getNextDate(string $currentDate, string $frequency): string
    {
        $date = new DateTime($currentDate);

        match ($frequency) {
            RecurringTransaction::FREQUENCY_DAILY => $date->add(new DateInterval('P1D')),
            RecurringTransaction::FREQUENCY_WEEKLY => $date->add(new DateInterval('P7D')),
            RecurringTransaction::FREQUENCY_MONTHLY => $date->add(new DateInterval('P1M')),
            default => throw new InvalidArgumentException('Unknown frequency: ' . $frequency),
        };

        return $date->format('Y-m-d');
    }

    /**
     * @return RecurringTransaction[]
     */
    public function getDueRecurringTransactions(): array
    {
        $today = date('Y-m-d');
        return RecurringTransaction::find()
            ->where(['active' => 1])
            ->andWhere(['<=', 'next_date', $today])
            ->all();
    }
}
