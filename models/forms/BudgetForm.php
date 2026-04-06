<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\Budget;
use yii\base\Model;

final class BudgetForm extends Model
{
    public ?string $name = null;
    public ?float $amount = null;
    public ?string $currency = 'BYN';
    public ?int $category_id = null;
    public ?string $period = Budget::PERIOD_MONTHLY;
    public ?string $start_date = null;
    public ?string $end_date = null;

    public function rules(): array
    {
        return [
            [['name', 'amount', 'currency', 'category_id', 'period', 'start_date'], 'required'],
            [['amount'], 'number', 'min' => 0.01],
            [['category_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 3],
            [['period'], 'in', 'range' => array_keys(Budget::getPeriods())],

            // Валидация дат
            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
            ['end_date', 'compare', 'compareAttribute' => 'start_date', 'operator' => '>=', 'enableClientValidation' => false, 'message' => 'Дата окончания не может быть раньше даты начала.'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'Название бюджета',
            'amount' => 'Лимит (сумма)',
            'currency' => 'Валюта',
            'category_id' => 'Категория',
            'period' => 'Период',
            'start_date' => 'Дата начала',
            'end_date' => 'Дата окончания',
        ];
    }

    public function setAttributesFromModel(Budget $model): void
    {
        $this->name = $model->name;
        $this->amount = $model->amount;
        $this->currency = $model->currency;
        $this->category_id = $model->category_id;
        $this->period = $model->period;
        $this->start_date = $model->start_date;
        $this->end_date = $model->end_date;
    }
}