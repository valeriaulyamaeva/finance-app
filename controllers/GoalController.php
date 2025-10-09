<?php

namespace app\controllers;

use app\models\Goal;
use app\services\GoalService;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;

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
                ],
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
    public function actionView(int $id): string
    {
        $model = $this->findModel($id);
        return $this->render('view', compact('model'));
    }

    public function actionCreate(): Response|string
    {
        $model = new Goal();

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post('Goal', []);
            try {
                $goal = $this->service->create($data, Yii::$app->user->id);
                return $this->redirect(['view', 'id' => $goal->id]);
            } catch (Throwable $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('create', compact('model'));
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id): Response|string
    {
        $goal = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post('Goal', []);
            try {
                $goal = $this->service->update($goal, $data);
                return $this->redirect(['view', 'id' => $goal->id]);
            } catch (Throwable $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('update', compact('goal'));
    }

    public function actionDelete(int $id): Response
    {
        try {
            $this->findModel($id)->delete();
        } catch (StaleObjectException|Throwable $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(['index']);
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
