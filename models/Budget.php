<?php

namespace app\models;


use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property float $amount
 * @property string $currency
 * @property string $period
 * @property string $start_date
 * @property string|null $end_date
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $category_id
 * @property float $spent
 *
 * @property RecurringTransaction[] $recurringTransactions
 * @property Transaction[] $transactions
 * @property User $user
 * @property Category $category
 */
class Budget extends ActiveRecord
{

    /**
     * ENUM field values
     */
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_YEARLY = 'yearly';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'budget';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['end_date'], 'default', 'value' => null],
            [['period'], 'default', 'value' => 'monthly'],
            [['user_id', 'category_id'], 'integer'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['user_id', 'name', 'amount', 'start_date', 'category_id'], 'required'],
            [['amount'], 'number'],
            ['currency', 'string', 'max' => 3],
            ['currency', 'default', 'value' => 'BYN'],
            [['currency'], 'safe'],
            [['period'], 'string'],
            [['start_date', 'end_date', 'created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            ['period', 'in', 'range' => array_keys(self::optsPeriod())],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'name' => 'Name',
            'amount' => 'Amount',
            'currency' => 'Currency',
            'category_id' => 'Category',
            'period' => 'Period',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'spent' => 'Потрачено',

        ];
    }

    /**
     * Gets query for [[RecurringTransactions]].
     *
     * @return ActiveQuery
     */
    public function getRecurringTransactions(): ActiveQuery
    {
        return $this->hasMany(RecurringTransaction::class, ['budget_id' => 'id']);
    }

    /**
     * Gets query for [[Transactions]].
     *
     * @return ActiveQuery
     */
    public function getTransactions(): ActiveQuery
    {
        return $this->hasMany(Transaction::class, ['budget_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }


    /**
     * column period ENUM value labels
     * @return string[]
     */
    public static function optsPeriod(): array
    {
        return [
            'daily' => 'День',
            'weekly' => 'Неделя',
            'monthly' => 'Месяц',
            'yearly' => 'Год',
        ];
    }


    /**
     * @return string
     */
    public function displayPeriod(): string
    {
        return self::optsPeriod()[$this->period];
    }

    /**
     * @return bool
     */
    public function isPeriodMonthly(): bool
    {
        return $this->period === self::PERIOD_MONTHLY;
    }

    public function setPeriodToMonthly(): void
    {
        $this->period = self::PERIOD_MONTHLY;
    }

    /**
     * @return bool
     */
    public function isPeriodYearly(): bool
    {
        return $this->period === self::PERIOD_YEARLY;
    }

    public function setPeriodToYearly(): void
    {
        $this->period = self::PERIOD_YEARLY;
    }

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }
}
