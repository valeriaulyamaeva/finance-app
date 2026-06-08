<?php

use yii\db\Migration;

class m260608_000001_create_investment_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('investment', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'type' => "ENUM('stocks','crypto','deposit','realestate','bonds','fund','other') NOT NULL DEFAULT 'other'",
            'invested_amount' => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'current_value' => $this->decimal(14, 2)->notNull()->defaultValue(0),
            'currency' => $this->string(3)->notNull()->defaultValue('BYN'),
            'purchase_date' => $this->date()->null(),
            'note' => $this->string(500)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_investment_user',
            'investment', 'user_id',
            'user', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->createIndex('idx_investment_user', 'investment', 'user_id');
    }

    public function safeDown(): void
    {
        $this->dropTable('investment');
    }
}
