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
 * @property string|null $related_type
 * @property int|null $related_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
class Notification extends ActiveRecord
{
    const string TYPE_BUDGET_EXCEED = 'budget_exceed';
    const string TYPE_GOAL_REACHED = 'goal_reached';
    const string TYPE_REMINDER = 'reminder';
    const string TYPE_OTHER = 'other';

    public static function tableName(): string
    {
        return 'notification';
    }

    public function rules(): array
    {
        return [
            [['read_status'], 'default', 'value' => 0],
            [['user_id', 'message', 'type'], 'required'],
            [['user_id', 'read_status', 'related_id'], 'integer'],
            [['message', 'type', 'related_type'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            ['type', 'in', 'range' => array_keys(self::optsType())],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'message' => 'Message',
            'type' => 'Type',
            'read_status' => 'Read Status',
            'related_type' => 'Related Type',
            'related_id' => 'Related ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function optsType(): array
    {
        return [
            self::TYPE_BUDGET_EXCEED => 'budget_exceed',
            self::TYPE_GOAL_REACHED => 'goal_reached',
            self::TYPE_REMINDER => 'reminder',
            self::TYPE_OTHER => 'other',
        ];
    }

    public function displayType(): string
    {
        return self::optsType()[$this->type] ?? $this->type;
    }

    public function isTypeBudgetExceed(): bool
    {
        return $this->type === self::TYPE_BUDGET_EXCEED;
    }
    public function setTypeToBudgetExceed(): void
    {
        $this->type = self::TYPE_BUDGET_EXCEED;
    }

    public function isTypeGoalReached(): bool
    {
        return $this->type === self::TYPE_GOAL_REACHED;
    }
    public function setTypeToGoalReached(): void
    {
        $this->type = self::TYPE_GOAL_REACHED;
    }

    public function isTypeReminder(): bool
    {
        return $this->type === self::TYPE_REMINDER;
    }
    public function setTypeToReminder(): void
    {
        $this->type = self::TYPE_REMINDER;
    }

    public function isTypeOther(): bool
    {
        return $this->type === self::TYPE_OTHER;
    }
    public function setTypeToOther(): void
    {
        $this->type = self::TYPE_OTHER;
    }

    public static function createForUser(int $userId, string $message, string $type = self::TYPE_OTHER): self
    {
        $notification = new self();
        $notification->user_id = $userId;
        $notification->message = $message;
        $notification->type = $type;
        $notification->read_status = 0;
        $notification->save(false);
        return $notification;
    }
}