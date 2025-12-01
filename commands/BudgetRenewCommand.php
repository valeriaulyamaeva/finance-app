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

        $expiredBudgets = Budget::find()
            ->where(['end_date' => $yesterday])
            ->andWhere(['period' => ['monthly', 'yearly']])
            ->all();

        $created = 0;
        $deleted = 0;

        foreach ($expiredBudgets as $oldBudget) {
            $newBudget = $this->createNextBudget($oldBudget);

            if ($newBudget) {
                if ($oldBudget->delete()) {
                    $this->stdout("Удалён старый бюджет #{$oldBudget->id} и создан новый #{$newBudget->id}\n");
                    $deleted++;
                }
                $created++;
            }
        }

        $this->stdout("Обработано бюджетов: " . count($expiredBudgets) . " | Создано новых: $created | Удалено старых: $deleted\n");
        return ExitCode::OK;
    }

    private function createNextBudget(Budget $oldBudget): ?Budget
    {
        $newBudget = new Budget();
        $newBudget->user_id = $oldBudget->user_id;
        $newBudget->name = $oldBudget->name;
        $newBudget->amount = $oldBudget->amount;
        $newBudget->currency = $oldBudget->currency;
        $newBudget->category_id = $oldBudget->category_id;
        $newBudget->period = $oldBudget->period;

        $newBudget->start_date = (new DateTime($oldBudget->end_date))
            ->modify('+1 day')
            ->format('Y-m-d');

        if ($oldBudget->period === 'monthly') {
            $newBudget->end_date = (new DateTime($newBudget->start_date))
                ->modify('last day of this month')
                ->format('Y-m-d');
        } elseif ($oldBudget->period === 'yearly') {
            $newBudget->end_date = (new DateTime($newBudget->start_date))
                ->modify('last day of december this year')
                ->format('Y-m-d');
        }

        if ($newBudget->save()) {
            return $newBudget;
        }

        $this->stderr("Ошибка создания нового бюджета: " . json_encode($newBudget->errors) . "\n");
        return null;
    }
}