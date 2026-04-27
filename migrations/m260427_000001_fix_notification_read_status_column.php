<?php

use yii\db\Migration;

/**
 * Fixes a typo in notification table column name.
 * Original migration created `read_s tatus` (with space) instead of `read_status`.
 * This migration is idempotent — it only renames if the broken column exists.
 */
class m260427_000001_fix_notification_read_status_column extends Migration
{
    public function safeUp(): void
    {
        $columns = $this->db->getSchema()->getTableSchema('notification', true)->columns;

        if (isset($columns['read_s tatus']) && !isset($columns['read_status'])) {
            $this->execute("ALTER TABLE notification CHANGE `read_s tatus` `read_status` TINYINT(1) NOT NULL DEFAULT 0");
        }
    }

    public function safeDown(): void
    {
        // No rollback — we don't want to recreate the typo
    }
}
