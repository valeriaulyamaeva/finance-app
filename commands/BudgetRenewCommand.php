<?php

namespace app\commands;

use app\models\Budget;
use yii\console\Controller;
use yii\console\ExitCode;
use DateTime;
class BudgetRenewCommand extends Controller
{
    public function actionIndex(): int
    {
        $yesterday = (new DateTime())->modify('-1 day')->format('Y-m-d');

        $budgets = Budget::find()
            ->where(['end_date' => $yesterday])
            ->andWhere(['period' => ['monthly', 'yearly']])
            ->all();

        foreach ($budgets as $budget) {
            $this->createNextBudget($budget);
        }

        $this->stdout("Проверено бюджетов: " . count($budgets) . "\n");
        return ExitCode::OK;
    }

    private function createNextBudget(Budget $budget): void
    {
        $newBudget = new Budget();
        $newBudget->user_id = $budget->user_id;
        $newBudget->name = $budget->name;
        $newBudget->amount = $budget->amount;
        $newBudget->currency = $budget->currency;
        $newBudget->category_id = $budget->category_id;
        $newBudget->period = $budget->period;

        $newBudget->start_date = (new DateTime($budget->end_date))->modify('+1 day')->format('Y-m-d');

        if ($budget->period === 'monthly') {
            $newBudget->end_date = (new DateTime($newBudget->start_date))
                ->modify('last day of this month')
                ->format('Y-m-d');
        } elseif ($budget->period === 'yearly') {
            $newBudget->end_date = (new DateTime($newBudget->start_date))
                ->modify('last day of december this year')
                ->format('Y-m-d');
        }

        if ($newBudget->save()) {
            $this->stdout("Создан бюджет #$newBudget->id: $newBudget->start_date — $newBudget->end_date\n");
        } else {
            $this->stderr("Ошибка создания бюджета: " . json_encode($newBudget->errors) . "\n");
        }
    }
}