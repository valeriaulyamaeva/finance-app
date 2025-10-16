<?php

use yii\db\Migration;

class m251015_000002_add_currency_to_budget extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('budget', 'currency', $this->string(3)->notNull()->defaultValue('BYN')->after('amount'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('budget', 'currency');
    }
}
