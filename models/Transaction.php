<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "transaction".
 *
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property string $currency
 * @property string $date
 * @property string $type
 * @property int|null $category_id
 * @property int|null $budget_id
 * @property int|null $goal_id
 * @property string|null $description
 * @property int|null $recurring_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Budget $budget
 * @property Category $category
 * @property Goal $goal
 * @property RecurringTransaction $recurring
 * @property User $user
 */
class Transaction extends ActiveRecord
{

    /**
     * ENUM field values
     */
    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';
    const TYPE_GOAL = 'goal';
    public ?string $display_amount = null;
    public ?string $display_currency = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'transaction';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['category_id', 'budget_id', 'goal_id', 'description', 'recurring_id'], 'default', 'value' => null],
            [['type'], 'default', 'value' => 'expense'],
            [['user_id', 'amount', 'date'], 'required'],
            [['user_id', 'category_id', 'budget_id', 'goal_id', 'recurring_id'], 'integer'],
            [['amount'], 'number'],
            ['currency', 'string', 'max' => 3],
            [['currency'], 'safe'],
            [['date', 'created_at', 'updated_at'], 'safe'],
            [['type', 'description'], 'string'],
            ['type', 'in', 'range' => array_keys(self::optsType())],
            [['budget_id'], 'exist', 'skipOnError' => true, 'targetClass' => Budget::class, 'targetAttribute' => ['budget_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['goal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Goal::class, 'targetAttribute' => ['goal_id' => 'id']],
            [['recurring_id'], 'exist', 'skipOnError' => true, 'targetClass' => RecurringTransaction::class, 'targetAttribute' => ['recurring_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['goal_id'], 'required', 'when' => function ($model) {
                return $model->category_id && Category::findOne($model->category_id)?->type === 'goal';
            }, 'message' => 'Выберите цель для категории типа "goal".'],
            ['currency', 'string', 'max' => 3],
            ['currency', 'default', 'value' => 'BYN'],
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
            'amount' => 'Amount',
            'currency' => 'Currency',
            'date' => 'Date',
            'type' => 'Type',
            'category_id' => 'Category ID',
            'budget_id' => 'Budget ID',
            'goal_id' => 'Goal ID',
            'description' => 'Description',
            'recurring_id' => 'Recurring ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Budget]].
     *
     * @return ActiveQuery
     */
    public function getBudget(): ActiveQuery
    {
        return $this->hasOne(Budget::class, ['id' => 'budget_id']);
    }

    /**
     * Gets query for [[Category]].
     *
     * @return ActiveQuery
     */
    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[Goal]].
     *
     * @return ActiveQuery
     */
    public function getGoal(): ActiveQuery
    {
        return $this->hasOne(Goal::class, ['id' => 'goal_id']);
    }

    /**
     * Gets query for [[Recurring]].
     *
     * @return ActiveQuery
     */
    public function getRecurring(): ActiveQuery
    {
        return $this->hasOne(RecurringTransaction::class, ['id' => 'recurring_id']);
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
     * column type ENUM value labels
     * @return string[]
     */
    public static function optsType(): array
    {
        return [
            self::TYPE_INCOME => 'income',
            self::TYPE_EXPENSE => 'expense',
            self::TYPE_GOAL => 'goal',
        ];
    }

    /**
     * @return string
     */
    public function displayType(): string
    {
        return self::optsType()[$this->type];
    }

    /**
     * @return bool
     */
    public function isTypeIncome(): bool
    {
        return $this->type === self::TYPE_INCOME;
    }

    public function setTypeToIncome(): void
    {
        $this->type = self::TYPE_INCOME;
    }

    /**
     * @return bool
     */
    public function isTypeExpense(): bool
    {
        return $this->type === self::TYPE_EXPENSE;
    }

    public function setTypeToExpense(): void
    {
        $this->type = self::TYPE_EXPENSE;
    }

    /**
     * @return bool
     */
    public function isTypeGoal(): bool
    {
        return $this->type === self::TYPE_GOAL;
    }

    public function setTypeToGoal(): void
    {
        $this->type = self::TYPE_GOAL;
    }

    public function getDisplayAmount(): string
    {
        return number_format($this->amount, 2, '.', '');
    }


    public function getRecurringTransaction(): ActiveQuery
    {
        return $this->hasOne(RecurringTransaction::class, ['id' => 'recurring_id']);
    }

}
