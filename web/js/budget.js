document.addEventListener('DOMContentLoaded', () => {
    const { createUrl, updateUrl, deleteUrl, viewUrl, userCurrency } = budgetConfig;

    const modalEl = document.getElementById('budgetModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('budgetForm');
    const formErrors = document.getElementById('formErrors');
    const budgetCurrency = document.getElementById('budgetCurrency');
    const modalTitle = document.getElementById('modalTitle');
    let currentAction = 'create';
    let currentId = null;

    // === ОТКРЫТИЕ: Создать бюджет ===
    document.getElementById('addBudgetBtn').addEventListener('click', () => {
        currentAction = 'create';
        currentId = null;
        form.reset();
        budgetCurrency.value = userCurrency;
        modalTitle.textContent = 'Создать бюджет';
        hideError();
        modal.show();
    });

    // === РЕДАКТИРОВАТЬ / УДАЛИТЬ ===
    document.querySelector('.cards-container').addEventListener('click', (e) => {
        const target = e.target.closest('button');
        if (!target) return;

        // --- РЕДАКТИРОВАТЬ ---
        if (target.classList.contains('editBtn')) {
            const id = target.dataset.id;
            fetch(`${viewUrl}?id=${id}`)
                .then(res => res.ok ? res.json() : Promise.reject(new Error('Network error')))
                .then(data => {
                    if (data.success && data.budget) {
                        const b = data.budget;
                        setField('Budget[name]', b.name);
                        setField('Budget[amount]', parseFloat(b.display_amount.replace(/[^\d.-]/g, '')) || '');
                        setField('Budget[period]', b.period || '');
                        setField('Budget[category_id]', b.category_id || '');
                        setField('Budget[start_date]', b.start_date || '');
                        setField('Budget[end_date]', b.end_date || '');
                        budgetCurrency.value = b.display_currency || 'BYN';

                        currentAction = 'update';
                        currentId = id;
                        modalTitle.textContent = 'Редактировать бюджет';
                        hideError();
                        modal.show();
                    } else {
                        showError(data.message || 'Не удалось загрузить бюджет');
                    }
                })
                .catch(err => {
                    console.error('Edit error:', err);
                    showError('Ошибка загрузки: ' + err.message);
                });
        }

        // --- УДАЛИТЬ ---
        if (target.classList.contains('deleteBtn')) {
            if (!confirm('Удалить бюджет?')) return;
            const id = target.dataset.id;

            fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ id })
            })
                .then(res => res.ok ? res.json() : Promise.reject(new Error('Network error')))
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        showError(data.message || 'Ошибка удаления');
                    }
                })
                .catch(err => {
                    console.error('Delete error:', err);
                    showError('Ошибка: ' + err.message);
                });
        }
    });

    // === СОХРАНЕНИЕ ===
    document.querySelector('.saveBudget').addEventListener('click', () => {
        hideError();

        const formData = new FormData(form);
        formData.set('Budget[currency]', budgetCurrency.value);

        // Валидация дат
        const startDate = form.querySelector('[name="Budget[start_date]"]').value;
        const endDate = form.querySelector('[name="Budget[end_date]"]').value;
        if (endDate && startDate && endDate < startDate) {
            showError('Дата окончания не может быть раньше начала');
            return;
        }

        const url = currentAction === 'create' ? createUrl : `${updateUrl}?id=${currentId}`;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }
        })
            .then(res => res.ok ? res.json() : Promise.reject(new Error('Network error')))
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showError(data.message || 'Ошибка сохранения');
                }
            })
            .catch(err => {
                console.error('Save error:', err);
                showError('Ошибка: ' + err.message);
            });
    });

    // === ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ===
    function setField(name, value) {
        const el = form.querySelector(`[name="${name}"]`);
        if (el) el.value = value;
    }

    function showError(message) {
        formErrors.textContent = message;
        formErrors.classList.remove('d-none');
    }

    function hideError() {
        formErrors.classList.add('d-none');
        formErrors.textContent = '';
    }
});