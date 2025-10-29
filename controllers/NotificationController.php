<?php

namespace app\controllers;

use app\models\Notification;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use app\services\NotificationService;

class NotificationController extends Controller
{
    private NotificationService $service;

    public function __construct($id, $module, NotificationService $service, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }

    public function beforeAction($action): bool
    {
        if (in_array($action->id, ['mark-read', 'mark-all-read'])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->id;

        $notifications = $this->service->getUserNotifications($userId);
        $unreadCount = $this->service->countUnread($userId);

        return [
            'notifications' => array_map(static function ($notification) {
                return $notification->toArray();
            }, array_slice($notifications, 0, 20)),
            'unread_count' => $unreadCount,
        ];
    }

    public function actionMarkRead(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $userId = Yii::$app->user->id;
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