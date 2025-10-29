<?php

namespace app\services;

use app\models\Notification;
use yii\db\Exception;

class NotificationService
{
    /**
     * @param int $userId
     * @param string $message
     * @param string $type
     * @param string|null $relatedType
     * @param int|null $relatedId
     * @return Notification|null
     * @throws Exception
     */
    public function createNotification(int $userId, string $message, string $type, ?string $relatedType = null, ?int $relatedId = null): ?Notification
    {
        $notification = new Notification();
        $notification->user_id = $userId;
        $notification->message = $message;
        $notification->type = $type;
        $notification->related_type = $relatedType;
        $notification->related_id = $relatedId;

        return $notification->save() ? $notification : null;
    }

    /**
     * @param int $userId
     * @param bool $onlyUnread
     * @return Notification[]
     */
    public function getUserNotifications(int $userId, bool $onlyUnread = false): array
    {
        $query = Notification::find()->where(['user_id' => $userId]);
        if ($onlyUnread) {
            $query->andWhere(['read_status' => 0]);
        }
        return $query->orderBy(['created_at' => SORT_DESC])->all();
    }

    public function countUnread(int $userId): int
    {
        return (int) Notification::find()->where(['user_id' => $userId, 'read_status' => 0])->count();
    }

    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::findOne($notificationId);
        if (!$notification) {
            return false;
        }
        $notification->read_status = 1;
        return $notification->save(false);
    }

    public function deleteNotification(int $notificationId): bool
    {
        $notification = Notification::findOne($notificationId);
        return (bool) $notification?->delete();
    }
}