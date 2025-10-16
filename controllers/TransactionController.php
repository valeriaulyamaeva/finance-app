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

class TransactionController extends Controller
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
        $currency = $user->currency;

        $dataProvider = new ActiveDataProvider([
            'query' => Transaction::find()
                ->where(['user_id' => $userId])
                ->orderBy(['date' => SORT_DESC]),
            'pagination' => ['pageSize' => 25],
        ]);

        $goals = ArrayHelper::map(
            Goal::find()
                ->where(['user_id' => $userId])
                ->all(),
            'id',
            'name'
        );

        $summary = $this->service->getSummary($userId);

        if ($currency !== 'BYN') {
            $rate = $this->currencyService->getRate('BYN', $currency);
            foreach ($summary as $key => $value) {
                if (is_numeric($value)) {
                    $summary[$key] *= $rate;
                }
            }
            foreach ($dataProvider->models as $transaction) {
                $transaction->amount *= $rate;
            }
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
            $currency = Yii::$app->user->identity->currency;
            $originalAmount = $data['amount'] ?? 0;

            if ($currency !== 'BYN' && isset($data['amount'])) {
                $rate = $this->currencyService->getRate($currency, 'BYN');
                $data['amount'] *= $rate;
            }

            $transaction = $this->service->create($data, Yii::$app->user->id);
            $transactionArray = $transaction->toArray();
            $transactionArray['display_amount'] = number_format($originalAmount, 2, '.', '');

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
            $currency = Yii::$app->user->identity->currency;
            $originalAmount = $data['amount'] ?? 0;

            if ($currency !== 'BYN' && isset($data['amount'])) {
                $rate = $this->currencyService->getRate($currency, 'BYN');
                $data['amount'] *= $rate;
            }

            $transaction = $this->service->update($id, $data);
            $transactionArray = $transaction->toArray();
            $transactionArray['display_amount'] = number_format($originalAmount, 2, '.', '');

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

        $currency = Yii::$app->user->identity->currency;
        $displayAmount = $transaction->amount;
        if ($currency !== 'BYN') {
            $rate = $this->currencyService->getRate('BYN', $currency);
            $displayAmount *= $rate;
        }

        $transactionArray = $transaction->toArray();
        $transactionArray['display_amount'] = number_format($displayAmount, 2, '.', '');

        return ['success' => true, 'transaction' => $transactionArray];
    }
}