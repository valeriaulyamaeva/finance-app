<?php

use yii\db\Migration;

class m251015_000004_add_currency_to_recurring_transaction extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('recurring_transaction', 'currency', $this->string(3)->notNull()->defaultValue('BYN')->after('amount'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('recurring_transaction', 'currency');
    }
}
