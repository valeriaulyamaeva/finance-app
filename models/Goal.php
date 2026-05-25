<?php

declare(strict_types=1);

namespace app\models;

use app\models\queries\GoalQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $category_id
 * @property string $name
 * @property float $target_amount
 * @property string $currency
 * @property string $deadline
 * @property float $current_amount
 * @property string $status
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Transaction[] $transactions
 * @property User $user
 * @property Category|null $category
 */
final class Goal extends ActiveRecord
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public static function tableName(): string
    {
        return '{{%goal}}';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'name', 'target_amount', 'deadline'], 'required'],
            [['user_id', 'category_id'], 'integer'],
            [['target_amount', 'current_amount'], 'number', 'min' => 0],
            [['deadline'], 'date', 'format' => 'php:Y-m-d'],
            [['status'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 3],
            [['currency'], 'default', 'value' => 'BYN'],
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => array_keys(self::getStatuses())],
            [['category_id'], 'default', 'value' => null],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getProgress(): float
    {
        if ($this->target_amount <= 0) {
            return 0;
        }
        $percentage = ($this->current_amount / $this->target_amount) * 100;
        return round(min($percentage, 100), 2);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function updateStatus(): void
    {
        if ($this->current_amount >= $this->target_amount) {
            $this->status = self::STATUS_COMPLETED;
        } elseif ($this->status === self::STATUS_COMPLETED) {
            $this->status = self::STATUS_ACTIVE;
        }
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Активна',
            self::STATUS_COMPLETED => 'Выполнена',
            self::STATUS_FAILED => 'Провалена',
        ];
    }

    public function getStatusLabel(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'primary',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            default => 'secondary',
        };
    }

    public function getTransactions(): ActiveQuery
    {
        return $this->hasMany(Transaction::class, ['goal_id' => 'id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function find(): GoalQuery
    {
        return new GoalQuery(get_called_class());
    }
}