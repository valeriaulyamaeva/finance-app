<?php

namespace app\controllers;

use app\models\Budget;
use app\services\BudgetService;
use app\services\CurrencyService;
use Exception;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\Response;

class BudgetController extends Controller
{
    private BudgetService $service;
    private CurrencyService $currencyService;

    public function __construct($id, $module, BudgetService $budgetService, CurrencyService $currencyService, $config = [])
    {
        $this->service = $budgetService;
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
        $summary = $this->service->getUserSummary($userId);
        $userCurrency = $user->currency ?? 'BYN';

        $dataProvider = new ActiveDataProvider([
            'query' => Budget::find()
                ->where(['user_id' => $userId])
                ->with('category'),
            'pagination' => ['pageSize' => 10],
            'sort' => ['defaultOrder' => ['start_date' => SORT_DESC]],
        ]);

        $budgetsWithDisplay = [];
        foreach ($dataProvider->models as $budget) {
            $budgetSummary = $this->service->calculateSummary($budget);

            $displayAmount = $budget->amount;
            $displaySpent = $budgetSummary['spent'];
            $displayRemaining = $budgetSummary['remaining'];

            $userCurrency = $user->currency ?? 'BYN';
            if ($budget->currency !== $userCurrency) {
                $displayAmount = $this->currencyService->fromBase(
                    $this->currencyService->toBase($budget->amount, $budget->currency),
                    $userCurrency
                );
                $displaySpent = $this->currencyService->fromBase(
                    $this->currencyService->toBase($budgetSummary['spent'], $budget->currency),
                    $userCurrency
                );
            }

            $budgetsWithDisplay[] = [
                'model' => $budget,
                'display_amount' => number_format($displayAmount, 2, '.', ''),
                'display_spent' => number_format($displaySpent, 2, '.', ''),
                'display_remaining' => number_format($displayRemaining, 2, '.', ''),
                'display_currency' => $userCurrency,
                'category_name' => $budget->category->name ?? '-',
                'display_period' => $budget->displayPeriod(),
            ];
        }

        return $this->render('index', [
            'user' => $user,
            'dataProvider' => $dataProvider,
            'budgetsWithDisplay' => $budgetsWithDisplay,
            'summary' => $summary,
        ]);
    }

    public function actionCreate(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $data = Yii::$app->request->post();
            $user = Yii::$app->user->identity;

            $data['user_id'] = $user->id;
            $data['currency'] = $user->currency ?? 'BYN';

            $originalAmount = (float)($data['Budget']['amount'] ?? 0);

            $budget = $this->service->create($data, $user->id);

            $budgetArray = $budget->toArray();
            $budgetArray['display_amount'] = number_format($originalAmount, 2, '.', '');
            $budgetArray['display_currency'] = $data['currency'];
            $budgetArray['category_name'] = $budget->category->name ?? '-';

            return ['success' => true, 'budget' => $budgetArray];
        } catch (Throwable $e) {
            Yii::error('Ошибка при создании бюджета: ' . $e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionUpdate(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $data = Yii::$app->request->post();
            $user = Yii::$app->user->identity;

            $data['currency'] = $user->currency ?? 'BYN';

            $budget = $this->service->update($id, $data);

            $budgetArray = $budget->toArray();
            $amount = $data['Budget']['amount'] ?? $budget->amount;
            $budgetArray['display_amount'] = number_format((float)$amount, 2, '.', '');
            $budgetArray['display_currency'] = $data['currency'];
            $budgetArray['category_name'] = $budget->category->name ?? '-';

            return ['success' => true, 'budget' => $budgetArray];
        } catch (Throwable $e) {
            Yii::error('Ошибка при обновлении бюджета: ' . $e->getMessage(), __METHOD__);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @throws Exception
     */
    public function actionView(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $budget = Budget::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        if (!$budget) {
            return ['success' => false, 'message' => 'Бюджет не найден'];
        }

        $userCurrency = Yii::$app->user->identity->currency ?? 'BYN';
        $displayAmount = $budget->amount;
        $displaySpent = $budget->spent;

        if ($budget->currency !== $userCurrency) {
            $displayAmount = $this->currencyService->fromBase(
                $this->currencyService->toBase($budget->amount, $budget->currency),
                $userCurrency
            );
            $displaySpent = $this->currencyService->fromBase(
                $this->currencyService->toBase($budget->spent, $budget->currency),
                $userCurrency
            );
        }

        $budgetArray = $budget->toArray();
        $budgetArray['display_amount'] = number_format($displayAmount, 2, '.', '');
        $budgetArray['display_spent'] = number_format($displaySpent, 2, '.', '');
        $budgetArray['display_currency'] = $userCurrency;
        $budgetArray['display_period'] = $budget->displayPeriod();
        $budgetArray['category_name'] = $budget->category->name ?? '-';

        return ['success' => true, 'budget' => $budgetArray];
    }

    public function actionDelete(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $id = (int)Yii::$app->request->post('id');
            if (!$id) {
                return ['success' => false, 'message' => 'ID бюджета не указан'];
            }

            $budget = Budget::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
            if (!$budget) {
                return ['success' => false, 'message' => 'Бюджет не найден или принадлежит другому пользователю'];
            }

            $budget->delete();
            return ['success' => true];
        } catch (Throwable $e) {
            Yii::error("Ошибка при удалении бюджета: {$e->getMessage()}", __METHOD__);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
