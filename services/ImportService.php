<?php

declare(strict_types=1);

namespace app\services;

use app\models\Category;
use app\models\ImportedTransactionHash;
use app\models\StatementImport;
use Exception;
use Yii;

readonly class ImportService
{
    public function __construct(
        private TransactionService $transactionService,
    ) {}

    public function parseFile(string $filePath, string $fileType): array
    {
        $url = Yii::$app->params['parserServiceUrl'] . '/parse/' . $fileType;

        $cfile = new \CURLFile($filePath, match ($fileType) {
            'csv' => 'text/csv',
            'pdf' => 'application/pdf',
        }, 'upload.' . $fileType);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['file' => $cfile],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            throw new Exception("Ошибка парсер-сервиса: " . ($error ?: "HTTP $httpCode"));
        }

        $data = json_decode($response, true);
        if (!$data) {
            throw new Exception("Невалидный ответ от парсер-сервиса");
        }

        return $data;
    }

    public function getExistingHashes(int $userId, array $hashes): array
    {
        if (empty($hashes)) {
            return [];
        }

        return ImportedTransactionHash::find()
            ->select('hash')
            ->where(['user_id' => $userId, 'hash' => $hashes])
            ->column();
    }

    public function mapCategories(int $userId, array $parsedTransactions): array
    {
        $categories = Category::find()
            ->where(['user_id' => $userId])
            ->all();

        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[mb_strtolower($cat->name)] = $cat;
        }

        foreach ($parsedTransactions as &$tx) {
            $tx['category_id'] = null;
            $tx['category_name'] = null;

            if (empty($tx['suggested_category'])) {
                continue;
            }

            $suggested = mb_strtolower($tx['suggested_category']);

            // Exact match
            if (isset($categoryMap[$suggested])) {
                $cat = $categoryMap[$suggested];
                $tx['category_id'] = $cat->id;
                $tx['category_name'] = $cat->name;
                continue;
            }

            // Partial match
            foreach ($categoryMap as $name => $cat) {
                if (str_contains($name, $suggested) || str_contains($suggested, $name)) {
                    $tx['category_id'] = $cat->id;
                    $tx['category_name'] = $cat->name;
                    break;
                }
            }
        }

        return $parsedTransactions;
    }

    public function bulkImport(int $userId, array $transactions, string $filename, string $fileType): StatementImport
    {
        return Yii::$app->db->transaction(function () use ($userId, $transactions, $filename, $fileType) {
            $import = new StatementImport();
            $import->user_id = $userId;
            $import->filename = $filename;
            $import->file_type = $fileType;
            $import->transactions_count = 0;
            if (!$import->save()) {
                throw new Exception('Ошибка создания записи импорта: ' . json_encode($import->getErrors()));
            }

            $count = 0;
            $skipped = 0;
            foreach ($transactions as $tx) {
                // Skip if no category selected
                if (empty($tx['category_id'])) {
                    $skipped++;
                    continue;
                }

                // Skip duplicates
                $existingHash = ImportedTransactionHash::find()
                    ->where(['user_id' => $userId, 'hash' => $tx['hash']])
                    ->exists();
                if ($existingHash) {
                    $skipped++;
                    continue;
                }

                $data = [
                    'amount' => $tx['amount'],
                    'currency' => $tx['currency'],
                    'date' => $tx['date'],
                    'type' => $tx['type'],
                    'category_id' => (int)$tx['category_id'],
                    'description' => $tx['description'] ?? null,
                ];

                $transaction = $this->transactionService->create($data, $userId);

                $hash = new ImportedTransactionHash();
                $hash->user_id = $userId;
                $hash->hash = $tx['hash'];
                $hash->transaction_id = $transaction->id;
                $hash->import_id = $import->id;
                $hash->save(false);

                $count++;
            }

            $import->transactions_count = $count;
            $import->save(false);

            return $import;
        });
    }
}
