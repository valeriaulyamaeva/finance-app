<?php

namespace app\controllers;

use app\models\RecurringTransaction;
use app\services\CurrencyService;
use app\services\RecurringTransactionService;
use DateMalformedStringException;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\web\Response;
use yii\web\NotFoundHttpException;

class RecurringTransactionController extends BaseController
{
    private RecurringTransactionService $service;
    private CurrencyService $currencyService;

    public function __construct($id, $module, RecurringTransactionService $service, CurrencyService $currencyService, $config = [])
    {
        $this->service = $service;
        $this->currencyService = $currencyService;
        parent::__construct($id, $module, $config);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function actionCreate(): Response|array
    {
        $model = new RecurringTransaction();
        $model->user_id = Yii::$app->user->id;

        Yii::$app->response->format = Response::FORMAT_JSON;

        if ($this->request->isPost) {
            $data = array_merge(
                $this->request->post('RecurringTransaction', []),
                $this->request->post('Transaction', [])
            );

            // Валюта пользователя
            $currency = Yii::$app->user->identity->currency ?? 'BYN';
            $data['currency'] = $currency;

            // НЕ конвертируем сумму
            $originalAmount = $data['amount'] ?? 0;

            $model->load($data, '');
            if ($model->save()) {
                return [
                    'success' => true,
                    'id' => $model->id,
                    'amount' => $model->amount, // сохраняем как ввел пользователь
                    'display_amount' => number_format($originalAmount, 2, '.', ''),
                    'category_name' => $model->category->name ?? null,
                    'description' => $model->description,
                    'type' => $model->frequency,
                    'date' => $model->next_date,
                ];
            }
        }

        return ['success' => false, 'errors' => $model->errors];
    }

    /**
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            $data = $this->request->post('RecurringTransaction', []);
            $currency = Yii::$app->user->identity->currency;
            $data['currency'] = $currency;


            if ($this->service->saveRecurringTransaction($model, $data)) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete(int $id): Response
    {
        try {
            $this->findModel($id)->delete();
        } catch (Throwable) {
        }

        return $this->redirect(['index']);
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): RecurringTransaction
    {
        $model = RecurringTransaction::findOne($id);
        if (!$model || $model->user_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        return $model;
    }

    /**
     * @throws DateMalformedStringException
     * @throws Exception
     */
    public function actionGenerate(): Response
    {
        $transactions = $this->service->getDueRecurringTransactions();
        foreach ($transactions as $recurring) {
            $this->service->createTransactionFromRecurring($recurring);
        }

        return $this->asJson(['success' => true, 'count' => count($transactions)]);
    }
}