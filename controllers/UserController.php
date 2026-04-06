<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use app\models\User;
use app\models\forms\UserLoginForm;
use app\models\forms\UserRegisterForm;
use app\models\forms\ChangePasswordForm;
use app\models\forms\UserProfileForm;
use app\services\UserService;
use Throwable;

class UserController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly UserService $userService,
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
                    [
                        'actions' => ['login', 'register'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['profile', 'update', 'change-password', 'logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionRegister(): Response|string
    {
        $form = new UserRegisterForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $user = $this->userService->register($form);
                Yii::$app->user->login($user);
                Yii::$app->session->setFlash('success', 'Регистрация успешна!');
                return $this->redirect(['profile']);
            } catch (Throwable $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
                Yii::error($e->getMessage());
            }
        }

        return $this->render('register', ['model' => $form]);
    }

    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['profile']);
        }

        $form = new UserLoginForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $user = $this->userService->authenticate($form->email, $form->password);

            if ($user) {
                $duration = $form->rememberMe ? 3600 * 24 * 30 : 0;
                if (Yii::$app->user->login($user, $duration)) {
                    $user->updateAttributes(['last_login' => date('Y-m-d H:i:s')]);
                    return $this->redirect(['profile']);
                }
            } else {
                $form->addError('password', 'Неверный email или пароль.');
            }
        }

        return $this->render('login', ['model' => $form]);
    }

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionProfile(): string
    {
        return $this->render('profile', [
            'user' => Yii::$app->user->identity
        ]);
    }

    public function actionUpdate(): Response|string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $form = new UserProfileForm($user);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->userService->updateProfile($user, $form);
                Yii::$app->session->setFlash('success', 'Профиль обновлен.');
                return $this->redirect(['profile']);
            } catch (Throwable $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('update', ['model' => $form]);
    }

    public function actionChangePassword(): Response|string
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $form = new ChangePasswordForm();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->userService->changePassword($user, $form);
                Yii::$app->session->setFlash('success', 'Пароль успешно изменен.');
                return $this->redirect(['profile']);
            } catch (Throwable $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('change-password', ['model' => $form]);
    }
}