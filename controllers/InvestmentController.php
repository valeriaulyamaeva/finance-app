<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\forms\InvestmentForm;
use app\services\InvestmentService;
use app\services\MarketDataService;
use DomainException;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use Throwable;

final class InvestmentController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly InvestmentService $service,
        private readonly MarketDataService $marketData,
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
                        return ['success' => false, 'message' => 'Требуется авторизация'];
                    }
                    return $this->redirect(['/login']);
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create' => ['post'],
                    'update' => ['post'],
                    'delete' => ['post'],
                    'refresh' => ['post'],
                ],
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
            'investments' => $this->service->getForUser($userId),
            'summary' => $this->service->getSummary($userId, $userCurrency),
            'userCurrency' => $userCurrency,
        ]);
    }

    public function actionCreate(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $form = new InvestmentForm();

        if ($form->load(Yii::$app->request->post())) {
            try {
                $this->service->create((int)Yii::$app->user->id, $form);
                return ['success' => true];
            } catch (DomainException $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }
        return ['success' => false, 'message' => 'Некорректные данные'];
    }

    public function actionUpdate(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $form = new InvestmentForm();

        if ($form->load(Yii::$app->request->post())) {
            try {
                $this->service->update($id, (int)Yii::$app->user->id, $form);
                return ['success' => true];
            } catch (DomainException $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }
        return ['success' => false, 'message' => 'Ошибка загрузки данных'];
    }

    public function actionView(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $model = $this->service->findById($id, (int)Yii::$app->user->id);
            return ['success' => true, 'investment' => $model->toArray()];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionDelete(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $this->service->delete($id, (int)Yii::$app->user->id);
            return ['success' => true];
        } catch (DomainException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /** AJAX: live price for a ticker (used to auto-fill current value in the form). */
    public function actionPrice(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $type = (string)Yii::$app->request->get('type');
        $ticker = (string)Yii::$app->request->get('ticker');
        return $this->marketData->getPrice($type, $ticker);
    }

    /** AJAX: refresh current value of all tickered assets from the market. */
    public function actionRefresh(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user = Yii::$app->user->identity;
        $res = $this->service->refreshPrices((int)$user->id, $user->currency ?? 'BYN');
        return ['success' => true, 'updated' => $res['updated'], 'failed' => $res['failed']];
    }
}
