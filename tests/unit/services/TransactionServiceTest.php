<?php

namespace tests\unit\services;

use app\models\Budget;
use app\models\Category;
use app\models\Goal;
use app\models\Transaction;
use app\models\User;
use app\services\CurrencyService;
use app\services\TransactionService;
use Codeception\Test\Unit;
use UnitTester;

class TransactionServiceTest extends Unit
{
    protected UnitTester $tester;

    private TransactionService $service;
    private CurrencyService $currencyMock;

    protected function _before(): void
    {
        $this->currencyMock = $this->createMock(CurrencyService::class);
        $this->currencyMock
            ->method('toBase')
            ->willReturnCallback(fn($amount) => $amount); // 1:1
        $this->currencyMock
            ->method('fromBase')
            ->willReturnCallback(fn($amount) => $amount);
        $this->currencyMock
            ->method('getRate')
            ->willReturn(1.0);

        $this->service = new TransactionService($this->currencyMock);
    }

    /** @test */
    public function create_transaction_updates_budget_spent(): void
    {
        $user = $this->tester->haveRecord(User::class, ['currency' => 'BYN']);
        $category = $this->tester->haveRecord(Category::class, [
            'user_id' => $user->id,
            'type'    => 'expense'
        ]);
        $budget = $this->tester->haveRecord(Budget::class, [
            'user_id'     => $user->id,
            'category_id' => $category->id,
            'amount'      => 10000.00,
            'spent'       => 0.00,
            'currency'    => 'BYN',
            'period'      => 'monthly',
            'start_date'  => '2025-01-01',
        ]);

        $data = [
            'amount'      => 1500.00,
            'date'        => '2025-04-05',
            'category_id' => $category->id,
        ];

        $transaction = $this->service->create($data, $user->id);

        $this->tester->seeRecord(Budget::class, [
            'id'    => $budget->id,
            'spent' => 1500.00
        ]);

        $this->assertEquals($budget->id, $transaction->budget_id);
        $this->assertEquals(Transaction::TYPE_EXPENSE, $transaction->type);
    }

    /** @test */
    public function create_transaction_updates_goal_progress(): void
    {
        $user = $this->tester->haveRecord(User::class, ['currency' => 'BYN']);
        $category = $this->tester->haveRecord(Category::class, [
            'user_id' => $user->id,
            'type'    => 'goal'
        ]);
        $goal = $this->tester->haveRecord(Goal::class, [
            'user_id'        => $user->id,
            'target_amount'  => 10000.00,
            'current_amount' => 0.00,
            'currency'       => 'BYN',
            'status'         => 'active',
            'deadline'       => '2025-12-31',
        ]);

        $data = [
            'amount'      => 3000.00,
            'date'        => '2025-04-05',
            'category_id' => $category->id,
            'goal_id'     => $goal->id,
        ];

        $transaction = $this->service->create($data, $user->id);

        $this->tester->seeRecord(Goal::class, [
            'id'             => $goal->id,
            'current_amount' => 3000.00,
        ]);

        $this->assertEquals($goal->id, $transaction->goal_id);
        $this->assertEquals(Transaction::TYPE_GOAL, $transaction->type);
    }

    /** @test */
    public function delete_transaction_restores_budget_and_goal(): void
    {
        $user = $this->tester->haveRecord(User::class, ['currency' => 'BYN']);
        $category = $this->tester->haveRecord(Category::class, ['user_id' => $user->id, 'type' => 'expense']);
        $budget = $this->tester->haveRecord(Budget::class, [
            'user_id'    => $user->id,
            'category_id' => $category->id,
            'spent'       => 2000.00,
            'currency'    => 'BYN',
        ]);
        $goal = $this->tester->haveRecord(Goal::class, [
            'user_id'        => $user->id,
            'current_amount' => 5000.00,
            'currency'       => 'BYN',
        ]);

        $transaction = $this->tester->haveRecord(Transaction::class, [
            'user_id'     => $user->id,
            'amount'      => 1000.00,
            'currency'    => 'BYN',
            'type'       => 'expense',
            'category_id' => $category->id,
            'budget_id'   => $budget->id,
            'goal_id'     => $goal->id,
        ]);

        $this->service->delete($transaction->id);

        $this->tester->seeRecord(Budget::class, ['id' => $budget->id, 'spent' => 1000.00]);
        $this->tester->seeRecord(Goal::class, ['id' => $goal->id, 'current_amount' => 4000.00]);
        $this->tester->dontSeeRecord(Transaction::class, ['id' => $transaction->id]);
    }
}