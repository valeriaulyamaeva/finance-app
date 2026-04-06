<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\RecurringTransaction;
use app\services\RecurringTransactionService;
use Yii;
use yii\web\Response;
use Throwable;

final class RecurringTransactionController extends BaseController
{
    public function __construct(
        $id,
        $module,
        private readonly RecurringTransactionService $service,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionCreate(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $data = Yii::$app->request->post();
            if (empty($data['currency'])) {
                $data['currency'] = Yii::$app->user->identity->currency ?? 'BYN';
            }

            $model = $this->service->save($data, null, (int)Yii::$app->user->id);

            return [
                'success' => true,
                'id' => $model->id,
                'message' => 'Шаблон успешно создан'
            ];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionUpdate(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $data = Yii::$app->request->post();
            $this->service->save($data, $id, (int)Yii::$app->user->id);

            return ['success' => true];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionList(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $models = RecurringTransaction::find()
            ->forUser((int)Yii::$app->user->id)
            ->with(['category'])
            ->all();

        $data = array_map(fn(RecurringTransaction $m) => [
            'id' => $m->id,
            'amount' => $m->amount,
            'currency' => $m->currency,
            'frequency_label' => RecurringTransaction::optsFrequency()[$m->frequency] ?? $m->frequency,
            'next_date' => $m->next_date,
            'category' => $m->category->name ?? '-',
            'description' => $m->description ?? '',
            'active' => (bool)$m->active,
        ], $models);

        return ['success' => true, 'data' => $data];
    }

    public function actionDelete(int $id): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $this->service->delete($id);
            return ['success' => true];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function actionProcess(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $count = $this->service->runScheduledTasks();
            return ['success' => true, 'processed' => $count];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}