<?php

use yii\db\Migration;

class m251006_000005_create_goal_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('goal', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'target_amount' => $this->decimal(10, 2)->notNull(),
            'deadline' => $this->date()->notNull(),
            'current_amount' => $this->decimal(10, 2)->notNull()->defaultValue(0),
            'status' => "ENUM('active', 'completed', 'failed') NOT NULL DEFAULT 'active'",
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_goal_user', 'goal', 'user_id', 'user', 'id', 'CASCADE');
        $this->addForeignKey('fk_transaction_goal', 'transaction', 'goal_id', 'goal', 'id', 'SET NULL');

        $this->createIndex('idx_goal_user_id', 'goal', 'user_id');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_transaction_goal', 'transaction');
        $this->dropForeignKey('fk_goal_user', 'goal');
        $this->dropTable('goal');
    }
}