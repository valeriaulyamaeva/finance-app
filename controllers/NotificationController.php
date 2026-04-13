<?php

namespace app\controllers;

use app\models\Notification;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;
use app\services\NotificationService;

class NotificationController extends BaseController
{
    private NotificationService $service;

    public function __construct($id, $module, NotificationService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
                'denyCallback' => function () {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    Yii::$app->response->statusCode = 401;
                    return ['notifications' => [], 'unread_count' => 0];
                },
            ],
        ];
    }


    public function actionIndex(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = (int)Yii::$app->user->id;

        $notifications = $this->service->getUserNotifications($userId);
        $unreadCount = $this->service->countUnread($userId);

        return [
            'notifications' => array_map(static function ($notification) {
                return $notification->toArray();
            }, array_slice($notifications, 0, 20)),
            'unread_count' => $unreadCount,
        ];
    }

    public function actionMarkRead(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = (int)Yii::$app->request->get('id');
        $userId = (int)Yii::$app->user->id;
        $notification = Notification::findOne(['id' => $id, 'user_id' => $userId]);
        if ($notification && $this->service->markAsRead($id)) {
            return ['success' => true];
        }
        Yii::error("Notification with id=$id not found or user_id=$userId mismatch", __METHOD__);
        return ['success' => false, 'error' => 'Notification not found or access denied'];
    }

    public function actionMarkAllRead(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->id;
        $notifications = Notification::find()->where(['user_id' => $userId, 'read_status' => 0])->all();
        foreach ($notifications as $notification) {
            $notification->read_status = 1;
            $notification->save(false);
        }
        return ['success' => true];
    }
}