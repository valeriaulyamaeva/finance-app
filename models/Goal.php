<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "goal".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property float $target_amount
 * @property string $deadline
 * @property float $current_amount
 * @property string $status
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property RecurringTransaction[] $recurringTransactions
 * @property Transaction[] $transactions
 * @property User $user
 */
class Goal extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'goal';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['current_amount'], 'default', 'value' => 0.00],
            [['status'], 'default', 'value' => 'active'],
            [['user_id', 'name', 'target_amount', 'deadline'], 'required'],
            [['user_id'], 'integer'],
            [['target_amount', 'current_amount'], 'number'],
            [['deadline', 'created_at', 'updated_at'], 'safe'],
            [['status'], 'string'],
            [['name'], 'string', 'max' => 255],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
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
            'name' => 'Name',
            'target_amount' => 'Target Amount',
            'deadline' => 'Deadline',
            'current_amount' => 'Current Amount',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[RecurringTransactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecurringTransactions()
    {
        return $this->hasMany(RecurringTransaction::class, ['goal_id' => 'id']);
    }

    /**
     * Gets query for [[Transactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(Transaction::class, ['goal_id' => 'id']);
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
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_ACTIVE => 'active',
            self::STATUS_COMPLETED => 'completed',
            self::STATUS_FAILED => 'failed',
        ];
    }

    /**
     * @return string
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * @return bool
     */
    public function isStatusActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function setStatusToActive()
    {
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function isStatusCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function setStatusToCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isStatusFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function setStatusToFailed()
    {
        $this->status = self::STATUS_FAILED;
    }
}
