<?php

namespace app\controllers;

use app\models\Category;
use app\services\CategoryService;
use Yii;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\Response;

class CategoryController extends Controller
{
    private CategoryService $service;

    public function __construct($id, $module, CategoryService $service, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->service = $service;
    }

    public function actionIndex(): string
    {
        $userId = Yii::$app->user->id;
        $categories = $this->service->getAllByUser($userId);

        return $this->render('index', compact('categories'));
    }

    public function actionGoals(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->id;

        try {
            $goals = $this->service->getByType($userId, 'goal');
            return $this->asJson($goals);
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['errors' => $e->getMessage()]);
        }
    }

    public function actionCreate(): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->id;
        $data = Yii::$app->request->post('Category', []);

        try {
            $category = $this->service->create($userId, $data);
            return $this->asJson($category->toArray());
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['errors' => $e->getMessage()]);
        }
    }

    public function actionUpdate(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->id;
        $data = Yii::$app->request->post('Category', []);

        try {
            $category = $this->service->update($id, $userId, $data);
            return $this->asJson($category->toArray());
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['errors' => $e->getMessage()]);
        }
    }

    public function actionDelete(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->id;

        try {
            $this->service->delete($id, $userId);
            return $this->asJson(['success' => true]);
        } catch (Exception $e) {
            Yii::$app->response->statusCode = 400;
            return $this->asJson(['errors' => $e->getMessage()]);
        }
    }

    public function actionType(int $id): Response
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->id;

        $category = Category::findOne(['id' => $id, 'user_id' => $userId]);
        if (!$category) {
            return $this->asJson(['error' => 'Категория не найдена']);
        }

        return $this->asJson(['type' => $category->type]);
    }
}