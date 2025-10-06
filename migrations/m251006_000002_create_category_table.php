<?php

use yii\db\Migration;

class m251006_000002_create_category_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('category', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'type' => "ENUM('income', 'expense', 'goal') NOT NULL DEFAULT 'expense'",
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_category_user', 'category', 'user_id', 'user', 'id', 'CASCADE');
        $this->createIndex('idx_category_user_id', 'category', 'user_id');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_category_user', 'category');
        $this->dropTable('category');
    }
}