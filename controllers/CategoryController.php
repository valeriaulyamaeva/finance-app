<?php

declare(strict_types=1);

namespace app\controllers;

use app\services\CategoryService;
use app\models\forms\CategoryForm;
use DomainException;
use Yii;
use yii\web\Response;
use yii\filters\ContentNegotiator;

final class CategoryController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly CategoryService $service,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'only' => ['create', 'update', 'delete', 'goals', 'type'],
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        return $behaviors;
    }

    public function actionIndex(): string
    {
        return $this->render('index', [
            'categories' => $this->service->getAllByUser($this->getUserId()),
        ]);
    }

    public function actionGoals(): array
    {
        return $this->service->getMapByType($this->getUserId(), 'goal');
    }

    public function actionCreate(): array|Response
    {
        $form = new CategoryForm();

        if ($form->load(Yii::$app->request->post())) {
            try {
                $category = $this->service->create($this->getUserId(), $form);
                return $category->toArray();
            } catch (DomainException $e) {
                Yii::$app->response->statusCode = 422;
                return ['error' => $e->getMessage()];
            }
        }

        Yii::$app->response->statusCode = 400;
        return ['error' => 'Данные не получены'];
    }

    public function actionUpdate(int $id): array|Response
    {
        $form = new CategoryForm();

        if ($form->load(Yii::$app->request->post())) {
            try {
                $category = $this->service->update($id, $this->getUserId(), $form);
                return $category->toArray();
            } catch (DomainException $e) {
                Yii::$app->response->statusCode = 422;
                return ['error' => $e->getMessage()];
            }
        }

        Yii::$app->response->statusCode = 400;
        return ['error' => 'Данные для обновления не получены'];
    }

    public function actionDelete(int $id): array
    {
        try {
            $this->service->delete($id, $this->getUserId());
            return ['success' => true];
        } catch (DomainException $e) {
            Yii::$app->response->statusCode = 400;
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function actionType(int $id): array
    {
        $category = $this->service->findById($id, $this->getUserId());
        return [
            'success' => true,
            'type' => $category->type
        ];
    }

    private function getUserId(): int
    {
        return (int)Yii::$app->user->id;
    }
}