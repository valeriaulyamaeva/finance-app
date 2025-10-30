<?php
use app\models\Category;
use app\models\Budget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$categories = ArrayHelper::map(Category::find()->all(), 'id', 'name');
$budget = new Budget(['currency' => Yii::$app->user->identity->currency ?? 'BYN']);
?>

<div class="modal fade" id="budgetModal" tabindex="-1" aria-labelledby="budgetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="budgetModalLabel">
                    <span id="modalTitle">Создать бюджет</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>

            <div class="modal-body p-4">
                <?php $form = ActiveForm::begin([
                    'id' => 'budgetForm',
                    'options' => ['class' => 'needs-validation'],
                    'fieldConfig' => ['options' => ['class' => 'mb-3']],
                ]); ?>

                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
                <input type="hidden" name="Budget[currency]" id="budgetCurrency" value="<?= Html::encode($user->currency ?? 'BYN') ?>">

                <div class="form-floating mb-3">
                    <?= $form->field($budget, 'name')->textInput([
                        'class' => 'form-control rounded-3',
                        'placeholder' => 'Название бюджета',
                        'required' => true,
                        'autofocus' => true,
                    ])->label(false) ?>
                </div>

                <div class="form-floating mb-3">
                    <?= $form->field($budget, 'amount')->textInput([
                        'type' => 'number',
                        'class' => 'form-control rounded-3',
                        'placeholder' => 'Сумма',
                        'step' => '0.01',
                        'min' => '0',
                        'required' => true,
                    ])->label(false) ?>
                </div>

                <div class="mb-3">
                    <?= $form->field($budget, 'period')->dropDownList([
                        'daily' => 'День',
                        'weekly' => 'Неделя',
                        'monthly' => 'Месяц',
                        'yearly' => 'Год',
                    ], [
                        'class' => 'form-select rounded-3',
                        'prompt' => 'Выберите период',
                        'required' => true,
                    ])->label('Период') ?>
                </div>

                <div class="mb-3">
                    <?= $form->field($budget, 'category_id')->dropDownList($categories, [
                        'class' => 'form-select rounded-3',
                        'prompt' => 'Выберите категорию',
                    ])->label('Категория') ?>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <?= $form->field($budget, 'start_date')->textInput([
                                'type' => 'date',
                                'class' => 'form-control rounded-3',
                                'required' => true,
                            ])->label(false) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <?= $form->field($budget, 'end_date')->textInput([
                                'type' => 'date',
                                'class' => 'form-control rounded-3',
                            ])->label(false) ?>
                        </div>
                    </div>
                </div>

                <div id="formErrors" class="alert alert-danger mt-3 d-none"></div>

                <?php ActiveForm::end(); ?>
            </div>

            <div class="modal-footer bg-light border-0 p-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Отмена
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-5 saveBudget">
                    <i class="fas fa-save me-1"></i> Сохранить
                </button>
            </div>
        </div>
    </div>
</div>