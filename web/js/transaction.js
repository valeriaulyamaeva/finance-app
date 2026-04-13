document.addEventListener('DOMContentLoaded', () => {
    const { urls } = transactionConfig;
    const modalEl = document.getElementById('transactionModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('transactionForm');
    const formErrors = document.getElementById('formErrors');

    const toggleGoalField = (categoryId) => {
        if (!categoryId) { document.getElementById('goalSelector').style.display = 'none'; return; }
        fetch(`/category/type?id=${categoryId}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('goalSelector').style.display = (data.type === 'goal') ? 'block' : 'none';
            });
    };

    document.getElementById('createTransactionBtn').addEventListener('click', () => {
        form.reset();
        document.getElementById('transaction-id').value = '';
        document.getElementById('modalTransactionTitle').textContent = 'Новая транзакция';
        formErrors.classList.add('d-none');
        modal.show();
    });

    document.querySelector('.transactions-container').addEventListener('click', (e) => {
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
                    modal.show();
                }
            });
    });

    document.querySelector('.saveTransaction').addEventListener('click', () => {
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
                    formErrors.textContent = data.message;
                    formErrors.classList.remove('d-none');
                }
            });
    });

    document.querySelector('.transactions-container').addEventListener('click', (e) => {
        const deleteBtn = e.target.closest('.js-delete');
        if (!deleteBtn) return;

        if (!confirm('Вы уверены, что хотите удалить эту транзакцию?')) {
            return;
        }

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
                    alert('Ошибка при удалении: ' + data.message);
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
});

const recurringModal = new bootstrap.Modal(document.getElementById('recurringModal'));
const recurringListContainer = document.getElementById('recurringItemsList');
const recurringFormContainer = document.getElementById('recurringFormContainer');
const recurringForm = document.getElementById('recurringForm');

document.getElementById('viewRecurringBtn').addEventListener('click', loadRecurringList);

function loadRecurringList() {
    recurringFormContainer.style.display = 'none';
    recurringListContainer.innerHTML = '<p class="text-center">Загрузка...</p>';

    fetch(urls.recurringList)
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                renderRecurringList(res.data);
            }
        });
}

function renderRecurringList(data) {
    if (data.length === 0) {
        recurringListContainer.innerHTML = '<p class="text-muted text-center">У вас пока нет регулярных платежей</p>';
        return;
    }

    recurringListContainer.innerHTML = data.map(item => `
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div>
                    <span class="fw-bold">${item.amount} ${item.currency}</span> 
                    <span class="badge bg-info ms-2">${item.frequency_label}</span>
                    <div class="small text-muted">${item.category} • След: ${item.next_date}</div>
                </div>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-danger delete-recurring" data-id="${item.id}">🗑️</button>
                </div>
            </div>
        `).join('');

    // Навешиваем удаление
    document.querySelectorAll('.delete-recurring').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (confirm('Удалить этот шаблон?')) {
                fetch(`${urls.recurringDelete}?id=${btn.dataset.id}`, { method: 'POST', headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }})
                    .then(() => loadRecurringList());
            }
        });
    });
}

document.getElementById('createRecurringInModalBtn').addEventListener('click', () => {
    recurringForm.reset();
    document.getElementById('recurring-id').value = '';
    recurringFormContainer.style.display = 'block';
    recurringListContainer.style.display = 'none';
});

document.getElementById('cancelRecurringBtn').addEventListener('click', () => {
    recurringFormContainer.style.display = 'none';
    recurringListContainer.style.display = 'block';
});

recurringForm.addEventListener('submit', (e) => {
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
                recurringFormContainer.style.display = 'none';
                recurringListContainer.style.display = 'block';
                loadRecurringList();
            } else {
                alert('Ошибка: ' + data.message);
            }
        });
});