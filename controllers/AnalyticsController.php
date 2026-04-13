<?php

namespace app\controllers;

use DateInterval;
use DatePeriod;
use DateTime;
use yii\db\Expression;
use yii\db\Query;
use app\models\Transaction;
use app\services\CurrencyService;
use Yii;
use Exception;

class AnalyticsController extends BaseController
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

        $request = Yii::$app->request;
        $period = $request->get('period', 'month');
        $customStart = $request->get('start');
        $customEnd = $request->get('end');

        [$startDate, $endDate] = $this->resolvePeriod($period, $customStart, $customEnd);
        [$prevStart, $prevEnd] = $this->getPreviousPeriod($startDate, $endDate);

        // Current period totals
        $totalIncome = $this->sumByType($userId, 'income', $startDate, $endDate, $userCurrency);
        $totalExpense = $this->sumByType($userId, 'expense', $startDate, $endDate, $userCurrency);
        $remaining = $totalIncome - $totalExpense;

        // Previous period totals for comparison
        $prevIncome = $this->sumByType($userId, 'income', $prevStart, $prevEnd, $userCurrency);
        $prevExpense = $this->sumByType($userId, 'expense', $prevStart, $prevEnd, $userCurrency);

        $incomeChange = $prevIncome > 0 ? round(($totalIncome - $prevIncome) / $prevIncome * 100, 1) : null;
        $expenseChange = $prevExpense > 0 ? round(($totalExpense - $prevExpense) / $prevExpense * 100, 1) : null;

        // Category breakdown
        $categoryData = $this->getCategoryData($userId, $startDate, $endDate, $userCurrency);

        // Monthly trends (12 months)
        $startYearMonth = date('Y-m', strtotime('-11 months'));
        [$months, $expenseValues, $incomeValues] = $this->getMonthlyTrends($userId, $startYearMonth, $userCurrency);

        // Average per category
        $averageData = $this->getAverageData($userId, $startDate, $endDate, $userCurrency);

        // Top 5 categories
        $topCategories = $this->getTopCategories($userId, $startDate, $endDate, $userCurrency);

        // Expenses by day of week
        $weekdayData = $this->getWeekdayExpenses($userId, $startDate, $endDate, $userCurrency);

        return $this->render('index', [
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'remaining' => $remaining,
            'prevIncome' => $prevIncome,
            'prevExpense' => $prevExpense,
            'incomeChange' => $incomeChange,
            'expenseChange' => $expenseChange,
            'categoryData' => $categoryData,
            'months' => $months,
            'expenseValues' => $expenseValues,
            'incomeValues' => $incomeValues,
            'averageData' => $averageData,
            'topCategories' => $topCategories,
            'weekdayLabels' => $weekdayData['labels'],
            'weekdayTotals' => $weekdayData['totals'],
            'weekdayCounts' => $weekdayData['counts'],
            'currencySymbol' => $this->getCurrencySymbol($userCurrency),
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    private function resolvePeriod(string $period, ?string $customStart, ?string $customEnd): array
    {
        return match ($period) {
            'quarter' => [
                date('Y-m-d', strtotime('first day of -2 months')),
                date('Y-m-t'),
            ],
            'year' => [
                date('Y-01-01'),
                date('Y-12-31'),
            ],
            'custom' => [
                $customStart ?: date('Y-m-01'),
                $customEnd ?: date('Y-m-t'),
            ],
            default => [date('Y-m-01'), date('Y-m-t')],
        };
    }

    private function getPreviousPeriod(string $startDate, string $endDate): array
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $diff = $start->diff($end);
        $days = $diff->days + 1;

        $prevEnd = (clone $start)->modify('-1 day');
        $prevStart = (clone $prevEnd)->modify("-" . ($days - 1) . " days");

        return [$prevStart->format('Y-m-d'), $prevEnd->format('Y-m-d')];
    }

    private function sumByType(int $userId, string $type, string $start, string $end, string $userCurrency): float
    {
        $total = (float)(Transaction::find()
            ->where(['user_id' => $userId, 'type' => $type])
            ->andWhere(['between', 'date', $start, $end])
            ->sum('amount') ?? 0);

        return round($this->currencyService->fromBase(
            $this->currencyService->toBase($total, 'BYN'),
            $userCurrency
        ), 2);
    }

    private function getCategoryData(int $userId, string $start, string $end, string $userCurrency): array
    {
        $data = (new Query())
            ->select(['c.name AS category', 'SUM(t.amount) AS total'])
            ->from(['t' => 'transaction'])
            ->leftJoin(['c' => 'category'], 'c.id = t.category_id')
            ->where(['t.user_id' => $userId, 't.type' => 'expense'])
            ->andWhere(['between', 't.date', $start, $end])
            ->groupBy('c.name')
            ->all();

        foreach ($data as &$cat) {
            $cat['total'] = round($this->currencyService->fromBase(
                $this->currencyService->toBase($cat['total'], 'BYN'),
                $userCurrency
            ), 2);
        }
        return $data;
    }

    private function getMonthlyTrends(int $userId, string $startYearMonth, string $userCurrency): array
    {
        $currentMonth = date('Y-m');

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

            $expenseValues[] = round($this->currencyService->fromBase(
                $this->currencyService->toBase($expense, 'BYN'), $userCurrency
            ), 2);
            $incomeValues[] = round($this->currencyService->fromBase(
                $this->currencyService->toBase($income, 'BYN'), $userCurrency
            ), 2);
        }

        return [$months, $expenseValues, $incomeValues];
    }

    private function getAverageData(int $userId, string $start, string $end, string $userCurrency): array
    {
        $data = (new Query())
            ->select(['c.name AS category', 'AVG(t.amount) AS avg_amount'])
            ->from(['t' => 'transaction'])
            ->leftJoin(['c' => 'category'], 'c.id = t.category_id')
            ->where(['t.user_id' => $userId, 't.type' => 'expense'])
            ->andWhere(['between', 't.date', $start, $end])
            ->groupBy('c.name')
            ->all();

        foreach ($data as &$avg) {
            $avg['avg_amount'] = round($this->currencyService->fromBase(
                $this->currencyService->toBase($avg['avg_amount'], 'BYN'), $userCurrency
            ), 2);
        }
        return $data;
    }

    private function getTopCategories(int $userId, string $start, string $end, string $userCurrency): array
    {
        $data = (new Query())
            ->select(['c.name AS category', 'SUM(t.amount) AS total'])
            ->from(['t' => 'transaction'])
            ->leftJoin(['c' => 'category'], 'c.id = t.category_id')
            ->where(['t.user_id' => $userId, 't.type' => 'expense'])
            ->andWhere(['between', 't.date', $start, $end])
            ->groupBy('c.name')
            ->orderBy(['total' => SORT_DESC])
            ->limit(5)
            ->all();

        foreach ($data as &$top) {
            $top['total'] = round($this->currencyService->fromBase(
                $this->currencyService->toBase($top['total'], 'BYN'), $userCurrency
            ), 2);
        }
        return $data;
    }

    private function getWeekdayExpenses(int $userId, string $start, string $end, string $userCurrency): array
    {
        // DAYOFWEEK: 1=Sun, 2=Mon, ..., 7=Sat
        $rows = (new Query())
            ->select([
                new Expression("DAYOFWEEK(t.date) AS dow"),
                "SUM(t.amount) AS total",
                "COUNT(*) AS cnt",
            ])
            ->from(['t' => 'transaction'])
            ->where(['t.user_id' => $userId, 't.type' => 'expense'])
            ->andWhere(['between', 't.date', $start, $end])
            ->groupBy(new Expression("DAYOFWEEK(t.date)"))
            ->all();

        $dayNames = [2 => 'Пн', 3 => 'Вт', 4 => 'Ср', 5 => 'Чт', 6 => 'Пт', 7 => 'Сб', 1 => 'Вс'];
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int)$row['dow']] = $row;
        }

        $labels = [];
        $totals = [];
        $counts = [];
        foreach ($dayNames as $dow => $name) {
            $labels[] = $name;
            $raw = (float)($indexed[$dow]['total'] ?? 0);
            $totals[] = round($this->currencyService->fromBase(
                $this->currencyService->toBase($raw, 'BYN'), $userCurrency
            ), 2);
            $counts[] = (int)($indexed[$dow]['cnt'] ?? 0);
        }

        return ['labels' => $labels, 'totals' => $totals, 'counts' => $counts];
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
