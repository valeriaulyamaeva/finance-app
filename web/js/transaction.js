document.addEventListener('DOMContentLoaded', () => {
    if (!window.transactionConfig) {
        console.error('transactionConfig not loaded');
        return;
    }
    const config = window.transactionConfig;
    const urls = config.urls || {};

    const modalEl = document.getElementById('transactionModal');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    const form = document.getElementById('transactionForm');
    const formErrors = document.getElementById('formErrors');

    const toggleGoalField = (categoryId) => {
        const goalSelector = document.getElementById('goalSelector');
        if (!goalSelector) return;
        if (!categoryId) { goalSelector.style.display = 'none'; return; }
        fetch(`/category/type?id=${categoryId}`)
            .then(res => res.json())
            .then(data => {
                goalSelector.style.display = (data.type === 'goal') ? 'block' : 'none';
            });
    };

    document.getElementById('createTransactionBtn')?.addEventListener('click', () => {
        if (!form || !modal) return;
        form.reset();
        const idEl = document.getElementById('transaction-id');
        if (idEl) idEl.value = '';
        const titleEl = document.getElementById('modalTransactionTitle');
        if (titleEl) titleEl.textContent = 'Новая транзакция';
        formErrors?.classList.add('d-none');
        modal.show();
    });

    const cardsContainer = document.querySelector('.cards-container');

    cardsContainer?.addEventListener('click', (e) => {
        const btn = e.target.closest('.js-update');
        if (!btn) return;

        fetch(`${urls.view}?id=${btn.dataset.id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const t = data.transaction;
                    document.getElementById('transaction-id').value = t.id;
                    document.getElementById('transaction-amount').value = t.amount;
                    document.getElementById('transaction-date').value = t.date;
                    document.getElementById('transaction-category_id').value = t.category_id;
                    document.getElementById('transaction-goal_id').value = t.goal_id || '';

                    toggleGoalField(t.category_id);
                    document.getElementById('modalTransactionTitle').textContent = 'Редактирование';
                    modal?.show();
                }
            });
    });

    document.querySelector('.saveTransaction')?.addEventListener('click', () => {
        if (!form) return;
        const formData = new FormData(form);
        const id = document.getElementById('transaction-id').value;
        const url = id ? `${urls.update}?id=${id}` : urls.create;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    if (formErrors) {
                        formErrors.textContent = data.message;
                        formErrors.classList.remove('d-none');
                    }
                }
            });
    });

    cardsContainer?.addEventListener('click', async (e) => {
        const deleteBtn = e.target.closest('.js-delete');
        if (!deleteBtn) return;

        const confirmed = await window.appConfirm({
            title: 'Удалить транзакцию?',
            message: 'Это действие нельзя отменить.',
            confirmText: 'Удалить',
            danger: true,
        });
        if (!confirmed) return;

        const id = deleteBtn.dataset.id;

        fetch(`${urls.delete}?id=${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    window.appToast('Ошибка: ' + data.message, 'error');
                }
            })
            .catch(err => console.error('Ошибка:', err));
    });

    // Filters
    document.getElementById('applyFilter')?.addEventListener('click', () => {
        const start = document.getElementById('filterStart').value;
        const end = document.getElementById('filterEnd').value;
        const categoryId = document.getElementById('filterCategory').value;
        const params = new URLSearchParams();
        if (start) params.set('start', start);
        if (end) params.set('end', end);
        if (categoryId) params.set('category_id', categoryId);
        window.location.href = '/transaction?' + params.toString();
    });

    document.getElementById('resetFilter')?.addEventListener('click', () => {
        window.location.href = '/transaction';
    });

    // ─── Recurring transactions ──────────────────────────────────
    const recurringModalEl = document.getElementById('recurringModal');
    const recurringModal = recurringModalEl ? new bootstrap.Modal(recurringModalEl) : null;
    const recurringListContainer = document.getElementById('recurringItemsList');
    const recurringFormContainer = document.getElementById('recurringFormContainer');
    const recurringForm = document.getElementById('recurringForm');

    function loadRecurringList() {
        if (!recurringListContainer) return;
        if (recurringFormContainer) recurringFormContainer.style.display = 'none';
        recurringListContainer.style.display = 'block';
        recurringListContainer.innerHTML = '<p class="text-center text-muted py-3">Загрузка...</p>';

        fetch(urls.recurringList)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    renderRecurringList(res.data || []);
                } else {
                    recurringListContainer.innerHTML = '<p class="text-center text-muted py-3">Не удалось загрузить</p>';
                }
            })
            .catch(() => {
                recurringListContainer.innerHTML = '<p class="text-center text-muted py-3">Ошибка соединения</p>';
            });
    }

    function renderRecurringList(data) {
        if (!recurringListContainer) return;
        if (data.length === 0) {
            recurringListContainer.innerHTML = '<p class="text-muted text-center py-3">У вас пока нет регулярных платежей</p>';
            return;
        }

        recurringListContainer.innerHTML = data.map(item => `
            <div class="recurring-item">
                <div class="recurring-info">
                    <div class="recurring-header">
                        <span class="recurring-amount">${item.amount} ${item.currency}</span>
                        <span class="recurring-badge">${item.frequency_label}</span>
                    </div>
                    <div class="recurring-meta">
                        <i class="fas fa-tag"></i> ${item.category}
                        <span class="mx-2">•</span>
                        <i class="far fa-calendar-alt"></i> ${item.next_date}
                    </div>
                </div>
                <button class="recurring-delete delete-recurring" data-id="${item.id}" title="Удалить">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `).join('');

        recurringListContainer.querySelectorAll('.delete-recurring').forEach(btn => {
            btn.addEventListener('click', async () => {
                const ok = await window.appConfirm({
                    title: 'Удалить шаблон?',
                    message: 'Будущие транзакции по этому шаблону создаваться не будут.',
                    confirmText: 'Удалить',
                    danger: true,
                });
                if (!ok) return;
                fetch(`${urls.recurringDelete}?id=${btn.dataset.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }
                }).then(() => loadRecurringList());
            });
        });
    }

    document.getElementById('viewRecurringBtn')?.addEventListener('click', loadRecurringList);

    document.getElementById('createRecurringInModalBtn')?.addEventListener('click', () => {
        if (!recurringForm) return;
        recurringForm.reset();
        const idEl = document.getElementById('recurring-id');
        if (idEl) idEl.value = '';
        if (recurringFormContainer) recurringFormContainer.style.display = 'block';
        if (recurringListContainer) recurringListContainer.style.display = 'none';
    });

    document.getElementById('cancelRecurringBtn')?.addEventListener('click', () => {
        if (recurringFormContainer) recurringFormContainer.style.display = 'none';
        if (recurringListContainer) recurringListContainer.style.display = 'block';
    });

    recurringForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(recurringForm);

        fetch(urls.recurringCreate, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (recurringFormContainer) recurringFormContainer.style.display = 'none';
                    if (recurringListContainer) recurringListContainer.style.display = 'block';
                    loadRecurringList();
                    window.appToast('Шаблон создан', 'success');
                } else {
                    window.appToast('Ошибка: ' + data.message, 'error');
                }
            });
    });
});
