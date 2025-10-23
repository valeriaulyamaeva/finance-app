<?php

namespace app\controllers;

use yii\db\Expression;
use yii\db\Query;
use yii\web\Controller;
use app\models\Budget;
use app\models\Transaction;
use app\services\CurrencyService;
use Yii;
use Exception;

class AnalyticsController extends Controller
{
    private CurrencyService $currencyService;

    public function __construct($id, $module, CurrencyService $currencyService, $config = [])
    {
        $this->currencyService = $currencyService;
        parent::__construct($id, $module, $config);
    }

    /**
     * @throws Exception
     */
    public function actionIndex(): string
    {
        $user = Yii::$app->user->identity;
        $userId = $user->id;
        $userCurrency = $user->currency ?? 'BYN';

        $currentMonth = date('Y-m');
        $startYearMonth = date('Y-m', strtotime('-11 months'));

        $totalSpent = Transaction::find()
            ->where(['user_id' => $userId, 'type' => 'expense'])
            ->andWhere(['like', 'date', $currentMonth])
            ->sum('amount');

        $totalBudget = Budget::find()
            ->where(['user_id' => $userId])
            ->andWhere(['<=', 'start_date', date('Y-m-t')])
            ->andWhere([
                'or',
                ['end_date' => null],
                ['>=', 'end_date', date('Y-m-01')]
            ])
            ->sum('amount');

        $remaining = $totalBudget - $totalSpent;

        $totalBudgetDisplay = $this->currencyService->fromBase(
            $this->currencyService->toBase($totalBudget, 'BYN'),
            $userCurrency
        );

        $totalSpentDisplay = $this->currencyService->fromBase(
            $this->currencyService->toBase($totalSpent, 'BYN'),
            $userCurrency
        );

        $remainingDisplay = $totalBudgetDisplay - $totalSpentDisplay;

        $categoryData = (new Query())
            ->select(['c.name AS category', 'SUM(t.amount) AS total'])
            ->from(['t' => 'transaction'])
            ->leftJoin(['c' => 'category'], 'c.id = t.category_id')
            ->where(['t.user_id' => $userId, 't.type' => 'expense'])
            ->andWhere(['like', 't.date', $currentMonth])
            ->groupBy('c.name')
            ->all();

        foreach ($categoryData as &$cat) {
            $cat['total'] = $this->currencyService->fromBase(
                $this->currencyService->toBase($cat['total'], 'BYN'),
                $userCurrency
            );
        }

        $monthlyDataQuery = (new Query())
            ->select([
                "DATE_FORMAT(t.date, '%Y-%m') AS month",
                "SUM(CASE WHEN t.type='expense' THEN t.amount ELSE 0 END) AS total_expense",
                "SUM(CASE WHEN t.type='income' THEN t.amount ELSE 0 END) AS total_income",
            ])
            ->from(['t' => 'transaction'])
            ->where(['t.user_id' => $userId])
            ->andWhere(['>=', 't.date', $startYearMonth . '-01'])
            ->groupBy(new Expression("DATE_FORMAT(t.date, '%Y-%m')"))
            ->orderBy(['month' => SORT_ASC])
            ->all();

        $months = [];
        $expenseValues = [];
        $incomeValues = [];
        foreach ($monthlyDataQuery as $row) {
            $months[] = $row['month'];
            $expenseValues[] = $this->currencyService->fromBase(
                $this->currencyService->toBase($row['total_expense'], 'BYN'),
                $userCurrency
            );
            $incomeValues[] = $this->currencyService->fromBase(
                $this->currencyService->toBase($row['total_income'], 'BYN'),
                $userCurrency
            );
        }

        $averageData = (new Query())
            ->select(['c.name AS category', 'AVG(t.amount) AS avg_amount'])
            ->from(['t' => 'transaction'])
            ->leftJoin(['c' => 'category'], 'c.id = t.category_id')
            ->where(['t.user_id' => $userId, 't.type' => 'expense'])
            ->andWhere(['like', 't.date', $currentMonth])
            ->groupBy('c.name')
            ->all();

        foreach ($averageData as &$avg) {
            $avg['avg_amount'] = $this->currencyService->fromBase(
                $this->currencyService->toBase($avg['avg_amount'], 'BYN'),
                $userCurrency
            );
        }

        $topCategories = (new Query())
            ->select(['c.name AS category', 'SUM(t.amount) AS total'])
            ->from(['t' => 'transaction'])
            ->leftJoin(['c' => 'category'], 'c.id = t.category_id')
            ->where(['t.user_id' => $userId, 't.type' => 'expense'])
            ->andWhere(['like', 't.date', $currentMonth])
            ->groupBy('c.name')
            ->orderBy(['total' => SORT_DESC])
            ->limit(5)
            ->all();

        foreach ($topCategories as &$top) {
            $top['total'] = $this->currencyService->fromBase(
                $this->currencyService->toBase($top['total'], 'BYN'),
                $userCurrency
            );
        }

        return $this->render('index', [
            'totalBudget' => $totalBudgetDisplay,
            'totalSpent' => $totalSpentDisplay,
            'remaining' => $remainingDisplay,
            'categoryData' => $categoryData,
            'months' => $months,
            'expenseValues' => $expenseValues,
            'incomeValues' => $incomeValues,
            'averageData' => $averageData,
            'topCategories' => $topCategories,
            'currencySymbol' => $this->getCurrencySymbol($userCurrency),
        ]);
    }

    private function getCurrencySymbol(string $currency): string
    {
        return match ($currency) {
            'BYN' => 'Br',
            'USD' => '$',
            'EUR' => '€',
            'RUB' => '₽',
            default => $currency,
        };
    }
}
