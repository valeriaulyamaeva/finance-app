<?php

namespace app\models;

use Yii;

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
class RecurringTransaction extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recurring_transaction';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'amount' => 'Amount',
            'frequency' => 'Frequency',
            'next_date' => 'Next Date',
            'category_id' => 'Category ID',
            'budget_id' => 'Budget ID',
            'goal_id' => 'Goal ID',
            'description' => 'Description',
            'active' => 'Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Budget]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBudget()
    {
        return $this->hasOne(Budget::class, ['id' => 'budget_id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[Goal]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGoal()
    {
        return $this->hasOne(Goal::class, ['id' => 'goal_id']);
    }

    /**
     * Gets query for [[Transactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(Transaction::class, ['recurring_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }


    /**
     * column frequency ENUM value labels
     * @return string[]
     */
    public static function optsFrequency()
    {
        return [
            self::FREQUENCY_DAILY => 'daily',
            self::FREQUENCY_WEEKLY => 'weekly',
            self::FREQUENCY_MONTHLY => 'monthly',
        ];
    }

    /**
     * @return string
     */
    public function displayFrequency()
    {
        return self::optsFrequency()[$this->frequency];
    }

    /**
     * @return bool
     */
    public function isFrequencyDaily()
    {
        return $this->frequency === self::FREQUENCY_DAILY;
    }

    public function setFrequencyToDaily()
    {
        $this->frequency = self::FREQUENCY_DAILY;
    }

    /**
     * @return bool
     */
    public function isFrequencyWeekly()
    {
        return $this->frequency === self::FREQUENCY_WEEKLY;
    }

    public function setFrequencyToWeekly()
    {
        $this->frequency = self::FREQUENCY_WEEKLY;
    }

    /**
     * @return bool
     */
    public function isFrequencyMonthly()
    {
        return $this->frequency === self::FREQUENCY_MONTHLY;
    }

    public function setFrequencyToMonthly()
    {
        $this->frequency = self::FREQUENCY_MONTHLY;
    }
}
