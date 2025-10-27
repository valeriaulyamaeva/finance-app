<?php
use app\models\Category;
use app\models\Transaction;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$categories = ArrayHelper::map(
    Category::find()->where(['user_id' => Yii::$app->user->id])->all(),
    'id',
    'name'
);
$transaction = new Transaction();
/** @var array $goals */
?>

<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionModalLabel">Создать/обновить транзакцию</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>

            <div class="modal-body">
                <?php $form = ActiveForm::begin(['id' => 'transactionForm']); ?>
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <?= Html::activeHiddenInput($transaction, 'id') ?> <!-- Скрытое поле для ID -->

                <?= $form->field($transaction, 'amount')->textInput([
                    'placeholder' => 'Введите сумму',
                    'pattern' => '^[0-9]+([.,][0-9]{1,2})?$',
                    'title' => 'Введите число (например: 1500.50)',
                    'oninput' => "this.value = this.value.replace(',', '.')",
                    'required' => true,
                ]) ?>

                <?= $form->field($transaction, 'date')->input('date', ['required' => true]) ?>

                <?= $form->field($transaction, 'category_id')->dropDownList($categories, [
                    'prompt' => 'Выберите категорию',
                    'required' => true,
                ]) ?>

                <div id="goalSelector" style="display:none;">
                    <?= $form->field($transaction, 'goal_id')->dropDownList($goals, [
                        'prompt' => 'Выберите цель',
                    ]) ?>
                </div>

                <?= $form->field($transaction, 'description')->textarea(['rows' => 2, 'placeholder' => 'Описание']) ?>

                <hr>
                <h6>Повторяющаяся транзакция</h6>
                <div class="mb-3">
                    <label class="form-label">Повторять</label>
                    <label for="recurringFrequency"></label><select id="recurringFrequency" name="RecurringTransaction[frequency]" class="form-select">
                        <option value="">Никогда</option>
                        <option value="daily">Ежедневно</option>
                        <option value="weekly">Еженедельно</option>
                        <option value="monthly">Ежемесячно</option>
                    </select>
                </div>

                <div class="mb-3" id="nextDateWrapper" style="display:none;">
                    <label class="form-label">Следующая дата</label>
                    <label for="recurringNextDate"></label><input type="date" id="recurringNextDate" name="RecurringTransaction[next_date]" class="form-control">
                </div>

                <div id="formErrors" class="text-danger mt-2"></div>
                <?php ActiveForm::end(); ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <?= Html::button('Сохранить', ['class' => 'btn btn-primary saveTransaction']) ?>
            </div>
        </div>
    </div>
</div>