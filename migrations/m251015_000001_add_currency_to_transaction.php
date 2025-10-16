<?php

use yii\db\Migration;

class m251015_000001_add_currency_to_transaction extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('transaction', 'currency', $this->string(3)->notNull()->defaultValue('BYN')->after('amount'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('transaction', 'currency');
    }
}
