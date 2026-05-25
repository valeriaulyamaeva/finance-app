<?php
use app\models\Category;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$categories = $categories ?? ArrayHelper::map(Category::find()->where(['user_id' => Yii::$app->user->id])->all(), 'id', 'name');
$userCurrency = $user->currency ?? 'BYN';
?>

<div class="modal fade" id="budgetModal" tabindex="-1" aria-labelledby="budgetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 overflow-hidden">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-wallet me-2"></i>
                    <span id="modalTitle">Новый бюджет</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>

            <form id="budgetForm" class="modal-body p-4">
                <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                <div class="app-field mb-3">
                    <label for="budgetName" class="app-label">Название</label>
                    <input type="text" id="budgetName" name="Budget[name]" class="form-control"
                           placeholder="Например, Еда на месяц" required autofocus>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="app-field mb-3">
                            <label for="budgetAmount" class="app-label">Лимит</label>
                            <input type="number" step="0.01" min="0" id="budgetAmount" name="Budget[amount]"
                                   class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="app-field mb-3">
                            <label for="budgetCurrency" class="app-label">Валюта</label>
                            <select id="budgetCurrency" name="Budget[currency]" class="form-select">
                                <?php foreach (['BYN', 'USD', 'EUR', 'RUB'] as $c): ?>
                                    <option value="<?= $c ?>" <?= $c === $userCurrency ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="app-field mb-3">
                            <label for="budgetCategory" class="app-label">Категория</label>
                            <select id="budgetCategory" name="Budget[category_id]" class="form-select" required>
                                <option value="">Выберите категорию</option>
                                <?php foreach ($categories as $id => $name): ?>
                                    <option value="<?= $id ?>"><?= Html::encode($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="app-field mb-3">
                            <label for="budgetPeriod" class="app-label">Период</label>
                            <select id="budgetPeriod" name="Budget[period]" class="form-select" required>
                                <option value="daily">День</option>
                                <option value="weekly">Неделя</option>
                                <option value="monthly" selected>Месяц</option>
                                <option value="yearly">Год</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="app-field mb-3">
                            <label for="budgetStartDate" class="app-label">Начало</label>
                            <input type="date" id="budgetStartDate" name="Budget[start_date]" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="app-field mb-3">
                            <label for="budgetEndDate" class="app-label">
                                Конец <span class="text-muted small">(необязательно)</span>
                            </label>
                            <input type="date" id="budgetEndDate" name="Budget[end_date]" class="form-control">
                        </div>
                    </div>
                </div>

                <div id="formErrors" class="alert alert-danger mt-3 d-none"></div>
            </form>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    Отмена
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4 saveBudget">
                    <i class="fas fa-save me-1"></i> Сохранить
                </button>
            </div>
        </div>
    </div>
</div>
