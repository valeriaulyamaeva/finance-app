<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\Transaction;
use yii\base\Model;

final class TransactionForm extends Model
{
    public mixed $amount = null;
    public mixed $currency = 'BYN';
    public mixed  $date = null;
    public mixed $type = Transaction::TYPE_EXPENSE;
    public mixed $category_id = null;
    public mixed $budget_id = null;
    public mixed $goal_id = null;
    public mixed $description = null;

    public function init(): void
    {
        parent::init();
        if ($this->date === null) {
            $this->date = date('Y-m-d');
        }
    }

    public function formName(): string
    {
        return '';
    }

    public function rules(): array
    {
        return [
            [['amount', 'currency', 'date', 'type', 'category_id'], 'required'],
            [['category_id', 'budget_id', 'goal_id', 'description'], 'default', 'value' => null],

            [['amount'], 'number', 'min' => 0.01],
            [['category_id', 'budget_id', 'goal_id'], 'integer'],
            [['currency'], 'string', 'max' => 3],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['description'], 'string', 'max' => 500],
            [['type'], 'in', 'range' => array_keys(Transaction::getTypes())],
            ['goal_id', 'required', 'when' => function($model) {
                return $model->type === Transaction::TYPE_GOAL;
            }, 'enableClientValidation' => false],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'amount' => 'Сумма',
            'currency' => 'Валюта',
            'date' => 'Дата',
            'type' => 'Тип операции',
            'category_id' => 'Категория',
            'budget_id' => 'Бюджет',
            'goal_id' => 'Цель',
            'description' => 'Комментарий',
        ];
    }

    public function setAttributesFromModel(Transaction $model): void
    {
        $this->amount = $model->amount;
        $this->currency = $model->currency;
        $this->date = $model->date;
        $this->type = $model->type;
        $this->category_id = $model->category_id;
        $this->budget_id = $model->budget_id;
        $this->goal_id = $model->goal_id;
        $this->description = $model->description;
    }
}