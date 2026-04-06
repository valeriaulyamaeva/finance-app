<?php

declare(strict_types=1);

namespace app\models\forms;

use yii\base\Model;

class ChangePasswordForm extends Model
{
    public string $password = '';
    public string $password_repeat = '';

    public function rules(): array
    {
        return [
            [['password', 'password_repeat'], 'required'],
            ['password', 'string', 'min' => 6],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'password'        => 'Новый пароль',
            'password_repeat' => 'Повторите пароль',
        ];
    }
}
