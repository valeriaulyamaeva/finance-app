<?php

use yii\db\Migration;

class m260406_000001_create_statement_import_tables extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('statement_import', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'filename' => $this->string(255)->notNull(),
            'file_type' => "ENUM('csv', 'pdf') NOT NULL",
            'transactions_count' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_statement_import_user',
            'statement_import', 'user_id',
            'user', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->createTable('imported_transaction_hash', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'hash' => $this->string(64)->notNull(),
            'transaction_id' => $this->integer(),
            'import_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_imported_hash_user',
            'imported_transaction_hash', 'user_id',
            'user', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->addForeignKey(
            'fk_imported_hash_import',
            'imported_transaction_hash', 'import_id',
            'statement_import', 'id',
            'CASCADE', 'CASCADE'
        );

        $this->createIndex(
            'uq_user_hash',
            'imported_transaction_hash',
            ['user_id', 'hash'],
            true
        );
    }

    public function safeDown(): void
    {
        $this->dropTable('imported_transaction_hash');
        $this->dropTable('statement_import');
    }
}
