<?php

namespace app\controllers;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class BaseController extends Controller
{
    /**
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            $theme = $user->theme ?? 'light';

            Yii::$app->view->params['theme'] = $theme;
        } else {
            Yii::$app->view->params['theme'] = 'light';
        }

        return true;
    }
}
