<?php

namespace app\services;

use app\models\Category;
use RuntimeException;
use Throwable;
use Yii;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

class CategoryService
{
    /**
     * Получить все категории пользователя
     *
     * @param int $userId
     * @return Category[]
     */
    public function getAllByUser(int $userId): array
    {
        return Category::find()
            ->where(['user_id' => $userId])
            ->orderBy(['id' => SORT_DESC])
            ->all();
    }

    /**
     * Найти категорию по id
     *
     * @param int $id
     * @param int $userId
     * @return Category
     * @throws NotFoundHttpException
     */
    public function findById(int $id, int $userId): Category
    {
        $category = Category::findOne(['id' => $id, 'user_id' => $userId]);
        if (!$category) {
            throw new NotFoundHttpException('Категория не найдена');
        }
        return $category;
    }

    /**
     * Создать категорию
     *
     * @param int $userId
     * @param array $data
     * @return Category
     * @throws Exception
     */
    public function create(int $userId, array $data): Category
    {
        $category = new Category();
        $category->user_id = $userId;
        $category->load($data, '');
        Yii::info('CREATE CATEGORY: Data = ' . json_encode($data), __METHOD__);
        if (!$category->validate()) {
            Yii::error('CREATE CATEGORY VALIDATION ERRORS: ' . json_encode($category->errors), __METHOD__);
            throw new RuntimeException('Не удалось создать категорию: ' . json_encode($category->errors));
        }
        if (!$category->save(false)) {
            Yii::error('CREATE CATEGORY SAVE FAILED: ' . json_encode($category->attributes), __METHOD__);
            throw new RuntimeException('Не удалось сохранить категорию');
        }
        return $category;
    }

    /**
     * Обновить категорию
     *
     * @param int $id
     * @param int $userId
     * @param array $data
     * @return Category
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function update(int $id, int $userId, array $data): Category
    {
        $category = $this->findById($id, $userId);
        $category->load($data, '');
        Yii::info('UPDATE CATEGORY: ID = ' . $id . ', Data = ' . json_encode($data), __METHOD__);
        if (!$category->validate()) {
            Yii::error('UPDATE CATEGORY VALIDATION ERRORS: ' . json_encode($category->errors), __METHOD__);
            throw new RuntimeException('Не удалось обновить категорию: ' . json_encode($category->errors));
        }
        if (!$category->save(false)) {
            Yii::error('UPDATE CATEGORY SAVE FAILED: ' . json_encode($category->attributes), __METHOD__);
            throw new RuntimeException('Не удалось сохранить категорию');
        }
        return $category;
    }

    /**
     * Удалить категорию
     *
     * @param int $id
     * @param int $userId
     * @throws NotFoundHttpException
     * @throws Exception|Throwable
     */
    public function delete(int $id, int $userId): void
    {
        $category = $this->findById($id, $userId);
        Yii::info('DELETE CATEGORY: ID = ' . $id, __METHOD__);
        if (!$category->delete()) {
            Yii::error('DELETE CATEGORY FAILED: ' . json_encode($category->attributes), __METHOD__);
            throw new RuntimeException('Не удалось удалить категорию');
        }
    }
}