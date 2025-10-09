<?php

namespace app\controllers;

use app\models\Category;
use app\models\Transaction;
use app\services\TransactionService;
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

    public function __construct($id, $module, TransactionService $service = null, $config = [])
    {
        $this->service = $service ?? new TransactionService();
        parent::__construct($id, $module, $config);
    }

    /**
     * Список транзакций и сводка.
     */
    public function actionIndex(): string
    {
        $userId = Yii::$app->user->id;

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

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'summary' => $this->service->getSummary($userId),
            'goals' => $goals,
        ]);
    }

    /**
     * Создание транзакции (AJAX).
     */
    public function actionCreate(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $transaction = $this->service->create(Yii::$app->request->post(), Yii::$app->user->id);
            return ['success' => true, 'transaction' => $transaction->toArray()];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Обновление транзакции (AJAX).
     */
    public function actionUpdate(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $transaction = $this->service->update($id, Yii::$app->request->post());
            return ['success' => true, 'transaction' => $transaction->toArray()];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Удаление транзакции (AJAX).
     */
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
     * Просмотр одной транзакции (AJAX).
     */
    public function actionView(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $transaction = Transaction::findOne($id);

        return $transaction
            ? ['success' => true, 'transaction' => $transaction->toArray()]
            : ['success' => false, 'message' => 'Транзакция не найдена'];
    }
}
