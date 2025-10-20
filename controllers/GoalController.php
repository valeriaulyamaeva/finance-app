<?php

namespace app\controllers;

use app\models\Goal;
use app\services\CurrencyService;
use app\services\GoalService;
use Exception;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class GoalController extends BaseController
{
    public GoalService $service;
    private CurrencyService $currencyService;

    public function __construct($id, $module, GoalService $service, CurrencyService $currencyService, $config = [])
    {
        $this->service = $service;
        $this->currencyService = $currencyService;
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'create' => ['GET', 'POST'],
                    'update' => ['GET', 'POST'],
                    'view' => ['GET'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function () {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        return ['success' => false, 'message' => 'Требуется авторизация'];
                    }
                    return Yii::$app->response->redirect(['site/login']);
                },
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    public function actionIndex(): string
    {
        $user = Yii::$app->user->identity;
        $currency = $user->currency;

        $dataProvider = new ActiveDataProvider([
            'query' => Goal::find()->where(['user_id' => $user->id])->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        if ($currency !== 'BYN') {
            $rate = $this->currencyService->getRate('BYN', $currency);
            foreach ($dataProvider->models as $goal) {
                $goal->target_amount *= $rate;
                $goal->current_amount *= $rate;
            }
        }

        return $this->render('index', compact('dataProvider', 'user'));
    }

    /**
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionView(int $id): array|string
    {
        $model = $this->findModel($id);
        $currency = Yii::$app->user->identity->currency;
        $displayTargetAmount = $model->target_amount;
        $displayCurrentAmount = $model->current_amount;

        if ($currency !== 'BYN') {
            $rate = $this->currencyService->getRate('BYN', $currency);
            $displayTargetAmount *= $rate;
            $displayCurrentAmount *= $rate;
        }

        $goalArray = $model->toArray();
        $goalArray['display_target_amount'] = number_format($displayTargetAmount, 2, '.', '');
        $goalArray['display_current_amount'] = number_format($displayCurrentAmount, 2, '.', '');

        if (Yii::$app->request->isAjax || Yii::$app->request->get('ajax')) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => true, 'goal' => $goalArray];
        }
        return $this->render('view', compact('model'));
    }

    /**
     * @throws Exception
     */
    public function actionCreate(): array|string|Response
    {
        $user = Yii::$app->user->identity;
        $model = new Goal();
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post('Goal', []);
            $currency = $user->currency;
            $originalTargetAmount = $data['target_amount'] ?? 0;
            $originalCurrentAmount = $data['current_amount'] ?? 0;

            if ($currency !== 'BYN') {
                $rate = $this->currencyService->getRate($currency, 'BYN');
                if (isset($data['target_amount'])) {
                    $data['target_amount'] *= $rate;
                }
                if (isset($data['current_amount'])) {
                    $data['current_amount'] *= $rate;
                }
            }

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                try {
                    $goal = $this->service->create($data, $user->id);
                    $goalArray = $goal->toArray();
                    $goalArray['display_target_amount'] = number_format($originalTargetAmount, 2, '.', '');
                    $goalArray['display_current_amount'] = number_format($originalCurrentAmount, 2, '.', '');
                    return ['success' => true, 'goal' => $goalArray];
                } catch (Throwable $e) {
                    Yii::error('Create goal error: ' . $e->getMessage(), __METHOD__);
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            } else {
                try {
                    $goal = $this->service->create($data, $user->id);
                    return $this->redirect(['view', 'id' => $goal->id]);
                } catch (Throwable $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }
        return $this->render('create', compact('model', 'user'));
    }

    /**
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionUpdate(int $id): Response|string|array
    {
        $goal = $this->findModel($id);
        $user = Yii::$app->user->identity;
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post('Goal', []);
            $currency = $user->currency;
            $originalTargetAmount = $data['target_amount'] ?? 0;
            $originalCurrentAmount = $data['current_amount'] ?? 0;

            if ($currency !== 'BYN') {
                $rate = $this->currencyService->getRate($currency, 'BYN');
                if (isset($data['target_amount'])) {
                    $data['target_amount'] *= $rate;
                }
                if (isset($data['current_amount'])) {
                    $data['current_amount'] *= $rate;
                }
            }

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                try {
                    $goal = $this->service->update($goal, $data);
                    $goalArray = $goal->toArray();
                    $goalArray['display_target_amount'] = number_format($originalTargetAmount, 2, '.', '');
                    $goalArray['display_current_amount'] = number_format($originalCurrentAmount, 2, '.', '');
                    return ['success' => true, 'goal' => $goalArray];
                } catch (Throwable $e) {
                    Yii::error('Update goal error: ' . $e->getMessage(), __METHOD__);
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            } else {
                try {
                    $goal = $this->service->update($goal, $data);
                    return $this->redirect(['view', 'id' => $goal->id]);
                } catch (Throwable $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }
        return $this->render('update', compact('goal', 'user'));
    }

    public function actionDelete(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $goal = $this->findModel($id);
            $goal->delete();
            return ['success' => true];
        } catch (NotFoundHttpException $e) {
            return ['success' => false, 'message' => 'Цель не найдена или вы не авторизованы'];
        } catch (Throwable $e) {
            Yii::error('Delete goal error: ' . $e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => 'Ошибка при удалении цели: ' . $e->getMessage()];
        }
    }

    /**
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): Goal
    {
        $goal = Goal::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        if (!$goal) {
            throw new NotFoundHttpException('Goal not found.');
        }
        return $goal;
    }
}