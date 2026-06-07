<?php

declare(strict_types=1);

namespace app\services;

use app\models\Goal;
use app\models\Transaction;
use DateTime;
use yii\db\Query;

/**
 * Statistical forecasting based on historical transactions.
 * No ML — simple moving averages and linear extrapolation,
 * which is robust for personal finance horizons (1-3 months).
 */
final readonly class ForecastService
{
    private const HISTORY_DAYS = 90;

    public function __construct(
        private CurrencyService $currencyService,
    ) {}

    /**
     * Forecast end-of-month balance.
     *
     * Returns:
     *  - currentBalance: balance today
     *  - projectedExpense: expected additional spend until month end
     *  - projectedIncome: expected additional income until month end
     *  - projectedBalance: balance at month end
     *  - avgDailyExpense / avgDailyIncome: history-based averages
     *  - daysLeft
     */
    public function forecastMonthEnd(int $userId, string $userCurrency): array
    {
        [$avgDailyExpense, $avgDailyIncome] = $this->getDailyAverages($userId, $userCurrency);

        $today = new DateTime();
        $endOfMonth = new DateTime(date('Y-m-t'));
        $daysLeft = max(0, (int)$today->diff($endOfMonth)->format('%a'));

        $currentBalance = $this->getCurrentBalance($userId, $userCurrency);

        $projectedExpense = round($avgDailyExpense * $daysLeft, 2);
        $projectedIncome = round($avgDailyIncome * $daysLeft, 2);
        $projectedBalance = round($currentBalance + $projectedIncome - $projectedExpense, 2);

        return [
            'currentBalance' => $currentBalance,
            'projectedExpense' => $projectedExpense,
            'projectedIncome' => $projectedIncome,
            'projectedBalance' => $projectedBalance,
            'avgDailyExpense' => round($avgDailyExpense, 2),
            'avgDailyIncome' => round($avgDailyIncome, 2),
            'daysLeft' => $daysLeft,
        ];
    }

    /**
     * Per-category spend forecast for the current month.
     *
     * For each expense category: average monthly spend over the past 3 full months,
     * current month spend, and projected month total based on day-of-month pace.
     */
    public function forecastCategories(int $userId, string $userCurrency): array
    {
        $threeMonthsAgo = date('Y-m-01', strtotime('-3 months'));
        $monthStart = date('Y-m-01');
        $today = date('Y-m-d');

        $dayOfMonth = (int)date('j');
        $daysInMonth = (int)date('t');

        // Average monthly spend per category over past 3 months (excluding current)
        $historyRows = (new Query())
            ->select(['c.id', 'c.name', 'total' => 'SUM(t.amount)'])
            ->from(['t' => 'transaction'])
            ->innerJoin(['c' => 'category'], 'c.id = t.category_id')
            ->where(['t.user_id' => $userId, 't.type' => 'expense'])
            ->andWhere(['>=', 't.date', $threeMonthsAgo])
            ->andWhere(['<', 't.date', $monthStart])
            ->groupBy(['c.id', 'c.name'])
            ->all();

        // Current month spend per category
        $currentRows = (new Query())
            ->select(['c.id', 'total' => 'SUM(t.amount)'])
            ->from(['t' => 'transaction'])
            ->innerJoin(['c' => 'category'], 'c.id = t.category_id')
            ->where(['t.user_id' => $userId, 't.type' => 'expense'])
            ->andWhere(['between', 't.date', $monthStart, $today])
            ->groupBy(['c.id'])
            ->all();

        $currentByCat = [];
        foreach ($currentRows as $row) {
            $currentByCat[(int)$row['id']] = (float)$row['total'];
        }

        $result = [];
        foreach ($historyRows as $row) {
            $catId = (int)$row['id'];
            $avgMonthly = (float)$row['total'] / 3;
            $currentSpent = $currentByCat[$catId] ?? 0.0;

            // Project current month total from current pace
            $projected = $dayOfMonth > 0
                ? ($currentSpent / $dayOfMonth) * $daysInMonth
                : 0.0;

            $avgMonthlyConv = $this->convert($avgMonthly, $userCurrency);
            $currentConv = $this->convert($currentSpent, $userCurrency);
            $projectedConv = $this->convert($projected, $userCurrency);

            // Deviation > 20% from the usual → flag
            $status = 'normal';
            if ($avgMonthlyConv > 0) {
                $ratio = $projectedConv / $avgMonthlyConv;
                if ($ratio > 1.2) {
                    $status = 'over';
                } elseif ($ratio < 0.8) {
                    $status = 'under';
                }
            }

            $result[] = [
                'category' => $row['name'],
                'avgMonthly' => round($avgMonthlyConv, 2),
                'currentSpent' => round($currentConv, 2),
                'projected' => round($projectedConv, 2),
                'status' => $status,
            ];
        }

        // Sort: overruns first, then by projected desc
        usort($result, static function ($a, $b) {
            if ($a['status'] === 'over' && $b['status'] !== 'over') return -1;
            if ($b['status'] === 'over' && $a['status'] !== 'over') return 1;
            return $b['projected'] <=> $a['projected'];
        });

        return $result;
    }

    /**
     * Goal achievement forecast.
     *
     * For each active goal: contribution pace over past 90 days →
     * estimated completion date and on-track status vs deadline.
     */
    public function forecastGoals(int $userId): array
    {
        $goals = Goal::find()
            ->where(['user_id' => $userId, 'status' => Goal::STATUS_ACTIVE])
            ->all();

        $since = date('Y-m-d', strtotime('-' . self::HISTORY_DAYS . ' days'));
        $result = [];

        foreach ($goals as $goal) {
            $contributed = (float)(Transaction::find()
                ->where(['goal_id' => $goal->id])
                ->andWhere(['>=', 'date', $since])
                ->sum('amount') ?? 0);

            $dailyPace = $contributed / self::HISTORY_DAYS;
            $remaining = max(0, (float)$goal->target_amount - (float)$goal->current_amount);

            $etaDate = null;
            $daysNeeded = null;
            $onTrack = null;

            if ($remaining <= 0) {
                $onTrack = true;
            } elseif ($dailyPace > 0) {
                $daysNeeded = (int)ceil($remaining / $dailyPace);
                $eta = (new DateTime())->modify("+{$daysNeeded} days");
                $etaDate = $eta->format('Y-m-d');
                $onTrack = $etaDate <= $goal->deadline;
            } else {
                $onTrack = false; // no contributions in window
            }

            $result[] = [
                'id' => $goal->id,
                'name' => $goal->name,
                'currency' => $goal->currency,
                'current' => (float)$goal->current_amount,
                'target' => (float)$goal->target_amount,
                'remaining' => round($remaining, 2),
                'deadline' => $goal->deadline,
                'dailyPace' => round($dailyPace, 2),
                'etaDate' => $etaDate,
                'daysNeeded' => $daysNeeded,
                'onTrack' => $onTrack,
                'progress' => $goal->getProgress(),
            ];
        }

        return $result;
    }

    /**
     * Six-month projected balance series for the chart:
     * history (last 3 months actual) + forecast (next 3 months projected).
     */
    public function balanceTimeline(int $userId, string $userCurrency): array
    {
        [$avgDailyExpense, $avgDailyIncome] = $this->getDailyAverages($userId, $userCurrency);
        $netDaily = $avgDailyIncome - $avgDailyExpense;

        $labels = [];
        $actual = [];
        $forecast = [];

        // History: end-of-month balances, last 3 months
        for ($i = 3; $i >= 0; $i--) {
            $monthEnd = date('Y-m-t', strtotime("-{$i} months"));
            if ($monthEnd > date('Y-m-d')) {
                $monthEnd = date('Y-m-d');
            }
            $labels[] = date('M Y', strtotime($monthEnd));
            $balance = $this->getBalanceAt($userId, $monthEnd, $userCurrency);
            $actual[] = $balance;
            $forecast[] = null;
        }

        // Forecast: next 3 month ends from the last actual balance
        $lastBalance = end($actual) ?: 0.0;
        $lastDate = new DateTime(date('Y-m-d'));
        // Seed the forecast line at today's point for visual continuity
        $forecast[count($forecast) - 1] = $lastBalance;

        for ($i = 1; $i <= 3; $i++) {
            $monthEnd = new DateTime(date('Y-m-t', strtotime("+{$i} months")));
            $days = (int)$lastDate->diff($monthEnd)->format('%a');
            $labels[] = $monthEnd->format('M Y');
            $actual[] = null;
            $forecast[] = round($lastBalance + $netDaily * $days, 2);
        }

        return ['labels' => $labels, 'actual' => $actual, 'forecast' => $forecast];
    }

    // ─── internals ──────────────────────────────────────────────

    /** @return array{0: float, 1: float} [avgDailyExpense, avgDailyIncome] in user currency */
    private function getDailyAverages(int $userId, string $userCurrency): array
    {
        $since = date('Y-m-d', strtotime('-' . self::HISTORY_DAYS . ' days'));

        $rows = (new Query())
            ->select([
                'type' => 't.type',
                'total' => 'SUM(t.amount)',
            ])
            ->from(['t' => 'transaction'])
            ->where(['t.user_id' => $userId])
            ->andWhere(['>=', 't.date', $since])
            ->andWhere(['in', 't.type', ['expense', 'income']])
            ->groupBy(['t.type'])
            ->all();

        $expense = 0.0;
        $income = 0.0;
        foreach ($rows as $row) {
            $value = $this->convert((float)$row['total'], $userCurrency);
            if ($row['type'] === 'expense') {
                $expense = $value;
            } else {
                $income = $value;
            }
        }

        return [$expense / self::HISTORY_DAYS, $income / self::HISTORY_DAYS];
    }

    private function getCurrentBalance(int $userId, string $userCurrency): float
    {
        return $this->getBalanceAt($userId, date('Y-m-d'), $userCurrency);
    }

    private function getBalanceAt(int $userId, string $date, string $userCurrency): float
    {
        $rows = (new Query())
            ->select(['type' => 't.type', 'total' => 'SUM(t.amount)'])
            ->from(['t' => 'transaction'])
            ->where(['t.user_id' => $userId])
            ->andWhere(['<=', 't.date', $date])
            ->andWhere(['in', 't.type', ['expense', 'income']])
            ->groupBy(['t.type'])
            ->all();

        $balance = 0.0;
        foreach ($rows as $row) {
            $value = $this->convert((float)$row['total'], $userCurrency);
            $balance += $row['type'] === 'income' ? $value : -$value;
        }

        return round($balance, 2);
    }

    /** Convert from base (BYN) to user currency. Amounts in DB are mixed-currency but stored sums are близки к BYN-доминированным. */
    private function convert(float $amount, string $userCurrency): float
    {
        return $this->currencyService->fromBase(
            $this->currencyService->toBase($amount, 'BYN'),
            $userCurrency
        );
    }
}
