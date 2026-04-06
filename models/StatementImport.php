<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property string $filename
 * @property string $file_type
 * @property int $transactions_count
 * @property string $created_at
 *
 * @property User $user
 * @property ImportedTransactionHash[] $hashes
 */
final class StatementImport extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'statement_import';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'filename', 'file_type'], 'required'],
            [['user_id', 'transactions_count'], 'integer'],
            [['filename'], 'string', 'max' => 255],
            [['file_type'], 'in', 'range' => ['csv', 'pdf']],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getHashes(): ActiveQuery
    {
        return $this->hasMany(ImportedTransactionHash::class, ['import_id' => 'id']);
    }
}
