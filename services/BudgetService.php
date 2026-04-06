<?php

declare(strict_types=1);

namespace app\services;

use app\models\Budget;
use app\models\forms\BudgetForm;
use app\models\Transaction;
use yii\db\Connection;
use yii\web\NotFoundHttpException;
use DomainException;

final readonly class BudgetService
{
    public function __construct(
        private Connection $db,
        private CurrencyService $currencyService,
        private CategoryService $categoryService
    ) {}

    public function create(int $userId, BudgetForm $form): Budget
    {
        if (!$form->validate()) {
            throw new DomainException('Ошибка валидации: ' . implode(', ', $form->getErrorSummary(true)));
        }

        $this->categoryService->findById((int)$form->category_id, $userId);

        $budget = new Budget();
        $budget->user_id = $userId;
        $this->fillModel($budget, $form);

        if (!$budget->save()) {
            throw new DomainException('Не удалось сохранить бюджет.');
        }

        $this->refreshSpentAmount($budget);

        return $budget;
    }

    public function update(int $id, int $userId, BudgetForm $form): Budget
    {
        $budget = $this->findById($id, $userId);

        if (!$form->validate()) {
            throw new DomainException('Ошибка валидации.');
        }

        $this->fillModel($budget, $form);

        if (!$budget->save()) {
            throw new DomainException('Не удалось обновить бюджет.');
        }

        $this->refreshSpentAmount($budget);

        return $budget;
    }

    public function delete(int $id, int $userId): void
    {
        $budget = $this->findById($id, $userId);
        if (!$budget->delete()) {
            throw new DomainException('Ошибка при удалении бюджета.');
        }
    }

    public function refreshSpentAmount(Budget $budget): void
    {
        $transactions = Transaction::find()
            ->where(['category_id' => $budget->category_id, 'user_id' => $budget->user_id])
            ->andWhere(['>=', 'date', $budget->start_date])
            ->andFilterWhere(['<=', 'date', $budget->end_date])
            ->all();

        $totalSpent = 0.0;
        foreach ($transactions as $transaction) {
            $amount = (float)$transaction->amount;

            if ($transaction->currency !== $budget->currency) {
                $amount = $this->currencyService->convert(
                    $amount,
                    $transaction->currency,
                    $budget->currency
                );
            }
            $totalSpent += $amount;
        }

        $budget->updateAttributes(['spent' => $totalSpent]);
    }

    public function findById(int $id, int $userId): Budget
    {
        $model = Budget::find()->forUser($userId)->andWhere(['id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('Бюджет не найден.');
        }
        return $model;
    }

    private function fillModel(Budget $budget, BudgetForm $form): void
    {
        $budget->name = $form->name;
        $budget->amount = $form->amount;
        $budget->currency = $form->currency;
        $budget->category_id = $form->category_id;
        $budget->period = $form->period;
        $budget->start_date = $form->start_date;
        $budget->end_date = $form->end_date;
    }

    private function checkBudgetNotification(Budget $budget): void
    {
        $remaining = $budget->getRemainingAmount();

    }

    public function findActiveForCategory(int $categoryId, int $userId): ?Budget
    {
        return Budget::find()
            ->forUser($userId)
            ->forCategory($categoryId)
            ->active()
            ->one();
    }
}