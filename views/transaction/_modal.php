<?php
use app\models\Category;
use app\models\Transaction;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$categories = ArrayHelper::map(Category::find()->all(), 'id', 'name');
$transaction = new Transaction();
?>

<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionModalLabel">Создать/обновить транзакцию</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <?php $form = ActiveForm::begin(['id' => 'transactionForm']); ?>
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                <?= $form->field($transaction, 'amount')->input('text', [
                    'placeholder' => 'Введите сумму',
                    'pattern' => '^[0-9]+([.,][0-9]{1,2})?$',
                    'title' => 'Введите число (например: 1500.50)',
                    'oninput' => "this.value = this.value.replace(',', '.')",
                    'required' => true,
                ]) ?>

                <?= $form->field($transaction, 'date')->input('date', ['required' => true]) ?>
                <?= $form->field($transaction, 'category_id')->dropDownList($categories, ['prompt' => 'Выберите категорию']) ?>
                <?= $form->field($transaction, 'description')->textarea(['rows' => 2]) ?>

                <?php ActiveForm::end(); ?>

                <div id="formErrors" class="text-danger mt-2"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <?= Html::button('Сохранить', ['class' => 'btn btn-primary saveTransaction']) ?>
            </div>
        </div>
    </div>
</div>
