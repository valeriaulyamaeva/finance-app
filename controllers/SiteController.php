<?php

namespace app\controllers;

use app\models\User;
use app\services\UserService;
use Yii;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Controller for handling site-wide actions.
 */
class SiteController extends Controller
{
    private UserService $userService;

    public function __construct($id, $module, UserService $userService, $config = [])
    {
        $this->userService = $userService;
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'register', 'login'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['register', 'login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'register' => ['get', 'post'],
                    'login' => ['get', 'post'],
                ],
            ],
        ];
    }

    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('index');
    }

    /**
     * @throws Exception
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new User(['scenario' => 'login']);
        if ($this->request->isPost && $model->load($this->request->post()) && $model->validate()) {
            $user = $this->userService->authenticate($model->email, $model->password);
            if ($user && Yii::$app->user->login($user, 3600 * 24 * 30)) {
                $user->last_login = date('Y-m-d H:i:s');
                $user->save(false);
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Неверный email или пароль.');
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * @throws Exception
     * @throws \yii\base\Exception
     */
    public function actionRegister(): Response|string
    {
        $model = new User(['scenario' => 'create']);

        if ($model->load(Yii::$app->request->post())) {
            $model->setPassword($model->password);
            $model->generateAuthKey();

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Регистрация прошла успешно');
                return $this->redirect(['site/login']);
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка при регистрации');
            }
        }

        return $this->render('register', [
            'model' => $model,
        ]);
    }

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}