<?php

declare(strict_types=1);

use app\models\Category;
use app\models\forms\TransactionForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var array $goals */
/** @var TransactionForm $modelForm */

$modelForm = $modelForm ?? new TransactionForm();

$categories = ArrayHelper::map(
    Category::find()->where(['user_id' => Yii::$app->user->id])->all(),
    'id',
    'name'
);
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
                    'enableClientValidation' => false,
                ]); ?>

                <?= Html::hiddenInput('id', '', ['id' => 'transaction-id']) ?>

                <div class="mb-3">
                    <?= $form->field($modelForm, 'amount')->textInput([
                        'id' => 'transaction-amount',
                        'type' => 'number',
                        'step' => '0.01',
                        'class' => 'form-control rounded-3',
                        'placeholder' => '0.00'
                    ])->label('Сумма') ?>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= $form->field($modelForm, 'date')->textInput([
                            'id' => 'transaction-date',
                            'type' => 'date',
                            'value' => date('Y-m-d')
                        ])->label('Дата') ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?= $form->field($modelForm, 'category_id')->dropDownList($categories, [
                            'id' => 'transaction-category_id',
                            'prompt' => 'Выберите категорию',
                            'class' => 'form-select rounded-3'
                        ])->label('Категория') ?>
                    </div>
                </div>

                <div id="goalSelector" class="mb-3" style="display:none;">
                    <?= $form->field($modelForm, 'goal_id')->dropDownList($goals ?? [], [
                        'id' => 'transaction-goal_id',
                        'prompt' => 'Выберите цель',
                        'class' => 'form-select rounded-3'
                    ])->label('Цель') ?>
                </div>

                <div class="mb-3">
                    <?= $form->field($modelForm, 'description')->textarea([
                        'id' => 'transaction-description',
                        'rows' => 2,
                        'class' => 'form-control rounded-3'
                    ])->label('Описание') ?>
                </div>

                <div id="formErrors" class="alert alert-danger d-none"></div>

                <?php ActiveForm::end(); ?>
            </div>

            <div class="modal-footer border-0 p-4 justify-content-between">
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

            <div class="modal-body p-4">
                <div id="recurringItemsList" class="mb-4"></div>

                <div id="recurringFormContainer" style="display: none; background: var(--bg-surface-2); border: 1px solid var(--border-color);" class="p-4 rounded-4">
                    <h6 class="fw-bold mb-3" id="recurringFormTitle">Новый шаблон</h6>
                    <form id="recurringForm">
                        <input type="hidden" id="recurring-id" name="id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Сумма</label>
                                <input type="number" step="0.01" name="amount" id="recurring-amount" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Частота</label>
                                <select name="frequency" id="recurring-frequency" class="form-select" required>
                                    <option value="daily">Ежедневно</option>
                                    <option value="weekly">Еженедельно</option>
                                    <option value="monthly" selected>Ежемесячно</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Категория</label>
                                <?= Html::dropDownList('category_id', null, $categories, [
                                    'id' => 'recurring-category_id',
                                    'class' => 'form-select',
                                    'prompt' => 'Выберите категорию'
                                ]) ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold small">Дата следующего платежа</label>
                                <input type="date" name="next_date" id="recurring-next_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div id="recurringGoalSelector" class="mb-3" style="display:none;">
                            <label class="form-label fw-semibold small">Цель</label>
                            <?= Html::dropDownList('goal_id', null, $goals ?? [], [
                                'id' => 'recurring-goal_id',
                                'class' => 'form-select',
                                'prompt' => 'Выберите цель'
                            ]) ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Описание</label>
                            <textarea name="description" id="recurring-description" class="form-control" rows="2" placeholder="Например: Аренда квартиры"></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="cancelRecurringBtn">Отмена</button>
                            <button type="submit" class="btn btn-success btn-sm rounded-pill px-4">Сохранить шаблон</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-footer border-0 p-4 justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" id="createRecurringInModalBtn">
                    <i class="fas fa-plus me-2"></i> Создать
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$checkTypeUrl = Url::to(['category/type']);
$script = <<<JS
function handleCategoryTypeChange(categoryId, goalSelectorId, goalInputId) {
    var goalSelector = $('#' + goalSelectorId);
    var goalInput = $('#' + goalInputId);
    
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
}

$('#transaction-category_id').on('change', function() {
    handleCategoryTypeChange($(this).val(), 'goalSelector', 'transaction-goal_id');
});

$('#recurring-category_id').on('change', function() {
    handleCategoryTypeChange($(this).val(), 'recurringGoalSelector', 'recurring-goal_id');
});

$('#transactionForm, #recurringForm').on('reset', function() {
    $('#goalSelector, #recurringGoalSelector').hide();
});
JS;
$this->registerJs($script);
?>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    }
    #recurringItemsList .recurring-item {
        border-radius: 12px;
        transition: all 0.2s;
        border: 1px solid #eee;
        padding: 12px;
        margin-bottom: 10px;
    }
    #recurringItemsList .recurring-item:hover {
        background-color: #f8fafc;
        border-color: #e2e8f0;
    }
</style>