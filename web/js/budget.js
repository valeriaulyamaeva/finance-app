document.addEventListener('DOMContentLoaded', () => {
    const { createUrl, updateUrl, deleteUrl, viewUrl, userCurrency } = budgetConfig;

    const modalEl = document.getElementById('budgetModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('budgetForm');
    const formErrors = document.getElementById('formErrors');
    const budgetCurrency = document.getElementById('budgetCurrency');
    const modalTitle = document.getElementById('modalTitle');

    const filterMonth = document.getElementById('filterMonth');
    const filterYear = document.getElementById('filterYear');

    let currentAction = 'create';
    let currentId = null;

    const handleFilterChange = () => {
        const month = filterMonth.value;
        const year = filterYear.value;
        const url = new URL(window.location.href);
        url.searchParams.set('month', month);
        url.searchParams.set('year', year);
        window.location.href = url.toString();
    };

    if (filterMonth) filterMonth.addEventListener('change', handleFilterChange);
    if (filterYear) filterYear.addEventListener('change', handleFilterChange);

    document.getElementById('addBudgetBtn')?.addEventListener('click', () => {
        currentAction = 'create';
        currentId = null;
        form.reset();
        if (budgetCurrency) budgetCurrency.value = userCurrency;
        modalTitle.textContent = 'Создать бюджет';
        hideError();
        modal.show();
    });

    document.querySelector('.cards-container')?.addEventListener('click', async (e) => {
        const target = e.target.closest('button');
        if (!target) return;

        if (target.classList.contains('editBtn')) {
            const id = target.dataset.id;
            fetch(`${viewUrl}?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.budget) {
                        const b = data.budget;
                        setField('Budget[name]', b.name);
                        setField('Budget[amount]', b.amount);
                        setField('Budget[period]', b.period);
                        setField('Budget[category_id]', b.category_id);
                        setField('Budget[start_date]', b.start_date);
                        setField('Budget[end_date]', b.end_date);
                        if (budgetCurrency) budgetCurrency.value = b.currency;

                        currentAction = 'update';
                        currentId = id;
                        modalTitle.textContent = 'Редактировать бюджет';
                        hideError();
                        modal.show();
                    }
                })
                .catch(err => showError('Ошибка загрузки данных'));
        }

        if (target.classList.contains('deleteBtn')) {
            const ok = await window.appConfirm({
                title: 'Удалить бюджет?',
                message: 'Связанные транзакции останутся, но бюджет будет удалён.',
                confirmText: 'Удалить',
                danger: true,
            });
            if (!ok) return;
            const id = target.dataset.id;

            fetch(`${deleteUrl}?id=${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                    else window.appToast(data.message || 'Ошибка удаления', 'error');
                })
                .catch(() => window.appToast('Ошибка сети при удалении', 'error'));
        }
    });

    document.querySelector('.saveBudget')?.addEventListener('click', () => {
        hideError();
        const formData = new FormData(form);
        const url = currentAction === 'create' ? createUrl : `${updateUrl}?id=${currentId}`;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showError(data.message || 'Ошибка валидации');
                }
            })
            .catch(err => showError('Ошибка при сохранении'));
    });

    function setField(name, value) {
        const el = form.querySelector(`[name="${name}"]`);
        if (el) el.value = value;
    }

    function showError(message) {
        if (formErrors) {
            formErrors.textContent = message;
            formErrors.classList.remove('d-none');
        } else {
            alert(message);
        }
    }

    function hideError() {
        if (formErrors) {
            formErrors.classList.add('d-none');
            formErrors.textContent = '';
        }
    }
});