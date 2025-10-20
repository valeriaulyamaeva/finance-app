<?php

namespace app\controllers;

use app\models\Transaction;
use app\services\CurrencyService;
use app\services\TransactionService;
use Exception;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
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
    // TransactionController.php
    public function actionIndex(): string
    {
        $user = Yii::$app->user->identity;
        $userId = $user->id;
        $userCurrency = $user->currency ?? 'BYN';

        $dataProvider = new ActiveDataProvider([
            'query' => Transaction::find()
                ->where(['user_id' => $userId])
                ->orderBy(['date' => SORT_DESC]),
            'pagination' => ['pageSize' => 25],
        ]);

        $goals = ArrayHelper::map(
            Goal::find()->where(['user_id' => $userId])->all(),
            'id',
            'name'
        );

        $summary = $this->service->getSummary($userId);

        // Только форматируем суммы для отображения, не конвертируем повторно
        foreach (['income', 'expense', 'balance'] as $key) {
            if (isset($summary[$key])) {
                $summary[$key] = number_format($summary[$key], 2, '.', '');
            }
        }

        // Для транзакций конвертация отдельная, оставляем как было
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
}