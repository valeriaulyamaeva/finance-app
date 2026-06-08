<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\Investment;
use yii\base\Model;

final class InvestmentForm extends Model
{
    public mixed $name = null;
    public mixed $type = Investment::TYPE_OTHER;
    public mixed $ticker = null;
    public mixed $quantity = null;
    public mixed $invested_amount = null;
    public mixed $current_value = null;
    public mixed $currency = 'BYN';
    public mixed $purchase_date = null;
    public mixed $note = null;

    public function formName(): string
    {
        return 'Investment';
    }

    public function rules(): array
    {
        return [
            [['name', 'type', 'invested_amount'], 'required'],
            ['name', 'string', 'max' => 255],
            ['ticker', 'string', 'max' => 20],
            ['note', 'string', 'max' => 500],
            [['invested_amount', 'current_value', 'quantity'], 'number', 'min' => 0],
            ['currency', 'string', 'max' => 3],
            ['type', 'in', 'range' => array_keys(Investment::getTypes())],
            ['purchase_date', 'date', 'format' => 'php:Y-m-d'],
            [['current_value', 'purchase_date', 'note', 'ticker', 'quantity'], 'default', 'value' => null],
        ];
    }

    public function setFromModel(Investment $m): void
    {
        $this->name = $m->name;
        $this->type = $m->type;
        $this->ticker = $m->ticker;
        $this->quantity = $m->quantity;
        $this->invested_amount = $m->invested_amount;
        $this->current_value = $m->current_value;
        $this->currency = $m->currency;
        $this->purchase_date = $m->purchase_date;
        $this->note = $m->note;
    }
}
