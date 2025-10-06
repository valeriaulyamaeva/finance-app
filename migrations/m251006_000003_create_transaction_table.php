<?php

use yii\db\Migration;

class m251006_000003_create_transaction_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('transaction', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'amount' => $this->decimal(10, 2)->notNull(),
            'date' => $this->date()->notNull(),
            'type' => "ENUM('income', 'expense', 'goal') NOT NULL DEFAULT 'expense'",
            'category_id' => $this->integer(),
            'budget_id' => $this->integer(),
            'goal_id' => $this->integer(),
            'description' => $this->text(),
            'recurring_id' => $this->integer(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_transaction_user', 'transaction', 'user_id', 'user', 'id', 'CASCADE');
        $this->addForeignKey('fk_transaction_category', 'transaction', 'category_id', 'category', 'id', 'SET NULL');

        $this->createIndex('idx_transaction_user_id', 'transaction', 'user_id');
        $this->createIndex('idx_transaction_date', 'transaction', 'date');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_transaction_category', 'transaction');
        $this->dropForeignKey('fk_transaction_user', 'transaction');
        $this->dropTable('transaction');
    }
}