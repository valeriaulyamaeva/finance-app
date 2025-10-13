<?php
use app\models\Category;
use app\models\Budget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$categories = ArrayHelper::map(Category::find()->all(), 'id', 'name');
$budget = new Budget();
?>

<div class="modal fade" id="budgetModal" tabindex="-1" aria-labelledby="budgetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="budgetModalLabel">Создать/обновить бюджет</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>

            <div class="modal-body">
                <?php $form = ActiveForm::begin(['id' => 'budgetForm']); ?>
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                <?= $form->field($budget, 'name')->textInput(['placeholder' => 'Название бюджета', 'required' => true]) ?>

                <?= $form->field($budget, 'amount')->input('text', [
                    'placeholder' => 'Сумма бюджета',
                    'pattern' => '^[0-9]+([.,][0-9]{1,2})?$',
                    'title' => 'Введите число (например: 1500.50)',
                    'oninput' => "this.value = this.value.replace(',', '.')",
                    'required' => true,
                ]) ?>

                <?= $form->field($budget, 'period')->dropDownList([
                    'daily' => 'День',
                    'weekly' => 'Неделя',
                    'monthly' => 'Месяц',
                    'yearly' => 'Год',
                ], ['prompt' => 'Выберите период', 'required' => true]) ?>

                <?= $form->field($budget, 'category_id')->dropDownList($categories, ['prompt' => 'Выберите категорию']) ?>

                <?= $form->field($budget, 'start_date')->input('date', ['required' => true]) ?>
                <?= $form->field($budget, 'end_date')->input('date') ?>

                <?php ActiveForm::end(); ?>

                <div id="formErrors" class="text-danger mt-2"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <?= Html::button('Сохранить', ['class' => 'btn btn-primary saveBudget']) ?>
            </div>
        </div>
    </div>
</div>
