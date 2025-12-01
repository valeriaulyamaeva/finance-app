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
?>

<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-gradient-primary text-white border-0 py-4">
                <h5 class="modal-title fw-bold fs-5 d-flex align-items-center gap-2" id="transactionModalLabel">
                    <i class="fas fa-exchange-alt"></i>
                    <span id="modalTransactionTitle">Новая транзакция</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>

            <div class="modal-body p-4 p-lg-5">
                <?php $form = ActiveForm::begin([
                    'id' => 'transactionForm',
                    'options' => ['class' => 'needs-validation'],
                    'fieldConfig' => ['options' => ['class' => 'mb-3']],
                ]); ?>

                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <?= Html::activeHiddenInput($transaction, 'id') ?>

                <div class="form-floating mb-3">
                    <?= $form->field($transaction, 'amount')->textInput([
                        'type' => 'number',
                        'step' => '0.01',
                        'class' => 'form-control rounded-3',
                        'placeholder' => '0.00',
                        'required' => true,
                        'min' => '0.01',
                    ])->label('Сумма')
                    ?>
                </div>

                <div class="form-floating mb-3">
                    <?= $form->field($transaction, 'date')->textInput([
                        'type' => 'date',
                        'class' => 'form-control rounded-3',
                        'required' => true,
                    ])->label('Дата') ?>
                </div>

                <div class="form-floating mb-3">
                    <?= $form->field($transaction, 'category_id')->dropDownList($categories, [
                        'class' => 'form-select rounded-3',
                        'prompt' => 'Выберите категорию',
                        'required' => true,
                    ])->label('Категория') ?>
                </div>

                <div id="goalSelector" style="display:none;">
                    <div class="form-floating mb-3">
                        <?= $form->field($transaction, 'goal_id')->dropDownList($goals ?? [], [
                            'class' => 'form-select rounded-3',
                            'prompt' => 'Выберите цель',
                        ])->label('Цель') ?>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <?= $form->field($transaction, 'description')->textarea([
                        'rows' => 3,
                        'class' => 'form-control rounded-3',
                        'placeholder' => 'Заметка (необязательно)',
                        'style' => 'height: 100px',
                    ])->label('Описание') ?>
                    <label for="<?= Html::getInputId($transaction, 'description') ?>"></label>
                </div>

                <div class="form-floating mb-3">
                    <select id="recurringFrequency" name="RecurringTransaction[frequency]" class="form-select rounded-3">
                        <option value="">Никогда</option>
                        <option value="daily">Ежедневно</option>
                        <option value="weekly">Еженедельно</option>
                        <option value="monthly">Ежемесячно</option>
                        <option value="yearly">Ежегодно</option>
                    </select>
                </div>

                <div id="nextDateWrapper" style="display:none;">
                    <div class="form-floating mb-3">
                        <input type="date" id="recurringNextDate" name="RecurringTransaction[next_date]" class="form-control rounded-3">
                        <label for="recurringNextDate">Следующая дата</label>
                    </div>
                </div>

                <div id="formErrors" class="alert alert-danger mt-4 d-none"></div>

                <?php ActiveForm::end(); ?>
            </div>

            <div class="modal-footer bg-light border-0 p-4 justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Отмена
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-5 shadow-sm saveTransaction">
                    <i class="fas fa-save me-2"></i>Сохранить
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="recurringModal" tabindex="-1" aria-labelledby="recurringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-gradient-primary text-white border-0 py-4">
                <h5 class="modal-title fw-bold fs-5">
                    <i class="fas fa-redo-alt me-2"></i> Регулярные платежи
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="recurringList">
            </div>
            <div class="modal-footer bg-light border-0 p-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
                    Закрыть
                </button>
                <button type="button" class="btn btn-primary rounded-pill" id="createRecurringInModalBtn">
                    <i class="fas fa-plus me-2"></i> Создать
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
