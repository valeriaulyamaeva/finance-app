<?php

use yii\db\Migration;

class m260608_000002_add_ticker_to_investment extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('investment', 'ticker', $this->string(20)->null()->after('type'));
        $this->addColumn('investment', 'quantity', $this->decimal(18, 8)->null()->after('ticker'));
        $this->addColumn('investment', 'last_price', $this->decimal(18, 8)->null()->after('quantity'));
        $this->addColumn('investment', 'price_updated_at', $this->timestamp()->null()->after('last_price'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('investment', 'ticker');
        $this->dropColumn('investment', 'quantity');
        $this->dropColumn('investment', 'last_price');
        $this->dropColumn('investment', 'price_updated_at');
    }
}
