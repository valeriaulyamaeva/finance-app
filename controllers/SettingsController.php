<?php

namespace app\controllers;

use app\models\User;
use app\services\CurrencyService;
use Exception;
use Throwable;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class SettingsController extends Controller
{
    public CurrencyService $currencyService;

    public function __construct($id, $module, CurrencyService $currencyService, $config = [])
    {
        $this->currencyService = $currencyService;
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'save' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'save'],
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
        $user = Yii::$app->user->identity;

        return $this->render('index', [
            'user' => $user,
        ]);
    }

    public function actionSave(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;
        $data = Yii::$app->request->post('User', []);

        try {
            if (isset($data['username']) && $data['username'] !== $user->username) {
                $user->username = $data['username'];
            }

            if (isset($data['email']) && $data['email'] !== $user->email) {
                if (User::findOne(['email' => $data['email']])) {
                    throw new Exception('Email уже используется');
                }
                $user->email = $data['email'];
            }

            if (!empty($data['password'])) {
                $user->setPassword($data['password']);
                $user->generateAuthKey();
            }

            if (isset($data['theme']) && in_array($data['theme'], ['light', 'dark'])) {
                $user->theme = $data['theme'];
            }

            if (isset($data['currency']) && in_array($data['currency'], ['BYN', 'USD', 'EUR', 'RUB'])) {
                $user->currency = $data['currency'];
            }

            if (!$user->save()) {
                throw new Exception(implode(', ', $user->firstErrors));
            }

            return ['success' => true];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}