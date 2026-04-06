<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Budget;
use app\models\forms\BudgetForm;
use app\services\BudgetService;
use app\services\CategoryService;
use app\services\CurrencyService;
use Yii;
use yii\web\Response;
use yii\filters\ContentNegotiator;
use DomainException;

final class BudgetController extends BaseController
{
    public function __construct(
        $id, $module,
        private readonly BudgetService $service,
        private readonly CategoryService $categoryService,
        private readonly CurrencyService $currencyService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'only' => ['create', 'update', 'delete', 'view'],
            'formats' => ['application/json' => Response::FORMAT_JSON],
        ];
        return $behaviors;
    }

    public function actionIndex(?string $month = null, ?string $year = null): string
    {
        $userId = (int)Yii::$app->user->id;
        $user = Yii::$app->user->identity;

        $targetMonth = $month ?? date('m');
        $targetYear = $year ?? date('Y');

        $startDate = "$targetYear-$targetMonth-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $budgets = Budget::find()
            ->forUser($userId)
            ->with('category')
            ->andWhere([
                'or',
                ['between', 'start_date', $startDate, $endDate],
                ['between', 'end_date', $startDate, $endDate],
                [
                    'and',
                    ['<=', 'start_date', $startDate],
                    ['or', ['>=', 'end_date', $endDate], ['end_date' => null]]
                ]
            ])
            ->orderBy(['start_date' => SORT_DESC])
            ->all();

        $totalBudget = 0;
        $totalSpent = 0;
        foreach ($budgets as $budget) {
            $totalBudget += $this->currencyService->convert($budget->amount, $budget->currency, $user->currency);
            $totalSpent += $this->currencyService->convert($budget->spent, $budget->currency, $user->currency);
        }

        return $this->render('index', [
            'budgets' => $budgets,
            'user' => $user,
            'selectedMonth' => $targetMonth,
            'selectedYear' => $targetYear,
            'summary' => [
                'total_budget' => $totalBudget,
                'total_spent' => $totalSpent,
                'remaining' => $totalBudget - $totalSpent,
            ],
        ]);
    }

    public function actionCreate(): array
    {
        $form = new BudgetForm();
        if ($form->load(Yii::$app->request->post(), 'Budget')) {
            try {
                $this->service->create((int)Yii::$app->user->id, $form);
                return ['success' => true];
            } catch (DomainException $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }
        return ['success' => false, 'message' => 'Ошибка загрузки данных в форму'];
    }

    public function actionUpdate(int $id): array
    {
        $form = new BudgetForm();
        if ($form->load(Yii::$app->request->post(), 'Budget')) {
            try {
                $this->service->update($id, (int)Yii::$app->user->id, $form);
                return ['success' => true];
            } catch (DomainException $e) {
                return ['success' => false, 'message' => $e->getMessage()];
            }
        }
        return ['success' => false, 'message' => 'Ошибка загрузки данных'];
    }

    public function actionView(int $id): array
    {
        $budget = $this->service->findById($id, (int)Yii::$app->user->id);
        return [
            'success' => true,
            'budget' => $budget->toArray(),
        ];
    }

    public function actionDelete(int $id): array
    {
        try {
            $this->service->delete($id, (int)Yii::$app->user->id);
            return ['success' => true];
        } catch (DomainException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}