<?php

declare(strict_types=1);

namespace app\commands;

use app\models\Budget;
use app\services\BudgetService;
use yii\console\Controller;
use yii\console\ExitCode;
use DateTime;
use Yii;

class BudgetRenewCommand extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly BudgetService $budgetService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionIndex(): int
    {
        $today = date('Y-m-d');

        $expiredBudgets = Budget::find()
            ->where(['<', 'end_date', $today])
            ->andWhere(['not', ['end_date' => null]])
            ->all();

        $count = 0;
        foreach ($expiredBudgets as $oldBudget) {
            $alreadyExists = Budget::find()
                ->where([
                    'user_id' => $oldBudget->user_id,
                    'category_id' => $oldBudget->category_id,
                    'start_date' => $this->calculateNextStartDate($oldBudget->end_date)
                ])->exists();

            if ($alreadyExists) {
                continue;
            }

            if ($this->renew($oldBudget)) {
                $count++;
            }
        }

        $this->stdout("Автопродление завершено. Создано новых бюджетов: $count\n");
        return ExitCode::OK;
    }

    private function renew(Budget $oldBudget): bool
    {
        $newBudget = new Budget();
        $newBudget->attributes = $oldBudget->attributes;
        $newBudget->id = null;
        $newBudget->spent = 0;

        $newBudget->start_date = $this->calculateNextStartDate($oldBudget->end_date);
        $newBudget->end_date = $this->calculateNextEndDate($newBudget->start_date, $oldBudget->period);

        if ($newBudget->save()) {
            $this->budgetService->refreshSpentAmount($newBudget);
            return true;
        }

        $this->stderr("Ошибка при продлении бюджета #{$oldBudget->id}: " . json_encode($newBudget->errors) . "\n");
        return false;
    }

    private function calculateNextStartDate(string $oldEndDate): string
    {
        return (new DateTime($oldEndDate))->modify('+1 day')->format('Y-m-d');
    }

    private function calculateNextEndDate(string $startDate, string $period): string
    {
        $dt = new DateTime($startDate);
        return match ($period) {
            Budget::PERIOD_DAILY   => $dt->format('Y-m-d'),
            Budget::PERIOD_WEEKLY  => $dt->modify('+6 days')->format('Y-m-d'),
            Budget::PERIOD_MONTHLY => $dt->modify('last day of this month')->format('Y-m-d'),
            Budget::PERIOD_YEARLY  => $dt->modify('last day of december this year')->format('Y-m-d'),
            default                => $dt->modify('+1 month')->format('Y-m-d'),
        };
    }
}