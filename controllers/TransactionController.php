<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Category;
use app\models\Transaction;
use app\models\Goal;
use app\services\CurrencyService;
use app\services\TransactionService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use Throwable;

final class TransactionController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly TransactionService $service,
        private readonly CurrencyService $currencyService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): string
    {
        $user = Yii::$app->user->identity;
        $userId = $user->id;

        $request = Yii::$app->request;
        $startDate = $request->get('start', date('Y-m-01'));
        $endDate = $request->get('end', date('Y-m-t'));
        $categoryId = $request->get('category_id');

        $query = Transaction::find()
            ->forUser($userId)
            ->andWhere(['between', 'date', $startDate, $endDate])
            ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC]);

        if ($categoryId) {
            $query->andWhere(['category_id' => (int)$categoryId]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
        ]);

        $goals = ArrayHelper::map(
            Goal::find()->forUser($userId)->active()->all(),
            'id',
            'name'
        );

        $categories = Category::find()
            ->where(['user_id' => $userId])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        $summary = $this->service->getMonthlySummary($userId, $startDate, $endDate);

        return $this->render('index', [
            'user' => $user,
            'dataProvider' => $dataProvider,
            'summary' => $summary,
            'goals' => $goals,
            'categories' => $categories,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'categoryId' => $categoryId,
        ]);
    }

    public function actionCreate(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $data = Yii::$app->request->post();
            $transaction = $this->service->create($data, (int)Yii::$app->user->id);

            return [
                'success' => true,
                'transaction' => $transaction->toArray(),
                'summary' => $this->service->getMonthlySummary((int)Yii::$app->user->id)
            ];
        } catch (Throwable $e) {
            Yii::error($e->getMessage());
            return ['success' => false, 'message' => 'Ошибка при создании: ' . $e->getMessage()];
        }
    }

    public function actionUpdate(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $data = Yii::$app->request->post();
            $transaction = $this->service->update($id, $data);

            return [
                'success' => true,
                'transaction' => $transaction->toArray(),
                'summary' => $this->service->getMonthlySummary((int)Yii::$app->user->id)
            ];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionView(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $transaction = Transaction::find()
            ->where(['id' => $id, 'user_id' => Yii::$app->user->id])
            ->one();

        if (!$transaction) {
            return ['success' => false, 'message' => 'Запись не найдена'];
        }

        return ['success' => true, 'transaction' => $transaction->toArray()];
    }

    public function actionDelete(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $this->service->delete($id);
            return [
                'success' => true,
                'summary' => $this->service->getMonthlySummary((int)Yii::$app->user->id)
            ];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}