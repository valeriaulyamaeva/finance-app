<?php

declare(strict_types=1);

namespace app\models;

use app\models\queries\CategoryQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $type
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property RecurringTransaction[] $recurringTransactions
 * @property Transaction[] $transactions
 * @property User $user
 */
final class Category extends ActiveRecord
{
    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_GOAL = 'goal';

    public static function tableName(): string
    {
        return 'category';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'name', 'type'], 'required'],
            [['user_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'trim'],
            ['type', 'in', 'range' => array_keys(self::getTypes())],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_INCOME => Yii::t('app', 'Доход'),
            self::TYPE_EXPENSE => Yii::t('app', 'Расход'),
            self::TYPE_GOAL => Yii::t('app', 'Цель'),
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getTransactions(): ActiveQuery
    {
        return $this->hasMany(Transaction::class, ['category_id' => 'id']);
    }

    public static function find(): CategoryQuery
    {
        return new CategoryQuery(get_called_class());
    }

    public function isIncome(): bool
    {
        return $this->type === self::TYPE_INCOME;
    }

    public function isExpense(): bool
    {
        return $this->type === self::TYPE_EXPENSE;
    }
}