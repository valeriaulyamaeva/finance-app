<?php

namespace app\services;

use app\models\User;
use Yii;
use yii\base\Exception;

class UserSettingsService
{
    public function updateProfile(User $user, array $data): array
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = $data['User'] ?? $data;

            if (!empty($post['username'])) $user->username = trim($post['username']);
            if (!empty($post['email'])) $user->email = trim($post['email']);
            if (!empty($post['theme'])) $user->theme = $post['theme'];
            if (!empty($post['currency'])) $user->currency = strtoupper($post['currency']);

            if (!empty($post['password'])) {
                if (strlen($post['password']) < 6) {
                    return ['success' => false, 'message' => 'Пароль должен быть не менее 6 символов'];
                }
                $user->setPassword($post['password']);
            }

            if (!$user->save()) {
                return ['success' => false, 'errors' => $user->errors];
            }

            $transaction->commit();

            Yii::$app->session->setFlash('success', 'Настройки обновлены');
            return ['success' => true];
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => 'Ошибка при сохранении: ' . $e->getMessage()];
        }
    }
}
