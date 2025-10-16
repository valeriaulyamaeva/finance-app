<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "notification".
 *
 * @property int $id
 * @property int $user_id
 * @property string $message
 * @property string $type
 * @property int $read_status
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
class Notification extends ActiveRecord
{

    /**
     * ENUM field values
     */
    const string TYPE_BUDGET_EXCEED = 'budget_exceed';
    const string TYPE_GOAL_REACHED = 'goal_reached';
    const string TYPE_REMINDER = 'reminder';
    const string TYPE_OTHER = 'other';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'notification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['read_status'], 'default', 'value' => 0],
            [['user_id', 'message', 'type'], 'required'],
            [['user_id', 'read_status'], 'integer'],
            [['message', 'type'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
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
            'message' => 'Message',
            'type' => 'Type',
            'read_status' => 'Read Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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
            self::TYPE_BUDGET_EXCEED => 'budget_exceed',
            self::TYPE_GOAL_REACHED => 'goal_reached',
            self::TYPE_REMINDER => 'reminder',
            self::TYPE_OTHER => 'other',
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
    public function isTypeBudgetexceed(): bool
    {
        return $this->type === self::TYPE_BUDGET_EXCEED;
    }

    public function setTypeToBudgetexceed(): void
    {
        $this->type = self::TYPE_BUDGET_EXCEED;
    }

    /**
     * @return bool
     */
    public function isTypeGoalreached(): bool
    {
        return $this->type === self::TYPE_GOAL_REACHED;
    }

    public function setTypeToGoalreached(): void
    {
        $this->type = self::TYPE_GOAL_REACHED;
    }

    /**
     * @return bool
     */
    public function isTypeReminder(): bool
    {
        return $this->type === self::TYPE_REMINDER;
    }

    public function setTypeToReminder(): void
    {
        $this->type = self::TYPE_REMINDER;
    }

    /**
     * @return bool
     */
    public function isTypeOther(): bool
    {
        return $this->type === self::TYPE_OTHER;
    }

    public function setTypeToOther(): void
    {
        $this->type = self::TYPE_OTHER;
    }
}
