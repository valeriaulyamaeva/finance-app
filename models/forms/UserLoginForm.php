<?php

declare(strict_types=1);

namespace app\models\forms;

use yii\base\Model;

class UserLoginForm extends Model
{
    public string $email = '';
    public string $password = '';
    public bool $rememberMe = true;

    public function rules(): array
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'trim'],
            ['email', 'email'],
            ['rememberMe', 'boolean'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'email'      => 'Email',
            'password'   => 'Пароль',
            'rememberMe' => 'Запомнить меня',
        ];
    }
}
