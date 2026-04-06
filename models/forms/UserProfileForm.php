<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\User;
use yii\base\Model;

class UserProfileForm extends Model
{
    public string $username = '';
    public string $email = '';
    public string $theme = '';
    public string $currency = '';
    public ?string $new_password = null;

    public function __construct(
        private readonly User $user,
        array $config = []
    ) {
        $this->username = $user->username;
        $this->email    = $user->email;
        $this->theme    = $user->theme;
        $this->currency = $user->currency;
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['username', 'email'], 'required'],
            [['username', 'email'], 'trim'],
            ['username', 'string', 'max' => 255],
            ['email', 'email'],
            ['email', 'unique',
                'targetClass' => User::class,
                'filter' => ['<>', 'id', $this->user->id],
                'message' => 'Этот email уже используется.'
            ],
            [['theme'], 'in', 'range' => array_keys(User::optsTheme())],
            [['currency'], 'in', 'range' => array_keys(User::optsCurrency())],
            ['new_password', 'string', 'min' => 6],
            ['new_password', 'default', 'value' => null],
        ];
    }
}