<?php

declare(strict_types=1);

namespace app\controllers;

use app\services\ForecastService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;

final class ForecastController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly ForecastService $forecastService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
                'denyCallback' => function () {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        Yii::$app->response->statusCode = 401;
                        return ['success' => false];
                    }
                    return $this->redirect(['/login']);
                },
            ],
        ];
    }

    public function actionIndex(): string
    {
        $user = Yii::$app->user->identity;
        $userId = (int)$user->id;
        $userCurrency = $user->currency ?? 'BYN';

        return $this->render('index', [
            'user' => $user,
            'monthEnd' => $this->forecastService->forecastMonthEnd($userId, $userCurrency),
            'categories' => $this->forecastService->forecastCategories($userId, $userCurrency),
            'goals' => $this->forecastService->forecastGoals($userId),
            'timeline' => $this->forecastService->balanceTimeline($userId, $userCurrency),
            'userCurrency' => $userCurrency,
        ]);
    }
}
