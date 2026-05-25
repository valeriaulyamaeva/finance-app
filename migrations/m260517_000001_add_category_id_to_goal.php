<?php

use yii\db\Migration;

/**
 * Adds category_id link to goal table so transactions with that category
 * can automatically credit the goal.
 */
class m260517_000001_add_category_id_to_goal extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('goal', 'category_id', $this->integer()->null()->after('user_id'));
        $this->addForeignKey(
            'fk_goal_category',
            'goal', 'category_id',
            'category', 'id',
            'SET NULL', 'CASCADE'
        );
        $this->createIndex('idx_goal_category', 'goal', 'category_id');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_goal_category', 'goal');
        $this->dropIndex('idx_goal_category', 'goal');
        $this->dropColumn('goal', 'category_id');
    }
}
