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
        $defaults = self::getDefaultCategories();

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

    /**
     * The full default category list. Public so migrations can backfill existing users.
     * @return array<int, array{name: string, type: string}>
     */
    public static function getDefaultCategories(): array
    {
        return [
            // Income
            ['name' => 'Зарплата', 'type' => Category::TYPE_INCOME],
            ['name' => 'Подработка', 'type' => Category::TYPE_INCOME],
            ['name' => 'Кэшбек', 'type' => Category::TYPE_INCOME],
            ['name' => 'Возврат', 'type' => Category::TYPE_INCOME],
            ['name' => 'Переводы (доход)', 'type' => Category::TYPE_INCOME],

            // Expense — daily essentials
            ['name' => 'Продукты', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Еда', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Рестораны', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Алкоголь', 'type' => Category::TYPE_EXPENSE],

            // Expense — transport
            ['name' => 'Транспорт', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Топливо', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Авто', 'type' => Category::TYPE_EXPENSE],

            // Expense — home & utilities
            ['name' => 'Жилье', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Коммунальные', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Связь', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Дом', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Электроника', 'type' => Category::TYPE_EXPENSE],

            // Expense — recurring digital
            ['name' => 'Подписки', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Автосписания', 'type' => Category::TYPE_EXPENSE],

            // Expense — lifestyle
            ['name' => 'Развлечения', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Здоровье', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Красота', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Спорт', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Одежда', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Хобби', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Питомцы', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Путешествия', 'type' => Category::TYPE_EXPENSE],

            // Expense — other
            ['name' => 'Образование', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Маркетплейс', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Благотворительность', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Переводы (расход)', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Прочее', 'type' => Category::TYPE_EXPENSE],
        ];
    }
}