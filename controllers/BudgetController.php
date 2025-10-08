<?php

namespace app\controllers;

use app\models\Budget;
use app\models\Category;
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
        $dataProvider = new ActiveDataProvider([
            'query' => Budget::find()->where(['user_id' => $userId])->orderBy(['created_at' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        $summary = $this->service->getUserSummary($userId);

        $categories = Category::find()
            ->where(['user_id' => $userId])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'summary' => $summary,
            'categories' => $categories,
        ]);
    }

    /**
     * Создание бюджета (AJAX JSON).
     */
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

    /**
     * Обновление бюджета (AJAX JSON).
     */
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

    /**
     * Удаление бюджета.
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
}
