<?php

declare(strict_types=1);

namespace app\models;

use app\models\queries\TransactionQuery;
use Yii;
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
 * @property string $date
 * @property string $type
 * @property int|null $category_id
 * @property int|null $budget_id
 * @property int|null $goal_id
 * @property int|null $recurring_id
 * @property string|null $description
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 * @property Category|null $category
 * @property Budget|null $budget
 * @property Goal|null $goal
 */
final class Transaction extends ActiveRecord
{
    public const TYPE_INCOME = 'income';
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_GOAL = 'goal';

    public static function tableName(): string
    {
        return 'transaction';
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
                    'category_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'budget_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'goal_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                ],
                'typecastAfterFind' => true,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'amount', 'date', 'currency', 'type'], 'required'],
            [['user_id', 'category_id', 'budget_id', 'goal_id', 'recurring_id'], 'integer'],

            [['category_id', 'budget_id', 'goal_id', 'recurring_id', 'description'], 'default', 'value' => null],

            [['amount'], 'number', 'min' => 0.01],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['currency'], 'string', 'max' => 3],
            [['description'], 'string', 'max' => 500],
            [['type'], 'in', 'range' => array_keys(self::getTypes())],

            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id'],
            [['budget_id'], 'exist', 'targetClass' => Budget::class, 'targetAttribute' => 'id'],
            [['goal_id'], 'exist', 'targetClass' => Goal::class, 'targetAttribute' => 'id'],
        ];
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_INCOME => Yii::t('app', 'Доход'),
            self::TYPE_EXPENSE => Yii::t('app', 'Расход'),
            self::TYPE_GOAL => Yii::t('app', 'Цель/Накопление'),
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getBudget(): ActiveQuery
    {
        return $this->hasOne(Budget::class, ['id' => 'budget_id']);
    }

    public function getGoal(): ActiveQuery
    {
        return $this->hasOne(Goal::class, ['id' => 'goal_id']);
    }

    public function formatAmount(): string
    {
        return number_format($this->amount, 2, '.', ' ');
    }

    public static function find(): TransactionQuery
    {
        return new TransactionQuery(get_called_class());
    }
}