document.addEventListener('DOMContentLoaded', () => {
    const { createUrl, updateUrl, deleteUrl, viewUrl, userCurrency } = goalConfig;

    const modalEl = document.getElementById('goalModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('goalForm');
    const formErrors = document.getElementById('formErrors');
    const modalTitle = document.getElementById('goalModalTitle');

    let currentAction = 'create';
    let currentId = null;

    document.getElementById('addGoalBtn')?.addEventListener('click', () => {
        currentAction = 'create';
        currentId = null;
        form.reset();
        modalTitle.textContent = 'Новая финансовая цель';
        hideError();
        modal.show();
    });

    document.querySelector('.cards-container')?.addEventListener('click', (e) => {
        const target = e.target.closest('button');
        if (!target) return;

        const id = target.dataset.id;

        if (target.classList.contains('editBtn')) {
            fetch(`${viewUrl}?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        currentAction = 'update';
                        currentId = id;
                        modalTitle.textContent = 'Редактирование цели';

                        fillForm(data.goal);
                        hideError();
                        modal.show();
                    }
                });
        }

        if (target.classList.contains('deleteBtn')) {
            if (confirm('Вы уверены, что хотите удалить цель?')) {
                fetch(`${deleteUrl}?id=${id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': yii.getCsrfToken() }
                })
                    .then(res => res.json())
                    .then(data => data.success ? location.reload() : alert(data.message));
            }
        }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const url = currentAction === 'create' ? createUrl : `${updateUrl}?id=${currentId}`;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': yii.getCsrfToken() }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showError(data.message || 'Ошибка валидации');
                }
            })
            .catch(() => showError('Ошибка сети'));
    });

    function fillForm(goal) {
        document.getElementById('goalName').value = goal.name;
        document.getElementById('goalTarget').value = goal.target_amount;
        document.getElementById('goalDeadline').value = goal.deadline;
        document.getElementById('goalCurrency').value = goal.currency;

        const currentField = document.getElementById('goalCurrent');
        if (currentField) currentField.value = goal.current_amount;
    }

    function showError(msg) {
        formErrors.textContent = msg;
        formErrors.classList.remove('d-none');
    }

    function hideError() {
        formErrors.classList.add('d-none');
    }
});