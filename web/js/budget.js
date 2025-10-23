document.addEventListener('DOMContentLoaded', () => {
    const { createUrl, updateUrl, deleteUrl, viewUrl, userCurrency } = budgetConfig;

    const modalEl = document.getElementById('budgetModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('budgetForm');
    const formErrors = document.getElementById('formErrors');
    const budgetCurrency = document.getElementById('budgetCurrency');
    let currentAction = 'create';
    let currentId = null;

    document.getElementById('addBudgetBtn').addEventListener('click', () => {
        currentAction = 'create';
        currentId = null;
        form.reset();
        budgetCurrency.value = userCurrency;
        formErrors.textContent = '';
        formErrors.style.display = 'none';
        modal.show();
    });

    document.querySelector('.cards-container').addEventListener('click', (e) => {
        const target = e.target;

        if (target.classList.contains('editBtn')) {
            const id = target.dataset.id;
            fetch(`${viewUrl}?id=${id}`)
                .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }))
                .then(data => {
                    if (data.success && data.budget) {
                        const b = data.budget;
                        form.querySelector('[name="Budget[name]"]').value = b.name;
                        form.querySelector('[name="Budget[amount]"]').value = b.display_amount;
                        form.querySelector('[name="Budget[period]"]').value = b.period;
                        form.querySelector('[name="Budget[category_id]"]').value = b.category_id;
                        form.querySelector('[name="Budget[start_date]"]').value = b.start_date;
                        form.querySelector('[name="Budget[end_date]"]').value = b.end_date;
                        budgetCurrency.value = b.display_currency || 'BYN';
                        currentAction = 'update';
                        currentId = id;
                        formErrors.textContent = '';
                        formErrors.style.display = 'none';
                        modal.show();
                    } else {
                        formErrors.textContent = data.message || 'Ошибка при загрузке бюджета';
                        formErrors.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Edit error:', err);
                    formErrors.textContent = `Ошибка: ${err.message}`;
                    formErrors.style.display = 'block';
                });
        }

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
                .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }))
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        formErrors.textContent = data.message || 'Ошибка при удалении';
                        formErrors.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Delete error:', err);
                    formErrors.textContent = `Ошибка: ${err.message}`;
                    formErrors.style.display = 'block';
                });
        }
    });

    document.querySelector('.saveBudget').addEventListener('click', () => {
        const formData = new FormData(form);
        formData.set('Budget[currency]', budgetCurrency.value);
        const url = currentAction === 'create' ? createUrl : `${updateUrl}?id=${currentId}`;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }
        })
            .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }))
            .then(data => {
                if (data.success && data.budget) {
                    location.reload();
                } else {
                    formErrors.textContent = data.message || 'Ошибка при сохранении';
                    formErrors.style.display = 'block';
                }
            })
            .catch(err => {
                console.error('Save error:', err);
                formErrors.textContent = `Ошибка: ${err.message}`;
                formErrors.style.display = 'block';
            });
    });
});
