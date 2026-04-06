<?php

declare(strict_types=1);

namespace app\services;

use app\models\Category;
use app\models\forms\CategoryForm;
use yii\db\Connection;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use DomainException;
use Throwable;

final readonly class CategoryService
{
    public function __construct(
        private Connection $db
    ) {}

    /**
     * @return Category[]
     */
    public function getAllByUser(int $userId): array
    {
        return Category::find()
            ->forUser($userId)
            ->orderBy(['id' => SORT_DESC])
            ->all();
    }

    public function findById(int $id, int $userId): Category
    {
        $category = Category::find()->forUser($userId)->andWhere(['id' => $id])->one();

        if (!$category) {
            throw new NotFoundHttpException('Категория не найдена');
        }

        return $category;
    }

    public function create(int $userId, CategoryForm $form): Category
    {
        $form->user_id = $userId;

        if (!$form->validate()) {
            throw new DomainException('Ошибка валидации: ' . implode(', ', $form->getErrorSummary(true)));
        }

        $category = new Category();
        $category->user_id = $userId;
        $category->name = $form->name;
        $category->type = $form->type;

        if (!$category->save()) {
            throw new DomainException('Не удалось сохранить категорию в базу данных.');
        }

        return $category;
    }

    public function update(int $id, int $userId, CategoryForm $form): Category
    {
        $category = $this->findById($id, $userId);
        $form->user_id = $userId;

        if (!$form->validate()) {
            throw new DomainException('Ошибка валидации: ' . implode(', ', $form->getErrorSummary(true)));
        }

        $category->name = $form->name;
        $category->type = $form->type;

        if (!$category->save()) {
            throw new DomainException('Не удалось обновить категорию.');
        }

        return $category;
    }

    public function delete(int $id, int $userId): void
    {
        $category = $this->findById($id, $userId);

        $transaction = $this->db->beginTransaction();
        try {
            if (!$category->delete()) {
                throw new DomainException('Не удалось удалить категорию.');
            }
            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function getMapByType(int $userId, string $type): array
    {
        return Category::find()
            ->forUser($userId)
            ->andWhere(['type' => $type])
            ->select(['name', 'id'])
            ->indexBy('id')
            ->column();
    }

    public function createDefaultCategories(int $userId): void
    {
        $defaults = [
            ['name' => 'Зарплата', 'type' => Category::TYPE_INCOME],
            ['name' => 'Подработка', 'type' => Category::TYPE_INCOME],
            ['name' => 'Продукты', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Транспорт', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Жилье', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Развлечения', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Здоровье', 'type' => Category::TYPE_EXPENSE],
        ];

        $rows = [];
        foreach ($defaults as $data) {
            $rows[] = [
                $userId,
                $data['name'],
                $data['type'],
                new Expression('NOW()'),
                new Expression('NOW()'),
            ];
        }

        $this->db->createCommand()->batchInsert(
            Category::tableName(),
            ['user_id', 'name', 'type', 'created_at', 'updated_at'],
            $rows
        )->execute();
    }
}