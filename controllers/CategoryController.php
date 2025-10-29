<?php

namespace app\controllers;

use app\models\Category;
use app\services\CategoryService;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CategoryController extends BaseController
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

        $goals = $this->service->getByType($userId, 'goal');
        return $this->asJson($goals);
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

    /**
     * @throws NotFoundHttpException
     */
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

    /**
     * @throws Throwable
     * @throws NotFoundHttpException
     */
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

    public function actionType(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $category = Category::findOne($id);
        if (!$category) {
            return ['success' => false, 'message' => 'Категория не найдена'];
        }

        return ['success' => true, 'type' => $category->type];
    }
}