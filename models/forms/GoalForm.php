<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\Goal;
use yii\base\Model;

final class GoalForm extends Model
{
    public ?string $name = null;
    public ?float $target_amount = null;
    public ?string $currency = null;
    public ?string $deadline = null;
    public ?int $category_id = null;
    public ?float $current_amount = null;

    public function rules(): array
    {
        return [
            [['name', 'target_amount', 'deadline'], 'required'],
            ['name', 'string', 'max' => 255],
            ['target_amount', 'number', 'min' => 0.01],
            ['current_amount', 'number', 'min' => 0],
            ['currency', 'string', 'max' => 3],
            ['category_id', 'integer'],
            [['category_id', 'current_amount'], 'default', 'value' => null],
            ['deadline', 'date', 'format' => 'php:Y-m-d'],
            ['deadline', 'validateDeadline'],
        ];
    }

    public function validateDeadline($attribute): void
    {
        if (strtotime($this->$attribute) < strtotime(date('Y-m-d'))) {
            $this->addError($attribute, 'Дедлайн не может быть в прошлом.');
        }
    }

    public function setFromModel(Goal $goal): void
    {
        $this->name = $goal->name;
        $this->target_amount = $goal->target_amount;
        $this->currency = $goal->currency;
        $this->deadline = $goal->deadline;
        $this->category_id = $goal->category_id;
        $this->current_amount = (float)$goal->current_amount;
    }
}