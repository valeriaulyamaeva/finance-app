document.addEventListener('DOMContentLoaded', () => {
    const modal = new bootstrap.Modal(document.getElementById('goalModal'));
    let currentAction = 'create';

    const goalForm = document.getElementById('goalForm');
    const formErrors = document.getElementById('formErrors');
    const goalCurrency = document.getElementById('goalCurrency');
    const goalCurrencyLabel = document.getElementById('goalCurrencyLabel');

    goalCurrency.value = userCurrency;
    goalCurrencyLabel.textContent = userCurrency;

    document.getElementById('addGoalBtn').addEventListener('click', () => {
        currentAction = 'create';
        goalForm.reset();
        formErrors.style.display = 'none';
        goalCurrency.value = userCurrency;
        goalCurrencyLabel.textContent = userCurrency;
        modal.show();
    });

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            fetch(`${goalUrls.view}?id=${id}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.goal) {
                        const g = data.goal;
                        document.getElementById('goalId').value = g.id;
                        document.getElementById('goalName').value = g.name;
                        document.getElementById('goalTarget').value = parseFloat(g.display_target_amount ?? g.target_amount);
                        document.getElementById('goalCurrent').value = parseFloat(g.display_current_amount ?? g.current_amount);
                        document.getElementById('goalDeadline').value = g.deadline;
                        document.getElementById('goalStatus').value = g.status;
                        goalCurrency.value = userCurrency;
                        goalCurrencyLabel.textContent = userCurrency;
                        currentAction = 'update';
                        formErrors.style.display = 'none';
                        modal.show();
                    } else {
                        formErrors.textContent = data.message || 'Ошибка загрузки данных';
                        formErrors.style.display = 'block';
                    }
                })
                .catch(error => {
                    formErrors.textContent = 'Ошибка: ' + error.message;
                    formErrors.style.display = 'block';
                });
        });
    });

    document.querySelectorAll('.deleteBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!confirm('Удалить цель?')) return;
            fetch(goalUrls.delete + '?id=' + btn.dataset.id, {
                method: 'POST',
                headers: {'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content}
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                    else {
                        formErrors.textContent = data.message || 'Ошибка удаления';
                        formErrors.style.display = 'block';
                    }
                })
                .catch(error => {
                    formErrors.textContent = 'Ошибка: ' + error.message;
                    formErrors.style.display = 'block';
                });
        });
    });

    goalForm.addEventListener('submit', e => {
        e.preventDefault();
        const id = document.getElementById('goalId').value;
        const formData = new FormData(goalForm);

        if (formData.get('Goal[target_amount]')) {
            formData.set('Goal[target_amount]', parseFloat(formData.get('Goal[target_amount]')));
        }
        if (formData.get('Goal[current_amount]')) {
            formData.set('Goal[current_amount]', parseFloat(formData.get('Goal[current_amount]')));
        }

        formData.set('Goal[currency]', userCurrency);

        const url = currentAction === 'create' ? goalUrls.create : `${goalUrls.update}?id=${id}`;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else {
                    formErrors.textContent = data.message || 'Ошибка сохранения';
                    formErrors.style.display = 'block';
                }
            })
            .catch(error => {
                formErrors.textContent = 'Ошибка: ' + error.message;
                formErrors.style.display = 'block';
            });
    });
});