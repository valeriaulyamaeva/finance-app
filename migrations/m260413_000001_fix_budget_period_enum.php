<?php

use yii\db\Migration;

class m260413_000001_fix_budget_period_enum extends Migration
{
    public function safeUp(): void
    {
        $this->alterColumn('budget', 'period', "ENUM('daily','weekly','monthly','yearly') NOT NULL DEFAULT 'monthly'");
    }

    public function safeDown(): void
    {
        $this->alterColumn('budget', 'period', "ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly'");
    }
}
