<?php

use yii\db\Migration;

class m251027_000001_add_related_columns_to_notification_table extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('notification', 'related_type', $this->string(50)->defaultValue(null)->after('type'));
        $this->addColumn('notification', 'related_id', $this->integer()->defaultValue(null)->after('related_type'));

        $this->createIndex('idx_notification_related', 'notification', ['related_type', 'related_id']);
    }

    public function safeDown(): void
    {
        $this->dropIndex('idx_notification_related', 'notification');
        $this->dropColumn('notification', 'related_id');
        $this->dropColumn('notification', 'related_type');
    }
}
