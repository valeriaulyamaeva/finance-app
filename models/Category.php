<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "category".
 *
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
class Category extends ActiveRecord
{

    /**
     * ENUM field values
     */
    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';
    const TYPE_GOAL = 'goal';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['type'], 'default', 'value' => 'expense'],
            [['user_id', 'name'], 'required'],
            [['user_id'], 'integer'],
            [['type'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            ['type', 'in', 'range' => array_keys(self::optsType())],
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
            'type' => 'Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[RecurringTransactions]].
     *
     * @return ActiveQuery
     */
    public function getRecurringTransactions(): ActiveQuery
    {
        return $this->hasMany(RecurringTransaction::class, ['category_id' => 'id']);
    }

    /**
     * Gets query for [[Transactions]].
     *
     * @return ActiveQuery
     */
    public function getTransactions(): ActiveQuery
    {
        return $this->hasMany(Transaction::class, ['category_id' => 'id']);
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
}
