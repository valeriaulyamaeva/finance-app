<?php

declare(strict_types=1);

namespace app\models\forms;

use yii\base\Model;
use app\models\User;

class UserRegisterForm extends Model
{
    public string $email = '';
    public string $password = '';
    public string $password_repeat = '';

    public function rules(): array
    {
        return [
            [['email', 'password', 'password_repeat'], 'required'],
            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique',
                'targetClass' => User::class,
                'targetAttribute' => 'email',
                'message' => 'Пользователь с таким email уже существует.',
            ],
            ['password', 'string', 'min' => 8],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'email'           => 'Email',
            'password'        => 'Пароль',
            'password_repeat' => 'Повторите пароль',
        ];
    }
}
