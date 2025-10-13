<?php
use yii\db\Migration;

class m251013_000006_add_spent_to_budget_table extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('budget', 'spent', $this->decimal(10, 2)->notNull()->defaultValue(0));
    }

    public function safeDown(): void
    {
        $this->dropColumn('budget', 'spent');
    }
}
