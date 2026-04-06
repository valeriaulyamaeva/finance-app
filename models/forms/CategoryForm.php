<?php

declare(strict_types=1);

namespace app\models\forms;

use app\models\Category;
use yii\base\Model;

final class CategoryForm extends Model
{
    public ?string $name = null;
    public ?string $type = null;
    public ?int $user_id = null;

    public function rules(): array
    {
        return [
            [['name', 'type'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'trim'],
            ['type', 'in', 'range' => array_keys(Category::getTypes())],
            ['name', 'unique',
                'targetClass' => Category::class,
                'targetAttribute' => ['name', 'user_id'],
                'message' => 'Категория с таким названием уже существует.'
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => 'Название категории',
            'type' => 'Тип',
        ];
    }
}