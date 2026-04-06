<?php

declare(strict_types=1);

namespace app\models;

use app\models\queries\RecurringTransactionQuery;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property string $currency
 * @property string $frequency
 * @property string $next_date
 * @property int|null $category_id
 * @property int|null $budget_id
 * @property int|null $goal_id
 * @property string|null $description
 * @property int $active
 * @property string $created_at
 * @property string $updated_at
 */
final class RecurringTransaction extends ActiveRecord
{
    public const FREQUENCY_DAILY = 'daily';
    public const FREQUENCY_WEEKLY = 'weekly';
    public const FREQUENCY_MONTHLY = 'monthly';

    public static function tableName(): string
    {
        return 'recurring_transaction';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'amount' => AttributeTypecastBehavior::TYPE_FLOAT,
                    'active' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'category_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'goal_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                ],
                'typecastAfterFind' => true,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'amount', 'currency', 'frequency', 'next_date'], 'required'],
            [['user_id', 'category_id', 'budget_id', 'goal_id', 'active'], 'integer'],
            [['category_id', 'budget_id', 'goal_id', 'description'], 'default', 'value' => null],
            [['active'], 'default', 'value' => 1],
            [['amount'], 'number', 'min' => 0.01],
            [['currency'], 'string', 'max' => 3],
            [['frequency'], 'in', 'range' => array_keys(self::optsFrequency())],
            [['next_date'], 'date', 'format' => 'php:Y-m-d'],
            [['description'], 'string', 'max' => 500],

            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id'],
            [['goal_id'], 'exist', 'targetClass' => Goal::class, 'targetAttribute' => 'id'],
        ];
    }

    public static function optsFrequency(): array
    {
        return [
            self::FREQUENCY_DAILY => 'Ежедневно',
            self::FREQUENCY_WEEKLY => 'Еженедельно',
            self::FREQUENCY_MONTHLY => 'Ежемесячно',
        ];
    }

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getGoal(): ActiveQuery
    {
        return $this->hasOne(Goal::class, ['id' => 'goal_id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getTransactions(): ActiveQuery
    {
        return $this->hasMany(Transaction::class, ['recurring_id' => 'id']);
    }

    public static function find(): RecurringTransactionQuery
    {
        return new RecurringTransactionQuery(get_called_class());
    }

    public function getBudget(): ActiveQuery
    {
        return $this->hasOne(Budget::class, ['id' => 'budget_id']);
    }
}