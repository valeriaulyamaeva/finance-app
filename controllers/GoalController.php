<?php
namespace app\controllers;

use app\models\Goal;
use app\services\GoalService;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class GoalController extends Controller
{
    public GoalService $service;

    public function __construct($id, $module, GoalService $service, $config = [])
    {
        $this->service = $service;
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

    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Goal::find()->where(['user_id' => Yii::$app->user->id])->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', compact('dataProvider'));
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): array|string
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->isAjax || Yii::$app->request->get('ajax')) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => true, 'goal' => $model->toArray()];
        }
        return $this->render('view', compact('model'));
    }

    public function actionCreate(): array|string|Response
    {
        $model = new Goal();
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post('Goal', []);
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                try {
                    $goal = $this->service->create($data, Yii::$app->user->id);
                    return ['success' => true, 'goal' => $goal->toArray()];
                } catch (Throwable $e) {
                    Yii::error('Create goal error: ' . $e->getMessage(), __METHOD__);
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            } else {
                try {
                    $goal = $this->service->create($data, Yii::$app->user->id);
                    return $this->redirect(['view', 'id' => $goal->id]);
                } catch (Throwable $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }
        return $this->render('create', compact('model'));
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id): Response|string|array
    {
        $goal = $this->findModel($id);
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post('Goal', []);
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                try {
                    $goal = $this->service->update($goal, $data);
                    return ['success' => true, 'goal' => $goal->toArray()];
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
        return $this->render('update', compact('goal'));
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