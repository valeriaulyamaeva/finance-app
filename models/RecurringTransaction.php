<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "recurring_transaction".
 *
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property string $frequency
 * @property string $next_date
 * @property int|null $category_id
 * @property int|null $budget_id
 * @property int|null $goal_id
 * @property string|null $description
 * @property int $active
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Budget $budget
 * @property Category $category
 * @property Goal $goal
 * @property Transaction[] $transactions
 * @property User $user
 */
class RecurringTransaction extends ActiveRecord
{
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';

    public static function tableName(): string
    {
        return 'recurring_transaction';
    }

    public function rules(): array
    {
        return [
            [['category_id', 'budget_id', 'goal_id', 'description'], 'default', 'value' => null],
            [['active'], 'default', 'value' => 1],
            [['user_id', 'amount', 'frequency', 'next_date'], 'required'],
            [['user_id', 'category_id', 'budget_id', 'goal_id', 'active'], 'integer'],
            [['amount'], 'number'],
            [['frequency', 'description'], 'string'],
            [['next_date', 'created_at', 'updated_at'], 'safe'],
            ['frequency', 'in', 'range' => array_keys(self::optsFrequency())],
            [['budget_id'], 'exist', 'skipOnError' => true, 'targetClass' => Budget::class, 'targetAttribute' => ['budget_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['goal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Goal::class, 'targetAttribute' => ['goal_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            ['currency', 'string', 'max' => 3],
            ['currency', 'default', 'value' => 'BYN'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
            'amount' => 'Сумма',
            'frequency' => 'Частота',
            'next_date' => 'Следующая дата',
            'category_id' => 'Категория',
            'budget_id' => 'Бюджет',
            'goal_id' => 'Цель',
            'description' => 'Описание',
            'active' => 'Активно',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    public function getBudget(): ActiveQuery
    {
        return $this->hasOne(Budget::class, ['id' => 'budget_id']);
    }

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getGoal(): ActiveQuery
    {
        return $this->hasOne(Goal::class, ['id' => 'goal_id']);
    }

    public function getTransactions(): ActiveQuery
    {
        return $this->hasMany(Transaction::class, ['recurring_id' => 'id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function optsFrequency(): array
    {
        return [
            self::FREQUENCY_DAILY => 'Ежедневно',
            self::FREQUENCY_WEEKLY => 'Еженедельно',
            self::FREQUENCY_MONTHLY => 'Ежемесячно',
        ];
    }

    public function displayFrequency(): string
    {
        return self::optsFrequency()[$this->frequency] ?? $this->frequency;
    }

    public function isFrequencyDaily(): bool
    {
        return $this->frequency === self::FREQUENCY_DAILY;
    }

    public function setFrequencyToDaily(): void
    {
        $this->frequency = self::FREQUENCY_DAILY;
    }

    public function isFrequencyWeekly(): bool
    {
        return $this->frequency === self::FREQUENCY_WEEKLY;
    }

    public function setFrequencyToWeekly(): void
    {
        $this->frequency = self::FREQUENCY_WEEKLY;
    }

    public function isFrequencyMonthly(): bool
    {
        return $this->frequency === self::FREQUENCY_MONTHLY;
    }

    public function setFrequencyToMonthly(): void
    {
        $this->frequency = self::FREQUENCY_MONTHLY;
    }
}