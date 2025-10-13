<?php

namespace app\controllers;

use app\models\Budget;
use app\services\BudgetService;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\Response;

class BudgetController extends Controller
{
    private BudgetService $service;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = new BudgetService();
    }

    /**
     * Страница бюджета.
     */
    public function actionIndex(): string
    {
        $userId = Yii::$app->user->id;
        $summary = $this->service->getUserSummary($userId);

        $dataProvider = new ActiveDataProvider([
            'query' => Budget::find()->where(['user_id' => $userId])->with('category'),
            'pagination' => ['pageSize' => 10],
            'sort' => ['defaultOrder' => ['start_date' => SORT_DESC]],
        ]);

        return $this->render('index', [
            'summary' => $summary,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $budget = Budget::findOne($id);
        if (!$budget) {
            return ['success' => false, 'message' => 'Бюджет не найден'];
        }

        return ['success' => true, 'budget' => $budget->toArray()];
    }

    public function actionCreate(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $budget = $this->service->create(Yii::$app->request->post(), Yii::$app->user->id);
            return ['success' => true, 'budget' => $budget->toArray()];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionUpdate(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $budget = $this->service->update($id, Yii::$app->request->post());
            return ['success' => true, 'budget' => $budget->toArray()];
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
}
