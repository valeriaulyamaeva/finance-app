<?php

use yii\db\Migration;

class m251006_000009_fix_user_timestamps extends Migration
{
    public function safeUp(): void
    {
        $this->alterColumn('user', 'created_at', $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'));
        $this->alterColumn('user', 'updated_at', $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
    }

    public function safeDown(): void
    {
        $this->alterColumn('user', 'created_at', $this->timestamp()->null()->defaultExpression('CURRENT_TIMESTAMP'));
        $this->alterColumn('user', 'updated_at', $this->timestamp()->null()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
    }
}