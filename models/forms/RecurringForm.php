<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\RecurringTransaction;
use yii\base\Model;

final class RecurringForm extends Model
{
    public mixed $amount;
    public mixed $currency = 'BYN';
    public mixed $frequency = RecurringTransaction::FREQUENCY_MONTHLY;
    public mixed $next_date;
    public mixed $category_id;
    public mixed $goal_id;
    public mixed $description;
    public mixed $active = 1;

    public function formName(): string { return ''; }

    public function rules(): array
    {
        return [
            [['amount', 'currency', 'frequency', 'next_date', 'category_id'], 'required'],
            [['goal_id', 'description'], 'default', 'value' => null],
            [['active'], 'default', 'value' => 1],
            [['category_id', 'goal_id', 'active'], 'integer'],
            [['amount'], 'number', 'min' => 0.01],
            [['next_date'], 'date', 'format' => 'php:Y-m-d'],
            [['frequency'], 'in', 'range' => array_keys(RecurringTransaction::optsFrequency())],
            [['description'], 'string', 'max' => 500],
            [['currency'], 'string', 'max' => 3],
        ];
    }
}