document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('goalModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('goalForm');
    const formErrors = document.getElementById('formErrors');
    const modalTitle = document.getElementById('goalModalTitle');

    let currentAction = 'create';
    let currentId = null;

    document.getElementById('addGoalBtn').addEventListener('click', () => {
        currentAction = 'create';
        currentId = null;
        form.reset();
        document.getElementById('goalId').value = '';
        document.getElementById('goalCurrency').value = userCurrency;
        modalTitle.textContent = 'Создать цель';
        formErrors.classList.add('d-none');
        modal.show();
    });

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            fetch(`${goalUrls.view}?id=${id}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.goal) {
                        const g = data.goal;
                        document.getElementById('goalId').value = g.id;
                        document.getElementById('goalName').value = g.name;
                        document.getElementById('goalTarget').value = g.display_target_amount || g.target_amount;
                        document.getElementById('goalCurrent').value = g.display_current_amount || g.current_amount || 0;
                        document.getElementById('goalDeadline').value = g.deadline;
                        document.getElementById('goalStatus').value = g.status;

                        currentAction = 'update';
                        currentId = g.id;
                        modalTitle.textContent = 'Редактировать цель';
                        formErrors.classList.add('d-none');
                        modal.show();
                    } else {
                        showError(data.message || 'Не удалось загрузить цель');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showError('Ошибка загрузки данных');
                });
        });
    });

    document.querySelectorAll('.deleteBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!confirm('Удалить цель навсегда?')) return;

            fetch(`${goalUrls.delete}?id=${btn.dataset.id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showError(data.message || 'Ошибка удаления');
                    }
                })
                .catch(() => showError('Ошибка сети'));
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        formErrors.classList.add('d-none');

        const formData = new FormData(form);
        formData.set('Goal[currency]', userCurrency);

        const target = formData.get('Goal[target_amount]');
        const current = formData.get('Goal[current_amount]') || '0';
        if (target) formData.set('Goal[target_amount]', parseFloat(target));
        if (current) formData.set('Goal[current_amount]', parseFloat(current));

        const url = currentAction === 'create'
            ? goalUrls.create
            : `${goalUrls.update}?id=${currentId}`;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showError(data.message || 'Ошибка сохранения');
                }
            })
            .catch(err => {
                console.error(err);
                showError('Ошибка соединения');
            });
    });

    function showError(message) {
        formErrors.textContent = message;
        formErrors.classList.remove('d-none');
    }
});