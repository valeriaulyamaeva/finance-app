<?php

namespace app\services;

use app\models\User;
use Yii;
use yii\base\Exception;
use yii\db\Exception as DbException;

/**
 * Service for handling user-related operations.
 */
class UserService
{
    /**
     * Authenticate a user by email and password.
     *
     * @param string $email
     * @param string $password
     * @return User|null
     */
    public function authenticate(string $email, string $password): ?User
    {
        $email = trim($email);
        Yii::info("LOGIN ATTEMPT: Email = '$email', Password = [HIDDEN]", __METHOD__);

        $user = User::findByEmail($email);
        if (!$user) {
            Yii::error("LOGIN ERROR: User not found for email: '$email'", __METHOD__);
            return null;
        }

        Yii::info("LOGIN DEBUG: Found user ID: $user->id, Email: $user->email, Status: $user->status", __METHOD__);
        $isValid = Yii::$app->security->validatePassword($password, $user->password_hash);
        Yii::info("LOGIN DEBUG: Password valid? " . ($isValid ? 'YES' : 'NO'), __METHOD__);

        if ($isValid) {
            return $user;
        }

        return null;
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @return User|null
     * @throws Exception
     */
    public function register(array $data): ?User
    {
        $user = new User(['scenario' => 'create']);

        // ✅ Универсальная загрузка данных
        if (isset($data['User'])) {
            $user->load($data);
            $password = $data['User']['password'] ?? '';
        } else {
            $user->load([$user->formName() => $data]);
            $password = $data['password'] ?? '';
        }

        Yii::info('REGISTER DEBUG: password = ' . var_export($password, true), __METHOD__);

        $user->setPassword($password);
        $user->auth_key = Yii::$app->security->generateRandomString();
        $user->access_token = Yii::$app->security->generateRandomString(64);
        $user->status = User::STATUS_ACTIVE;
        $user->theme = User::THEME_LIGHT;
        $user->currency = 'BYN';
        $user->created_at = date('Y-m-d H:i:s');
        $user->updated_at = date('Y-m-d H:i:s');

        Yii::info('=== USER ATTRIBUTES BEFORE SAVE === ' . json_encode($user->attributes), __METHOD__);

        try {
            if (!$user->save(false)) {
                Yii::error('=== SAVE RETURNED FALSE === ' . json_encode($user->errors) . ', Attributes: ' . json_encode($user->attributes), __METHOD__);
                return null;
            }
        } catch (\yii\db\Exception $e) {
            Yii::error('=== DB EXCEPTION === ' . $e->getMessage() . ', Attributes: ' . json_encode($user->attributes), __METHOD__);
            return null;
        }

        return $user;
    }

}