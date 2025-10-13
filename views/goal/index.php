<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'Цели';
$this->registerJsFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', ['position' => View::POS_END]);
$this->registerCssFile('https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

$createUrl = Url::to(['goal/create']);
$updateUrl = Url::to(['goal/update']);
$deleteUrl = Url::to(['goal/delete']);
$viewUrl = Url::to(['goal/view']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Yii::$app->request->csrfToken ?>">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
        body {
            font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
            background: #f9f7f4;
            margin: 0;
            padding: 0;
            color: #4b453f;
        }
        .sidebar {
            width: 20rem;
            background-color: #b6b6b6;
            color: #8e8e8e;
            padding: 2rem 1rem;
            height: 100vh;
            position: fixed;
        }
        .sidebar h2 {
            font-size: 2.5rem;
            color: #2c2929;
            margin-bottom: 2rem;
            font-weight: 600;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li a {
            color: #1c1b1b;
            text-decoration: none;
            display: block;
            padding: 0.5rem 0;
            font-weight: 500;
        }
        .sidebar ul li a:hover { color: #535353; }
        .content {
            margin-left: 21rem;
            padding: 2rem;
        }
        .content h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(22rem, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .card {
            background: #fff;
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin: 0;
            font-size: 1.3rem;
            color: #4b453f;
        }
        .card p {
            margin: 0.25rem 0;
            color: #6b7280;
        }
        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }
        .actions button {
            border: none;
            background: none;
            cursor: pointer;
            color: #6b7280;
            font-size: 1rem;
        }
        .actions button:hover { color: #171716; }
        .btn-add {
            background-color: #a3c9c9;
            color: #222020;
            border: none;
            border-radius: 20px;
            padding: 0.6rem 1.2rem;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        .btn-add:hover {
            background-color: #8da4a4;
            color: #fff;
        }
        #formErrors {
            display: none;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h2>PastelFinance</h2>
    <ul>
        <li><a href="transaction">Транзакции</a></li>
        <li><a href="budget">Бюджеты</a></li>
        <li><a href="category">Категории</a></li>
        <li><a href="goal">Цели</a></li>
        <li><a href="settings">Настройки</a></li>
    </ul>
</div>
<div class="content">
    <h1>Цели</h1>
    <button class="btn-add" id="addGoalBtn" data-bs-toggle="modal" data-bs-target="#goalModal">Добавить цель</button>
    <div class="cards-container">
        <?php if (isset($dataProvider) && $dataProvider->models): ?>
            <?php foreach ($dataProvider->models as $goal): ?>
                <div class="card" data-id="<?= $goal->id ?>">
                    <div>
                        <h3><?= Html::encode($goal->name) ?></h3>
                        <p>Цель: <?= number_format($goal->target_amount, 2) ?></p>
                        <p>Текущая сумма: <?= number_format($goal->current_amount, 2) ?></p>
                        <p>Статус: <?= Html::encode($goal->displayStatus()) ?></p>
                        <p>Срок: <?= Html::encode($goal->deadline) ?></p>
                    </div>
                    <div class="actions">
                        <button class="editBtn" data-id="<?= $goal->id ?>">✏️</button>
                        <button class="deleteBtn" data-id="<?= $goal->id ?>">🗑️</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Нет доступных целей.</p>
        <?php endif; ?>
    </div>
</div>
<div class="modal fade" id="goalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="goalForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Создать/обновить цель</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="Goal[id]" id="goalId">
                <div class="mb-3">
                    <label class="form-label">Название</label>
                    <label for="goalName"></label><input type="text" class="form-control" name="Goal[name]" id="goalName" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Целевая сумма</label>
                    <label for="goalTarget"></label><input type="number" class="form-control" name="Goal[target_amount]" id="goalTarget" step="0.01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Текущая сумма</label>
                    <label for="goalCurrent"></label><input type="number" class="form-control" name="Goal[current_amount]" id="goalCurrent" step="0.01">
                </div>
                <div class="mb-3">
                    <label class="form-label">Срок</label>
                    <label for="goalDeadline"></label><input type="date" class="form-control" name="Goal[deadline]" id="goalDeadline" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Статус</label>
                    <label for="goalStatus"></label><select class="form-select" name="Goal[status]" id="goalStatus">
                        <option value="active">Активна</option>
                        <option value="completed">Завершена</option>
                        <option value="failed">Не выполнена</option>
                    </select>
                </div>
                <div id="formErrors" class="alert alert-danger mt-2" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-primary saveGoal">Сохранить</button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = new bootstrap.Modal(document.getElementById('goalModal'));
        let currentAction = 'create';
        const createUrl = '<?= $createUrl ?>';
        const updateUrl = '<?= $updateUrl ?>';
        const deleteUrl = '<?= $deleteUrl ?>';
        const viewUrl = '<?= $viewUrl ?>';

        document.getElementById('addGoalBtn').addEventListener('click', () => {
            currentAction = 'create';
            document.getElementById('goalForm').reset();
            document.getElementById('formErrors').textContent = '';
            document.getElementById('formErrors').style.display = 'none';
            modal.show();
        });

        document.querySelectorAll('.editBtn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                fetch(viewUrl + '?id=' + id, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(res => {
                        if (!res.ok) throw new Error('Ошибка сервера: ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        if (data.success && data.goal) {
                            const g = data.goal;
                            document.getElementById('goalId').value = g.id;
                            document.getElementById('goalName').value = g.name;
                            document.getElementById('goalTarget').value = g.target_amount;
                            document.getElementById('goalCurrent').value = g.current_amount;
                            document.getElementById('goalDeadline').value = g.deadline;
                            document.getElementById('goalStatus').value = g.status;
                            currentAction = 'update';
                            document.getElementById('formErrors').style.display = 'none';
                            modal.show();
                        } else {
                            document.getElementById('formErrors').textContent = data.message || 'Ошибка загрузки данных';
                            document.getElementById('formErrors').style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Edit error:', error);
                        document.getElementById('formErrors').textContent = 'Ошибка: ' + error.message;
                        document.getElementById('formErrors').style.display = 'block';
                    });
            });
        });


        document.querySelectorAll('.deleteBtn').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!confirm('Удалить цель?')) return;
                fetch(deleteUrl + '?id=' + btn.dataset.id, {
                    method: 'POST',
                    headers: {'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content}
                })
                    .then(res => {
                        if (res.status === 401 || res.status === 403) {
                            throw new Error('Требуется авторизация');
                        }
                        if (!res.ok) {
                            throw new Error('Ошибка сервера: ' + res.status);
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            document.getElementById('formErrors').textContent = data.message || 'Ошибка удаления';
                            document.getElementById('formErrors').style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Delete error:', error);
                        document.getElementById('formErrors').textContent = 'Ошибка: ' + error.message;
                        document.getElementById('formErrors').style.display = 'block';
                    });
            });
        });

        document.getElementById('goalForm').addEventListener('submit', e => {
            e.preventDefault();
            const id = document.getElementById('goalId').value;
            const data = new FormData(e.target);
            console.log('Form Data:', Array.from(data.entries()));
            const url = currentAction === 'create' ? createUrl : updateUrl + '?id=' + id;
            fetch(url, {
                method: 'POST',
                body: data,
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                    .then(res => {
                    if (res.status === 401 || res.status === 403) {
                        throw new Error('Требуется авторизация');
                    }
                    if (!res.ok) {
                        throw new Error('Ошибка сервера: ' + res.status);
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        document.getElementById('formErrors').textContent = data.message || 'Ошибка сохранения';
                        document.getElementById('formErrors').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('formErrors').textContent = 'Ошибка: ' + error.message;
                    document.getElementById('formErrors').style.display = 'block';
                });
        });
    });
</script>
</body>
</html>