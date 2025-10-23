document.addEventListener('DOMContentLoaded', () => {
    const { urls, currencySymbols, userCurrency } = transactionConfig;

    const modalEl = document.getElementById('transactionModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('transactionForm');
    const formErrors = document.getElementById('formErrors');
    let currentAction = 'create';
    let currentId = null;

    const currencyInput = document.createElement('input');
    currencyInput.type = 'hidden';
    currencyInput.name = 'Transaction[currency]';
    form.appendChild(currencyInput);

    document.getElementById('createTransactionBtn').addEventListener('click', () => {
        currentAction = 'create';
        currentId = null;
        form.reset();
        currencyInput.value = userCurrency;
        formErrors.textContent = '';
        formErrors.style.display = 'none';
        modalEl.querySelector('.modal-title').textContent = 'Создать транзакцию';
        modal.show();
    });

    document.getElementById('createRecurringBtn').addEventListener('click', () => {
        currentAction = 'createRecurring';
        currentId = null;
        form.reset();
        currencyInput.value = userCurrency;
        formErrors.textContent = '';
        formErrors.style.display = 'none';
        modalEl.querySelector('.modal-title').textContent = 'Создать повторяющуюся транзакцию';
        modal.show();
    });

    document.querySelector('.transactions-container').addEventListener('click', e => {
        const target = e.target;

        if (target.classList.contains('js-update')) {
            const id = target.dataset.id;
            fetch(`${urls.view}?id=${id}`)
                .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }))
                .then(data => {
                    if (data.success && data.transaction) {
                        const t = data.transaction;
                        form.querySelector('[name="Transaction[amount]"]').value = t.display_amount;
                        form.querySelector('[name="Transaction[date]"]').value = t.date;
                        form.querySelector('[name="Transaction[category_id]"]').value = t.category_id;
                        form.querySelector('[name="Transaction[goal_id]"]').value = t.goal_id || '';
                        form.querySelector('[name="Transaction[description]"]').value = t.description || '';
                        currencyInput.value = t.display_currency;
                        currentAction = 'update';
                        currentId = id;
                        formErrors.textContent = '';
                        formErrors.style.display = 'none';
                        modalEl.querySelector('.modal-title').textContent = 'Обновить транзакцию';
                        modal.show();
                    } else {
                        formErrors.textContent = data.message || 'Ошибка при загрузке транзакции';
                        formErrors.style.display = 'block';
                    }
                })
                .catch(err => {
                    formErrors.textContent = 'Ошибка: ' + err.message;
                    formErrors.style.display = 'block';
                });
        }

        if (target.classList.contains('js-delete')) {
            const id = target.dataset.id;
            if (!confirm('Удалить эту транзакцию?')) return;

            fetch(`${urls.delete}?id=${id}`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) target.closest('.transaction-card').remove();
                    else alert('Ошибка при удалении: ' + (data.message || 'Неизвестная ошибка'));
                })
                .catch(err => alert('Ошибка сети: ' + err.message));
        }
    });

    document.querySelector('.saveTransaction').addEventListener('click', () => {
        currencyInput.value = form.querySelector('#currencySelector')?.value || userCurrency;
        const formData = new FormData(form);
        const url = currentAction === 'create' ? urls.create :
            currentAction === 'createRecurring' ? urls.createRecurring :
                `${urls.update}?id=${currentId}`;

        fetch(url, { method: 'POST', body: formData, headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content } })
            .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }))
            .then(data => {
                if (data.success || data.id) { modal.hide(); location.reload(); }
                else {
                    formErrors.textContent = data.message || Object.values(data.errors || {}).flat().join('; ') || 'Ошибка при сохранении';
                    formErrors.style.display = 'block';
                }
            })
            .catch(err => { formErrors.textContent = 'Ошибка: ' + err.message; formErrors.style.display = 'block'; });
    });
});
