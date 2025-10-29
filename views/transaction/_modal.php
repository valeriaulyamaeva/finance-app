<?php
use app\models\Category;
use app\models\Transaction;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
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
                <?= Html::activeHiddenInput($transaction, 'id') ?>

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
                    <select id="recurringFrequency" name="RecurringTransaction[frequency]" class="form-select">
                        <option value="">Никогда</option>
                        <option value="daily">Ежедневно</option>
                        <option value="weekly">Еженедельно</option>
                        <option value="monthly">Ежемесячно</option>
                    </select>
                </div>

                <div class="mb-3" id="nextDateWrapper" style="display:none;">
                    <label class="form-label">Следующая дата</label>
                    <input type="date" id="recurringNextDate" name="RecurringTransaction[next_date]" class="form-control" required>
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

<div class="modal fade" id="recurringModal" tabindex="-1" aria-labelledby="recurringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recurringModalLabel">Регулярные платежи</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
                <div id="recurringList" class="transactions-container">
                </div>
                <div id="recurringEmpty" class="text-center text-muted" style="display:none;">
                    <p>Нет регулярных платежей.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="createRecurringInModalBtn">
                    Создать новый
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$checkTypeUrl = Url::to(['category/type']);
$script = <<<JS
$('#transaction-category_id').on('change', function() {
    var categoryId = $(this).val();
    var goalSelector = $('#goalSelector');
    var goalInput = $('#transaction-goal_id');
    
    if (!categoryId) {
        goalSelector.hide();
        goalInput.val('').prop('required', false);
        return;
    }
    
    $.getJSON('$checkTypeUrl', {id: categoryId}, function(res) {
        if (res.type === 'goal') {
            goalSelector.show();
            goalInput.prop('required', true);
        } else {
            goalSelector.hide();
            goalInput.val('').prop('required', false);
        }
    }).fail(function() {
        goalSelector.hide();
        goalInput.val('').prop('required', false);
    });
});

$('#recurringFrequency').on('change', function() {
    var nextDateWrapper = $('#nextDateWrapper');
    var nextDateInput = $('#recurringNextDate');
    if ($(this).val()) {
        nextDateWrapper.show();
        nextDateInput.prop('required', true);
    } else {
        nextDateWrapper.hide();
        nextDateInput.val('').prop('required', false);
    }
});
JS;
$this->registerJs($script);
?>
