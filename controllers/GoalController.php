<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Category;
use app\models\Goal;
use app\models\forms\GoalForm;
use app\services\CurrencyService;
use app\services\GoalService;
use DomainException;
use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;

final class GoalController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly GoalService $service,
        private readonly CurrencyService $currencyService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'only' => ['create', 'update', 'delete', 'view'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ]);
    }

    public function actionIndex(): string
    {
        $userId = (int)Yii::$app->user->id;
        $user = Yii::$app->user->identity;

        $goals = Goal::find()
            ->forUser($userId)
            ->orderBy(['deadline' => SORT_ASC])
            ->all();

        // Categories of type "goal" — for binding goals to a category
        $categories = Category::find()
            ->where(['user_id' => $userId, 'type' => Category::TYPE_GOAL])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'goals' => $goals,
            'user' => $user,
            'currencyService' => $this->currencyService,
            'categories' => $categories,
        ]);
    }

    public function actionCreate(): array
    {
        $form = new GoalForm();
        $user = Yii::$app->user->identity;

        if ($form->load(Yii::$app->request->post(), 'Goal')) {
            try {
                $goal = $this->service->create((int)$user->id, $form, $user->currency);
                return ['success' => true, 'goal' => $goal->toArray()];
            } catch (DomainException $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return ['success' => false, 'message' => 'Некорректные данные'];
    }

    public function actionUpdate(int $id): array
    {
        $form = new GoalForm();
        if ($form->load(Yii::$app->request->post(), 'Goal')) {
            try {
                $goal = $this->service->update($id, (int)Yii::$app->user->id, $form);
                return ['success' => true, 'goal' => $goal->toArray()];
            } catch (DomainException $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return ['success' => false, 'message' => 'Ошибка загрузки данных'];
    }

    public function actionView(int $id): array
    {
        try {
            $goal = $this->service->findById($id, (int)Yii::$app->user->id);
            return [
                'success' => true,
                'goal' => $goal->toArray(),
                'progress' => $goal->getProgress()
            ];
        } catch (DomainException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionDelete(int $id): array
    {
        try {
            $this->service->delete($id, (int)Yii::$app->user->id);
            return ['success' => true];
        } catch (DomainException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}