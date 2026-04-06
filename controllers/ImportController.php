<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Category;
use app\services\ImportService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;
use Throwable;

final class ImportController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly ImportService $importService,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
                'denyCallback' => function () {
                    if (Yii::$app->request->isAjax) {
                        Yii::$app->response->format = Response::FORMAT_JSON;
                        Yii::$app->response->statusCode = 401;
                        return ['success' => false, 'message' => 'Необходима авторизация'];
                    }
                    return $this->redirect(['user/login']);
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'parse' => ['post'],
                    'confirm' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $userId = (int)Yii::$app->user->id;

        $categories = Category::find()
            ->where(['user_id' => $userId])
            ->orderBy(['name' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'categories' => $categories,
        ]);
    }

    public function actionParse(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $file = UploadedFile::getInstanceByName('file');
            if (!$file) {
                return ['success' => false, 'message' => 'Файл не загружен'];
            }

            $ext = strtolower($file->extension);
            if (!in_array($ext, ['csv', 'pdf'])) {
                return ['success' => false, 'message' => 'Поддерживаются только CSV и PDF файлы'];
            }

            $result = $this->importService->parseFile($file->tempName, $ext);

            if (empty($result['transactions'])) {
                return [
                    'success' => false,
                    'message' => 'Не удалось распознать транзакции',
                    'errors' => $result['errors'] ?? [],
                ];
            }

            $userId = (int)Yii::$app->user->id;

            // Check for duplicates
            $hashes = array_column($result['transactions'], 'hash');
            $existingHashes = $this->importService->getExistingHashes($userId, $hashes);

            // Map categories
            $transactions = $this->importService->mapCategories($userId, $result['transactions']);

            // Mark duplicates
            foreach ($transactions as &$tx) {
                $tx['is_duplicate'] = in_array($tx['hash'], $existingHashes);
            }

            return [
                'success' => true,
                'transactions' => $transactions,
                'metadata' => $result['metadata'] ?? [],
                'errors' => $result['errors'] ?? [],
            ];
        } catch (Throwable $e) {
            Yii::error('Import parse error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка обработки файла: ' . $e->getMessage()];
        }
    }

    public function actionConfirm(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $data = json_decode(Yii::$app->request->rawBody, true);
            $transactions = $data['transactions'] ?? [];
            $filename = $data['filename'] ?? 'unknown';
            $fileType = $data['file_type'] ?? 'csv';

            if (empty($transactions)) {
                return ['success' => false, 'message' => 'Нет транзакций для импорта'];
            }

            $userId = (int)Yii::$app->user->id;
            $import = $this->importService->bulkImport($userId, $transactions, $filename, $fileType);

            return [
                'success' => true,
                'message' => "Импортировано транзакций: {$import->transactions_count}",
                'count' => $import->transactions_count,
            ];
        } catch (Throwable $e) {
            Yii::error('Import confirm error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка импорта: ' . $e->getMessage()];
        }
    }
}
