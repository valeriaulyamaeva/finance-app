<?php

namespace app\controllers;

use DateInterval;
use DatePeriod;
use DateTime;
use yii\db\Expression;
use yii\db\Query;
use yii\web\Controller;
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

        $totalIncome = Transaction::find()
            ->where(['user_id' => $userId, 'type' => 'income'])
            ->andWhere(['like', 'date', $currentMonth])
            ->sum('amount');

        $totalExpense = Transaction::find()
            ->where(['user_id' => $userId, 'type' => 'expense'])
            ->andWhere(['like', 'date', $currentMonth])
            ->sum('amount');

        $remaining = $totalIncome - $totalExpense;

        $totalIncomeDisplay = $this->currencyService->fromBase(
            $this->currencyService->toBase($totalIncome, 'BYN'),
            $userCurrency
        );

        $totalExpenseDisplay = $this->currencyService->fromBase(
            $this->currencyService->toBase($totalExpense, 'BYN'),
            $userCurrency
        );

        $remainingDisplay = $totalIncomeDisplay - $totalExpenseDisplay;

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

        $period = new DatePeriod(
            new DateTime($startYearMonth . '-01'),
            new DateInterval('P1M'),
            (new DateTime($currentMonth . '-01'))->modify('+1 month')
        );

        $monthlyIndexed = [];
        foreach ($monthlyDataQuery as $row) {
            $monthlyIndexed[$row['month']] = $row;
        }

        foreach ($period as $date) {
            $monthKey = $date->format('Y-m');
            $months[] = $date->format('M Y');
            $expense = $monthlyIndexed[$monthKey]['total_expense'] ?? 0;
            $income = $monthlyIndexed[$monthKey]['total_income'] ?? 0;

            $expenseValues[] = $this->currencyService->fromBase(
                $this->currencyService->toBase($expense, 'BYN'),
                $userCurrency
            );
            $incomeValues[] = $this->currencyService->fromBase(
                $this->currencyService->toBase($income, 'BYN'),
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
            'totalIncome' => $totalIncomeDisplay,
            'totalExpense' => $totalExpenseDisplay,
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
