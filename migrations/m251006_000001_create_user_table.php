<?php


use yii\db\Migration;

class m251006_000001_create_user_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'username' => $this->string(255)->notNull(),
            'email' => $this->string(255)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'access_token' => $this->string(255),
            'theme' => "ENUM('light', 'dark') NOT NULL DEFAULT 'light'",
            'currency' => $this->string(3)->notNull()->defaultValue('BYN'),
            'avatar' => $this->string(255),
            'last_login' => $this->timestamp(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx_user_email', 'user', 'email');
    }

    public function safeDown(): void
    {
        $this->dropTable('user');
    }
}