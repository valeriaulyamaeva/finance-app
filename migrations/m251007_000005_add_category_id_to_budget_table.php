<?php

use yii\db\Migration;

class m251007_000005_add_category_id_to_budget_table extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('budget', 'category_id', $this->integer()->after('user_id'));

        $this->createIndex('idx_budget_category_id', 'budget', 'category_id');

        $this->addForeignKey(
            'fk_budget_category',
            'budget',
            'category_id',
            'category',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_budget_category', 'budget');
        $this->dropIndex('idx_budget_category_id', 'budget');
        $this->dropColumn('budget', 'category_id');
    }
}
