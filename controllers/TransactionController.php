<?php

namespace app\controllers;

use app\models\Transaction;
use app\services\CurrencyService;
use app\services\TransactionService;
use Exception;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use app\models\Goal;
use yii\helpers\ArrayHelper;

class TransactionController extends BaseController
{
    private TransactionService $service;
    private CurrencyService $currencyService;

    public function __construct($id, $module, TransactionService $service, CurrencyService $currencyService, $config = [])
    {
        $this->service = $service;
        $this->currencyService = $currencyService;
        parent::__construct($id, $module, $config);
    }

    /**
     * @throws Exception
     */
    public function actionIndex(): string
    {
        $user = Yii::$app->user->identity;
        $userId = $user->id;
        $userCurrency = $user->currency ?? 'BYN';

        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');

        $previousTransactions = Transaction::find()
            ->where(['user_id' => $userId])
            ->andWhere(['<', 'date', $firstDayOfMonth])
            ->all();

        $previousBalance = 0;
        foreach ($previousTransactions as $t) {
            $amount = $t->amount;
            if ($t->currency !== $userCurrency) {
                $amount = $this->currencyService->fromBase(
                    $this->currencyService->toBase($amount, $t->currency),
                    $userCurrency
                );
            }
            $previousBalance += $t->isTypeIncome() ? $amount : -$amount;
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Transaction::find()
                ->where(['user_id' => $userId])
                ->andWhere(['between', 'date', $firstDayOfMonth, $lastDayOfMonth])
                ->orderBy(['date' => SORT_DESC]),
            'pagination' => ['pageSize' => 25],
        ]);

        $goals = ArrayHelper::map(
            Goal::find()->where(['user_id' => $userId])->all(),
            'id',
            'name'
        );

        $transactions = Transaction::find()
            ->where(['user_id' => $userId])
            ->andWhere(['between', 'date', $firstDayOfMonth, $lastDayOfMonth])
            ->all();

        $income = 0;
        $expense = 0;

        foreach ($transactions as $t) {
            $amount = $t->amount;
            if ($t->currency !== $userCurrency) {
                $amount = $this->currencyService->fromBase(
                    $this->currencyService->toBase($amount, $t->currency),
                    $userCurrency
                );
            }

            if ($t->isTypeIncome()) {
                $income += $amount;
            } else {
                $expense += $amount;
            }
        }

        $summary = [
            'previousBalance' => number_format($previousBalance, 2, '.', ''),
            'income' => number_format($income, 2, '.', ''),
            'expense' => number_format($expense, 2, '.', ''),
            'balance' => number_format($previousBalance + $income - $expense, 2, '.', ''),
            'currency' => $userCurrency,
        ];

        foreach ($dataProvider->models as $transaction) {
            $transactionCurrency = $transaction->currency ?? 'BYN';
            $displayAmount = $transaction->amount;

            if ($transactionCurrency !== $userCurrency) {
                $displayAmount = $this->currencyService->fromBase(
                    $this->currencyService->toBase($transaction->amount, $transactionCurrency),
                    $userCurrency
                );
            }
            $transaction->display_amount = number_format($displayAmount, 2, '.', '');
            $transaction->display_currency = $userCurrency;
        }

        return $this->render('index', [
            'user' => $user,
            'dataProvider' => $dataProvider,
            'summary' => $summary,
            'goals' => $goals,
        ]);
    }

    public function actionCreate(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $data = Yii::$app->request->post();
            $user = Yii::$app->user->identity;
            $userCurrency = $user->currency;
            $originalAmount = isset($data['amount']) ? (float)$data['amount'] : 0;

            $data['amount'] = $originalAmount;
            $data['currency'] = $userCurrency;

            $transaction = $this->service->create($data, $user->id);

            $displayAmount = $originalAmount;
            if ($transaction->currency !== $userCurrency) {
                $rate = $this->currencyService->getRate($transaction->currency, $userCurrency);
                $displayAmount *= $rate;
            }

            $transactionArray = $transaction->toArray();
            $transactionArray['display_amount'] = number_format($displayAmount, 2, '.', '');
            $transactionArray['currency'] = $userCurrency;

            return ['success' => true, 'transaction' => $transactionArray];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionUpdate(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $data = Yii::$app->request->post();
            $user = Yii::$app->user->identity;
            $userCurrency = $user->currency;
            $originalAmount = isset($data['amount']) ? (float)$data['amount'] : 0;

            $data['amount'] = $originalAmount;
            $data['currency'] = $userCurrency;

            $transaction = $this->service->update($id, $data);

            $displayAmount = $originalAmount;
            if ($transaction->currency !== $userCurrency) {
                $rate = $this->currencyService->getRate($transaction->currency, $userCurrency);
                $displayAmount *= $rate;
            }

            $transactionArray = $transaction->toArray();
            $transactionArray['display_amount'] = number_format($displayAmount, 2, '.', '');
            $transactionArray['currency'] = $userCurrency;

            return ['success' => true, 'transaction' => $transactionArray];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionDelete(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $this->service->delete($id);
            return ['success' => true];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @throws Exception
     */
    public function actionView(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $transaction = Transaction::findOne($id);
        if (!$transaction || $transaction->user_id !== Yii::$app->user->id) {
            return ['success' => false, 'message' => 'Транзакция не найдена'];
        }

        $userCurrency = Yii::$app->user->identity->currency ?? 'BYN';
        $transactionCurrency = $transaction->currency ?? 'BYN';
        $displayAmount = $transaction->amount;

        if ($transactionCurrency !== $userCurrency) {
            $displayAmount = $this->currencyService->fromBase(
                $this->currencyService->toBase($transaction->amount, $transactionCurrency),
                $userCurrency
            );
        }

        $transactionArray = $transaction->toArray();
        $transactionArray['display_amount'] = number_format($displayAmount, 2, '.', '');
        $transactionArray['display_currency'] = $userCurrency;

        return ['success' => true, 'transaction' => $transactionArray];
    }

    public function actionSummary(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->id;

        $summary = $this->service->getSummary($userId);

        return [
            'success' => true,
            'summary' => [
                'previousBalance' => number_format($summary['previousBalance'] ?? 0, 2, '.', ''),
                'income' => number_format($summary['income'] ?? 0, 2, '.', ''),
                'expense' => number_format($summary['expense'] ?? 0, 2, '.', ''),
                'balance' => number_format($summary['balance'] ?? 0, 2, '.', ''),
                'currency' => $summary['currency'] ?? 'BYN',
            ],
        ];
    }
}