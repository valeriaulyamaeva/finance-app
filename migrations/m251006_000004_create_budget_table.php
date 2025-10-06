<?php

use yii\db\Migration;

class m251006_000004_create_budget_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('budget', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'amount' => $this->decimal(10, 2)->notNull(),
            'period' => "ENUM('monthly', 'yearly') NOT NULL DEFAULT 'monthly'",
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_budget_user', 'budget', 'user_id', 'user', 'id', 'CASCADE');
        $this->addForeignKey('fk_transaction_budget', 'transaction', 'budget_id', 'budget', 'id', 'SET NULL');

        $this->createIndex('idx_budget_user_id', 'budget', 'user_id');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_transaction_budget', 'transaction');
        $this->dropForeignKey('fk_budget_user', 'budget');
        $this->dropTable('budget');
    }
}