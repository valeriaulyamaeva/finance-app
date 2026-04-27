<?php

use app\services\CategoryService;
use yii\db\Migration;

/**
 * Adds new default categories (Еда, Кэшбек, Подписки, Автосписания,
 * Переводы (доход)/(расход), etc.) to existing users who registered
 * before these defaults were added.
 *
 * Idempotent: only inserts categories that don't already exist for the user.
 */
class m260427_000002_add_default_categories_for_existing_users extends Migration
{
    public function safeUp(): void
    {
        $userIds = $this->db->createCommand('SELECT id FROM user')->queryColumn();
        $defaults = CategoryService::getDefaultCategories();

        foreach ($userIds as $userId) {
            $existing = $this->db->createCommand(
                'SELECT name FROM category WHERE user_id = :uid',
                [':uid' => $userId]
            )->queryColumn();

            $existingSet = array_flip($existing);
            $rows = [];
            foreach ($defaults as $cat) {
                if (!isset($existingSet[$cat['name']])) {
                    $rows[] = [$userId, $cat['name'], $cat['type']];
                }
            }

            if (!empty($rows)) {
                $this->batchInsert('category', ['user_id', 'name', 'type'], $rows);
            }
        }
    }

    public function safeDown(): void
    {
        // No-op: don't delete user data on rollback
    }
}
