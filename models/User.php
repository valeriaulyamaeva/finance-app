<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;

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
 * @property string|null $password Virtual attribute for registration/login
 * @property string|null $password_repeat Virtual attribute for password confirmation
 */
class User extends ActiveRecord implements IdentityInterface
{
    const THEME_LIGHT = 'light';
    const THEME_DARK = 'dark';
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 0;

    public ?string $password = null;
    public ?string $password_repeat = null;

    public static function tableName(): string
    {
        return 'user';
    }

    public function rules(): array
    {
        return [
            // Rules for 'create' scenario (registration)
            [['username', 'email', 'password', 'password_repeat'], 'required', 'on' => 'create'],
            [['password_repeat'], 'compare', 'compareAttribute' => 'password', 'on' => 'create'],
            [['password'], 'string', 'min' => 6, 'on' => 'create'],
            [['email'], 'email', 'on' => 'create'],
            [['email'], 'unique', 'on' => 'create'],
            // Rules for 'login' scenario
            [['email', 'password'], 'required', 'on' => 'login'],
            [['email'], 'email', 'on' => 'login'],
            [['password'], 'string', 'min' => 6, 'on' => 'login'],
            // Common rules
            [['access_token', 'avatar', 'last_login'], 'default', 'value' => null],
            [['theme'], 'default', 'value' => self::THEME_LIGHT],
            [['currency'], 'default', 'value' => 'BYN'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['created_at', 'updated_at'], 'default', 'value' => date('Y-m-d H:i:s')],
            [['username', 'email', 'password_hash', 'auth_key'], 'required'],
            [['theme'], 'string'],
            [['last_login', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'integer'],
            [['username', 'email', 'password_hash', 'access_token', 'avatar'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['currency'], 'string', 'max' => 3],
            ['theme', 'in', 'range' => array_keys(self::optsTheme())],
        ];
    }

    public function scenarios(): array
    {
        return [
            'create' => ['username', 'email', 'password', 'password_repeat', 'theme', 'currency', 'status', 'created_at', 'updated_at'],
            'login' => ['email', 'password'],
            self::SCENARIO_DEFAULT => ['username', 'email', 'password_hash', 'auth_key', 'access_token', 'theme', 'currency', 'avatar', 'last_login', 'status', 'created_at', 'updated_at'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'username' => 'Имя',
            'email' => 'Электронная почта',
            'password' => 'Пароль',
            'password_repeat' => 'Подтверждение пароля',
            'password_hash' => 'Хэш пароля',
            'auth_key' => 'Ключ авторизации',
            'access_token' => 'Токен доступа',
            'theme' => 'Тема',
            'currency' => 'Валюта',
            'avatar' => 'Аватар',
            'last_login' => 'Последний вход',
            'status' => 'Статус',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    public static function findIdentity($id): ?static
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?static
    {
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByEmail(string $email): ?static
    {
        return static::find()
            ->where(['status' => self::STATUS_ACTIVE])
            ->andWhere('LOWER(email) = :email', [':email' => strtolower(trim($email))])
            ->one();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * @throws Exception
     */
    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
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

    public function getNotifications(): ActiveQuery
    {
        return $this->hasMany(Notification::class, ['user_id' => 'id']);
    }

    public function getRecurringTransactions(): ActiveQuery
    {
        return $this->hasMany(RecurringTransaction::class, ['user_id' => 'id']);
    }

    public function getTransactions(): ActiveQuery
    {
        return $this->hasMany(Transaction::class, ['user_id' => 'id']);
    }

    public static function optsTheme(): array
    {
        return [
            self::THEME_LIGHT => 'Светлая',
            self::THEME_DARK => 'Темная',
        ];
    }

    public function displayTheme(): string
    {
        return self::optsTheme()[$this->theme];
    }

    public function isThemeLight(): bool
    {
        return $this->theme === self::THEME_LIGHT;
    }

    public function setThemeToLight(): void
    {
        $this->theme = self::THEME_LIGHT;
    }

    public function isThemeDark(): bool
    {
        return $this->theme === self::THEME_DARK;
    }

    public function setThemeToDark(): void
    {
        $this->theme = self::THEME_DARK;
    }

    /**
     * @throws Exception
     */
    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

}