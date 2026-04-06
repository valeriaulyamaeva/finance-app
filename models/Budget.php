<?php

declare(strict_types=1);

namespace app\models;

use app\models\queries\BudgetQuery;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $category_id
 * @property string $name
 * @property float $amount
 * @property string $currency
 * @property string $period
 * @property string $start_date
 * @property string|null $end_date
 * @property float $spent
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 * @property Category|null $category
 */
final class Budget extends ActiveRecord
{
    public const PERIOD_DAILY = 'daily';
    public const PERIOD_WEEKLY = 'weekly';
    public const PERIOD_MONTHLY = 'monthly';
    public const PERIOD_YEARLY = 'yearly';

    public static function tableName(): string
    {
        return 'budget';
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
                    'spent' => AttributeTypecastBehavior::TYPE_FLOAT,
                    'user_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                    'category_id' => AttributeTypecastBehavior::TYPE_INTEGER,
                ],
                'typecastAfterFind' => true,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'name', 'amount', 'start_date', 'currency'], 'required'],
            [['user_id', 'category_id'], 'integer'],
            [['amount', 'spent'], 'number', 'min' => 0],
            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
            [['name'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 3],
            ['period', 'in', 'range' => array_keys(self::getPeriods())],
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public static function getPeriods(): array
    {
        return [
            self::PERIOD_DAILY => Yii::t('app', 'День'),
            self::PERIOD_WEEKLY => Yii::t('app', 'Неделя'),
            self::PERIOD_MONTHLY => Yii::t('app', 'Месяц'),
            self::PERIOD_YEARLY => Yii::t('app', 'Год'),
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

    public function getProgressPercentage(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }
        $percentage = ($this->spent / $this->amount) * 100;
        return round(min($percentage, 100), 2);
    }

    public function getRemainingAmount(): float
    {
        return max(0, $this->amount - $this->spent);
    }

    public function isExceeded(): bool
    {
        return $this->spent > $this->amount;
    }

    public static function find(): BudgetQuery
    {
        return new BudgetQuery(get_called_class());
    }

    public function displayPeriod(): string
    {
        return self::getPeriods()[$this->period] ?? $this->period;
    }
}