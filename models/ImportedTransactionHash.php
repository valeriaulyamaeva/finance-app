<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property string $hash
 * @property int|null $transaction_id
 * @property int $import_id
 * @property string $created_at
 *
 * @property User $user
 * @property StatementImport $import
 */
final class ImportedTransactionHash extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'imported_transaction_hash';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'hash', 'import_id'], 'required'],
            [['user_id', 'transaction_id', 'import_id'], 'integer'],
            [['hash'], 'string', 'max' => 64],
            [['user_id', 'hash'], 'unique', 'targetAttribute' => ['user_id', 'hash']],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getImport(): ActiveQuery
    {
        return $this->hasOne(StatementImport::class, ['id' => 'import_id']);
    }
}
