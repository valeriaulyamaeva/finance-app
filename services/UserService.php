<?php

declare(strict_types=1);

namespace app\services;

use app\models\forms\ChangePasswordForm;
use app\models\forms\UserProfileForm;
use app\models\forms\UserRegisterForm;
use app\models\User;
use yii\db\Connection;
use RuntimeException;
use Throwable;

final readonly class UserService
{
    public function __construct(
        private Connection $db,
        private CategoryService $categoryService
    ) {
    }

    /**
     * @throws Throwable
     */
    public function register(UserRegisterForm $form): User
    {
        $transaction = $this->db->beginTransaction();
        try {
            $user = new User();
            $user->email    = $form->email;
            $user->username = $this->extractUsernameFromEmail($form->email);
            $user->status   = User::STATUS_ACTIVE;
            $user->theme    = User::THEME_LIGHT;
            $user->currency = User::CURRENCY_BYN;

            $user->setPassword($form->password);
            $user->generateAuthKey();

            if (!$user->save()) {
                throw new RuntimeException('Ошибка сохранения: ' . implode(', ', $user->getErrorSummary(true)));
            }

            $this->categoryService->createDefaultCategories($user->id);
            $transaction->commit();
            return $user;
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function authenticate(string $email, string $password): ?User
    {
        $user = User::findByEmail($email);

        if (!$user || !$user->validatePassword($password)) {
            return null;
        }

        return $user;
    }

    /**
     * @throws Throwable
     */
    public function updateProfile(User $user, UserProfileForm $form): User
    {
        $transaction = $this->db->beginTransaction();
        try {
            $user->username = $form->username;
            $user->email    = $form->email;

            if ($form->theme) {
                $user->theme = $form->theme;
            }
            if ($form->currency) {
                $user->currency = strtoupper($form->currency);
            }

            if (!empty($form->new_password)) {
                $user->setPassword($form->new_password);
                $user->generateAuthKey();
            }

            if (!$user->save()) {
                throw new RuntimeException('Ошибка сохранения: ' . implode(', ', $user->getErrorSummary(true)));
            }

            $transaction->commit();
            return $user;
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @throws Throwable
     */
    public function changePassword(User $user, ChangePasswordForm $form): void
    {
        $transaction = $this->db->beginTransaction();
        try {
            $user->setPassword($form->password);
            $user->generateAuthKey();

            if (!$user->save()) {
                throw new RuntimeException('Не удалось изменить пароль.');
            }
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function extractUsernameFromEmail(string $email): string
    {
        return strstr($email, '@', true) ?: 'user_' . time();
    }
}