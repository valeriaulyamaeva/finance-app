<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\forms\UserProfileForm;
use app\models\User;
use app\services\UserService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use Throwable;

class SettingsController extends BaseController
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
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('index', [
            'user' => Yii::$app->user->identity,
        ]);
    }

    public function actionSave(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        /** @var User $user */
        $user = Yii::$app->user->identity;

        $form = new UserProfileForm($user);

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $this->userService->updateProfile($user, $form);
                return ['success' => true, 'message' => 'Настройки сохранены'];
            } catch (Throwable $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }

        return [
            'success' => false,
            'errors' => $form->getErrors()
        ];
    }
}