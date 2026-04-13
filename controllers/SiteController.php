<?php

namespace app\controllers;

use app\models\User;
use app\services\CategoryService;
use app\services\KeycloakService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

class SiteController extends BaseController
{
    private KeycloakService $keycloakService;
    private CategoryService $categoryService;

    public function __construct($id, $module, KeycloakService $keycloakService, CategoryService $categoryService, $config = [])
    {
        $this->keycloakService = $keycloakService;
        $this->categoryService = $categoryService;
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['get', 'post'],
                    'register' => ['get', 'post'],
                    'login' => ['get', 'post'],
                ],
            ],
        ];
    }

    public function beforeAction($action): bool
    {
        if (in_array($action->id, ['login', 'register', 'home', 'index', 'error'])) {
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
        ];
    }

    public function actionIndex(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/analytics']);
        }
        return $this->redirect(['/login']);
    }

    /**
     * Login: our form → Keycloak validates credentials → local session.
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/analytics']);
        }

        $model = new User(['scenario' => 'login']);

        if ($this->request->isPost && $model->load($this->request->post())) {
            $kcUser = $this->keycloakService->authenticate($model->email, $model->password);

            if ($kcUser) {
                $user = User::findOne(['email' => $kcUser['email']]);
                if (!$user) {
                    // First login — create local user synced from Keycloak
                    $user = new User([
                        'username' => $kcUser['name'],
                        'email' => $kcUser['email'],
                        'auth_key' => Yii::$app->security->generateRandomString(),
                        'password_hash' => Yii::$app->security->generatePasswordHash(
                            Yii::$app->security->generateRandomString(16)
                        ),
                    ]);
                    if (!$user->save(false)) {
                        Yii::$app->session->setFlash('error', 'Ошибка создания пользователя.');
                        return $this->render('login', ['model' => $model]);
                    }
                    $this->categoryService->createDefaultCategories($user->id);
                }

                $user->last_login = date('Y-m-d H:i:s');
                $user->save(false);

                Yii::$app->user->login($user, 3600 * 24 * 30);
                return $this->redirect(['/analytics']);
            }

            Yii::$app->session->setFlash('error', 'Неверный email или пароль.');
        }

        return $this->render('login', ['model' => $model]);
    }

    /**
     * Register: our form → Keycloak creates user → local user synced → redirect to login.
     */
    public function actionRegister(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/analytics']);
        }

        $model = new User(['scenario' => 'create']);

        if ($model->load(Yii::$app->request->post())) {
            try {
                // Validate locally FIRST, before touching Keycloak
                if (!$model->validate()) {
                    return $this->render('register', ['model' => $model]);
                }

                // Create user in Keycloak
                $this->keycloakService->register(
                    $model->email,
                    $model->password,
                    $model->username
                );

                $model->setPassword($model->password);
                $model->generateAuthKey();
                $model->status = User::STATUS_ACTIVE;

                if ($model->save(false)) {
                    $this->categoryService->createDefaultCategories($model->id);
                    $model->last_login = date('Y-m-d H:i:s');
                    $model->save(false);
                    Yii::$app->user->login($model, 3600 * 24 * 30);
                    return $this->redirect(['/analytics']);
                }

                Yii::error('User save failed: ' . json_encode($model->getErrors()));
                Yii::$app->session->setFlash('error', 'Ошибка сохранения пользователя.');
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('register', ['model' => $model]);
    }

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();
        return $this->redirect(['/login']);
    }
}
