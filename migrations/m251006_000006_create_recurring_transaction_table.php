<?php

use yii\db\Migration;

class m251006_000006_create_recurring_transaction_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('recurring_transaction', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'amount' => $this->decimal(10, 2)->notNull(),
            'frequency' => "ENUM('daily', 'weekly', 'monthly') NOT NULL",
            'next_date' => $this->date()->notNull(),
            'category_id' => $this->integer(),
            'budget_id' => $this->integer(),
            'goal_id' => $this->integer(),
            'description' => $this->text(),
            'active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_recurring_user', 'recurring_transaction', 'user_id', 'user', 'id', 'CASCADE');
        $this->addForeignKey('fk_recurring_category', 'recurring_transaction', 'category_id', 'category', 'id', 'SET NULL');
        $this->addForeignKey('fk_recurring_budget', 'recurring_transaction', 'budget_id', 'budget', 'id', 'SET NULL');
        $this->addForeignKey('fk_recurring_goal', 'recurring_transaction', 'goal_id', 'goal', 'id', 'SET NULL');
        $this->addForeignKey('fk_transaction_recurring', 'transaction', 'recurring_id', 'recurring_transaction', 'id', 'SET NULL');

        $this->createIndex('idx_recurring_user_id', 'recurring_transaction', 'user_id');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_transaction_recurring', 'transaction');
        $this->dropForeignKey('fk_recurring_goal', 'recurring_transaction');
        $this->dropForeignKey('fk_recurring_budget', 'recurring_transaction');
        $this->dropForeignKey('fk_recurring_category', 'recurring_transaction');
        $this->dropForeignKey('fk_recurring_user', 'recurring_transaction');
        $this->dropTable('recurring_transaction');
    }
}