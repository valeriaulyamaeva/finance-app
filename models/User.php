<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use Override;

/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $auth_key
 * @property string|null $access_token
 * @property string $theme
 * @property string $currency
 * @property string|null $avatar
 * @property string|null $last_login
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property-read Budget[] $budgets
 * @property-read Category[] $categories
 * @property-read Goal[] $goals
 * @property-read Transaction[] $transactions
 */
class User extends ActiveRecord implements IdentityInterface
{
    public const string THEME_LIGHT = 'light';
    public const string THEME_DARK  = 'dark';

    public const int STATUS_ACTIVE  = 1;
    public const int STATUS_DELETED = 0;

    public const string CURRENCY_BYN = 'BYN';
    public const string CURRENCY_USD = 'USD';
    public const string CURRENCY_EUR = 'EUR';
    public const string CURRENCY_RUB = 'RUB';

    #[Override]
    public static function tableName(): string
    {
        return '{{%user}}';
    }

    #[Override]
    public function rules(): array
    {
        return [
            [['username', 'email', 'password_hash', 'auth_key'], 'required'],
            ['email', 'email'],
            ['email', 'unique'],
            ['status', 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['theme', 'in', 'range' => array_keys(self::optsTheme())],
            ['currency', 'in', 'range' => array_keys(self::optsCurrency())],
            [['username', 'email', 'password_hash', 'access_token', 'avatar'], 'string', 'max' => 255],
            ['auth_key', 'string', 'max' => 32],
            ['currency', 'string', 'max' => 3],
            [['last_login', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    #[Override]
    public function attributeLabels(): array
    {
        return [
            'id'            => 'ID',
            'username'      => 'Имя',
            'email'         => 'Email',
            'password_hash' => 'Хэш пароля',
            'auth_key'      => 'Ключ авторизации',
            'access_token'  => 'Токен доступа',
            'theme'         => 'Тема',
            'currency'      => 'Валюта',
            'avatar'        => 'Аватар',
            'last_login'    => 'Последний вход',
            'status'        => 'Статус',
            'created_at'    => 'Создано',
            'updated_at'    => 'Обновлено',
        ];
    }

    #[Override]
    public static function findIdentity($id): ?static
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    #[Override]
    public static function findIdentityByAccessToken($token, $type = null): ?static
    {
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByEmail(string $email): ?static
    {
        return static::find()
            ->where(['status' => self::STATUS_ACTIVE])
            ->andWhere(['LOWER(email)' => strtolower(trim($email))])
            ->one();
    }

    #[Override]
    public function getId(): int|string
    {
        return $this->id;
    }

    #[Override]
    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    #[Override]
    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public static function optsTheme(): array
    {
        return [
            self::THEME_LIGHT => 'Светлая',
            self::THEME_DARK  => 'Тёмная',
        ];
    }

    public static function optsCurrency(): array
    {
        return [
            self::CURRENCY_BYN => 'BYN',
            self::CURRENCY_USD => 'USD',
            self::CURRENCY_EUR => 'EUR',
            self::CURRENCY_RUB => 'RUB',
        ];
    }

    public function getBudgets(): ActiveQuery
    {
        return $this->hasMany(Budget::class, ['user_id' => 'id']);
    }

    public function getCategories(): ActiveQuery
    {
        return $this->hasMany(Category::class, ['user_id' => 'id']);
    }

    public function getGoals(): ActiveQuery
    {
        return $this->hasMany(Goal::class, ['user_id' => 'id']);
    }

    public function getTransactions(): ActiveQuery
    {
        return $this->hasMany(Transaction::class, ['user_id' => 'id']);
    }
}