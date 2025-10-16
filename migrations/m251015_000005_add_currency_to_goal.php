<?php

use yii\db\Migration;

class m251015_000005_add_currency_to_goal extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('goal', 'currency', $this->string(3)->notNull()->defaultValue('BYN')->after('target_amount'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('goal', 'currency');
    }
}
