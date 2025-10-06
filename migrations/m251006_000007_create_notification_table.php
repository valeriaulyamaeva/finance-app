<?php

use yii\db\Migration;

class m251006_000007_create_notification_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('notification', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'message' => $this->text()->notNull(),
            'type' => "ENUM('budget_exceed', 'goal_reached', 'reminder', 'other') NOT NULL",
            'read_status' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_notification_user', 'notification', 'user_id', 'user', 'id', 'CASCADE');

        $this->createIndex('idx_notification_user_id', 'notification', 'user_id');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_notification_user', 'notification');
        $this->dropTable('notification');
    }
}