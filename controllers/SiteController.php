<?php

namespace app\controllers;

use app\models\User;
use app\services\UserService;
use Yii;
use yii\authclient\AuthAction;
use yii\db\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

class SiteController extends BaseController
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

    public function beforeAction($action): bool
    {
        if (in_array($action->id, ['login', 'register', 'google-login', 'home'])) {
            $this->layout = 'guest';
        } else {
            $this->layout = 'main';
        }
        return parent::beforeAction($action);
    }

    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'auth' => [
                'class' => AuthAction::class,
                'successCallback' => [$this, 'onAuthSuccess'],
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
                return $this->redirect(['./analytics']);
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

    /**
     * @throws \yii\base\Exception
     * @throws Exception
     */
    public function actionGoogleLogin(): Response
    {
        $client = Yii::$app->authClientCollection->getClient('google');

        try {
            if ($this->request->get('code')) {
                $token = $client->fetchAccessToken($this->request->get('code'));
                if (!$token) {
                    Yii::error('Failed to fetch access token: ' . json_encode($client->getErrors()), __METHOD__);
                    Yii::$app->session->setFlash('error', 'Ошибка получения токена от Google.');
                    return $this->redirect(['login']);
                }
                Yii::info('Access token: ' . json_encode($token), __METHOD__);

                $attributes = $client->getUserAttributes();
                Yii::info('Google user attributes: ' . json_encode($attributes), __METHOD__);

                $email = $attributes['email'] ?? null;
                $name = $attributes['name'] ?? 'Без имени';

                if (!$email) {
                    Yii::error('No email provided by Google', __METHOD__);
                    Yii::$app->session->setFlash('error', 'Не удалось получить email от Google.');
                    return $this->redirect(['login']);
                }

                $user = User::findOne(['email' => $email]);
                if (!$user) {
                    $user = new User([
                        'username' => $name,
                        'email' => $email,
                        'auth_key' => Yii::$app->security->generateRandomString(),
                        'password_hash' => Yii::$app->security->generatePasswordHash(Yii::$app->security->generateRandomString(10)),
                    ]);
                    if (!$user->save(false)) {
                        Yii::error('Failed to save user: ' . json_encode($user->errors), __METHOD__);
                        Yii::$app->session->setFlash('error', 'Ошибка при создании пользователя: ' . json_encode($user->errors));
                        return $this->redirect(['login']);
                    }
                    Yii::info('User created: ' . $email, __METHOD__);
                } else {
                    Yii::info('User found: ' . $email, __METHOD__);
                }

                if (Yii::$app->user->login($user, 3600 * 24 * 30)) {
                    $user->last_login = date('Y-m-d H:i:s');
                    if (!$user->save(false)) {
                        Yii::error('Failed to update last_login: ' . json_encode($user->errors), __METHOD__);
                    }
                    Yii::info('User logged in: ' . $email, __METHOD__);
                    return $this->redirect(['/budget']);
                } else {
                    Yii::error('Failed to login user', __METHOD__);
                    Yii::$app->session->setFlash('error', 'Ошибка при авторизации пользователя.');
                    return $this->redirect(['login']);
                }
            }

            return $this->redirect($client->buildAuthUrl());
        } catch (\Exception $e) {
            Yii::error('Google login error: ' . $e->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Ошибка при авторизации через Google: ' . $e->getMessage());
            return $this->redirect(['login']);
        }
    }

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}