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

            <div class="modal-body p-4">
                <?php $form = ActiveForm::begin([
                    'id' => 'transactionForm',
                    'enableClientValidation' => false,
                ]); ?>

                <?= Html::hiddenInput('id', '', ['id' => 'transaction-id']) ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="app-field mb-3">
                            <label for="transaction-amount" class="app-label">Сумма</label>
                            <input type="number" step="0.01" id="transaction-amount" name="amount" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="app-field mb-3">
                            <label for="transaction-date" class="app-label">Дата</label>
                            <input type="date" id="transaction-date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="app-field mb-3">
                    <label for="transaction-category_id" class="app-label">Категория</label>
                    <select id="transaction-category_id" name="category_id" class="form-select" required>
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $catId => $catName): ?>
                            <option value="<?= $catId ?>"><?= Html::encode($catName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="goalSelector" class="app-field mb-3" style="display:none;">
                    <label for="transaction-goal_id" class="app-label">Цель</label>
                    <select id="transaction-goal_id" name="goal_id" class="form-select">
                        <option value="">Выберите цель</option>
                        <?php foreach ($goals ?? [] as $gId => $gName): ?>
                            <option value="<?= $gId ?>"><?= Html::encode($gName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="app-field mb-3">
                    <label for="transaction-description" class="app-label">
                        Описание <span class="text-muted small">(необязательно)</span>
                    </label>
                    <textarea id="transaction-description" name="description" class="form-control" rows="2" placeholder="Заметка к транзакции"></textarea>
                </div>

                <input type="hidden" name="currency" value="<?= Html::encode(Yii::$app->user->identity->currency ?? 'BYN') ?>">

                <div id="formErrors" class="alert alert-danger d-none"></div>

                <?php ActiveForm::end(); ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Отмена
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4 saveTransaction">
                    <i class="fas fa-save me-1"></i> Сохранить
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

                <div id="recurringFormContainer" style="display: none;" class="recurring-form-card">
                    <h6 class="fw-bold mb-3" id="recurringFormTitle">
                        <i class="fas fa-clock me-2 text-muted"></i>Новый шаблон
                    </h6>
                    <form id="recurringForm">
                        <input type="hidden" id="recurring-id" name="id">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="app-field">
                                    <label for="recurring-amount" class="app-label">Сумма</label>
                                    <input type="number" step="0.01" name="amount" id="recurring-amount" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="app-field">
                                    <label for="recurring-currency" class="app-label">Валюта</label>
                                    <select name="currency" id="recurring-currency" class="form-select">
                                        <option value="BYN">BYN</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                        <option value="RUB">RUB</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="app-field">
                                    <label for="recurring-frequency" class="app-label">Частота</label>
                                    <select name="frequency" id="recurring-frequency" class="form-select" required>
                                        <option value="daily">Ежедневно</option>
                                        <option value="weekly">Еженедельно</option>
                                        <option value="monthly" selected>Ежемесячно</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="app-field">
                                    <label for="recurring-category_id" class="app-label">Категория</label>
                                    <?= Html::dropDownList('category_id', null, $categories, [
                                        'id' => 'recurring-category_id',
                                        'class' => 'form-select',
                                        'prompt' => 'Выберите категорию'
                                    ]) ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="app-field">
                                    <label for="recurring-next_date" class="app-label">Дата следующего платежа</label>
                                    <input type="date" name="next_date" id="recurring-next_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div id="recurringGoalSelector" class="mb-3" style="display:none;">
                            <div class="app-field">
                                <label for="recurring-goal_id" class="app-label">Цель</label>
                                <?= Html::dropDownList('goal_id', null, $goals ?? [], [
                                    'id' => 'recurring-goal_id',
                                    'class' => 'form-select',
                                    'prompt' => 'Выберите цель'
                                ]) ?>
                            </div>
                        </div>

                        <div class="app-field mb-3">
                            <label for="recurring-description" class="app-label">Описание <span class="text-muted small">(необязательно)</span></label>
                            <textarea name="description" id="recurring-description" class="form-control" rows="2" placeholder="Например: Аренда квартиры"></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 mt-2" style="border-top: 1px solid var(--border-color);">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" id="cancelRecurringBtn">Отмена</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-save me-1"></i> Сохранить
                            </button>
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