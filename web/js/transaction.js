document.addEventListener('DOMContentLoaded', () => {
    const { urls, currencySymbols, userCurrency } = transactionConfig;

    const modalEl = document.getElementById('transactionModal');
    if (!modalEl) {
        console.error('Модальное окно транзакции не найдено');
        return;
    }
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('transactionForm');
    const formErrors = document.getElementById('formErrors');
    const categorySelect = form.querySelector('[name="Transaction[category_id]"]');
    const goalSelector = document.getElementById('goalSelector');
    const goalSelect = form.querySelector('[name="Transaction[goal_id]"]');
    const recurringFrequency = document.getElementById('recurringFrequency');
    const nextDateWrapper = document.getElementById('nextDateWrapper');
    const nextDateInput = document.getElementById('recurringNextDate');

    if (!form || !formErrors || !categorySelect || !goalSelector || !goalSelect || !recurringFrequency || !nextDateWrapper || !nextDateInput) {
        console.error('Отсутствуют элементы формы:', { form, formErrors, categorySelect, goalSelector, goalSelect, recurringFrequency, nextDateWrapper, nextDateInput });
        return;
    }

    let currentAction = 'create';
    let currentId = null;

    const currencyInput = document.createElement('input');
    currencyInput.type = 'hidden';
    currencyInput.name = 'Transaction[currency]';
    form.appendChild(currencyInput);

    function toggleGoalSelector() {
        const selectedId = categorySelect.value;
        if (!selectedId) {
            goalSelector.style.display = 'none';
            goalSelect.value = '';
            goalSelect.required = false;
            return;
        }

        fetch('/category/type?id=' + selectedId, {
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
            .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }))
            .then(data => {
                console.log('Ответ проверки категории:', data);
                if (data.type === 'goal') {
                    goalSelector.style.display = 'block';
                    goalSelect.required = true;
                } else {
                    goalSelector.style.display = 'none';
                    goalSelect.value = '';
                    goalSelect.required = false;
                }
            })
            .catch(err => {
                console.error('Ошибка проверки типа категории:', err);
                goalSelector.style.display = 'none';
                goalSelect.value = '';
                goalSelect.required = false;
            });
    }

    categorySelect.addEventListener('change', toggleGoalSelector);

    recurringFrequency.addEventListener('change', () => {
        if (recurringFrequency.value) {
            nextDateWrapper.style.display = 'block';
            nextDateInput.required = true;
            if (!nextDateInput.value) {
                const today = new Date().toISOString().slice(0, 10);
                nextDateInput.value = today;
            }
        } else {
            nextDateWrapper.style.display = 'none';
            nextDateInput.required = false;
            nextDateInput.value = '';
        }
    });

    document.getElementById('createTransactionBtn').addEventListener('click', () => {
        currentAction = 'create';
        currentId = null;
        form.reset();
        currencyInput.value = userCurrency;
        formErrors.textContent = '';
        formErrors.style.display = 'none';
        modalEl.querySelector('.modal-title').textContent = 'Создать транзакцию';
        toggleGoalSelector();

        recurringFrequency.value = '';
        nextDateWrapper.style.display = 'none';
        recurringFrequency.closest('.mb-3').style.display = 'none';

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
                        categorySelect.value = t.category_id;
                        goalSelect.value = t.goal_id || '';
                        form.querySelector('[name="Transaction[description]"]').value = t.description || '';
                        currencyInput.value = t.display_currency;
                        currentAction = 'update';
                        currentId = id;
                        formErrors.textContent = '';
                        formErrors.style.display = 'none';
                        modalEl.querySelector('.modal-title').textContent = 'Обновить транзакцию';
                        toggleGoalSelector();
                        modal.show();
                    } else {
                        formErrors.textContent = data.message || 'Ошибка при загрузке транзакции';
                        formErrors.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Ошибка редактирования:', err);
                    formErrors.textContent = 'Ошибка: ' + err.message;
                    formErrors.style.display = 'block';
                });
        }

        if (target.classList.contains('js-delete')) {
            const id = target.dataset.id;
            if (!confirm('Удалить эту транзакцию?')) return;

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
                        target.closest('.transaction-card').remove();
                    } else {
                        formErrors.textContent = data.message || 'Ошибка при удалении';
                        formErrors.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error('Ошибка удаления:', err);
                    formErrors.textContent = 'Ошибка: ' + err.message;
                    formErrors.style.display = 'block';
                });
        }
    });

    document.querySelector('.saveTransaction').addEventListener('click', () => {
        const selectedId = categorySelect.value;
        if (selectedId) {
            fetch('/category/type?id=' + selectedId, {
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
                .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }))
                .then(data => {
                    if (data.type === 'goal' && !goalSelect.value) {
                        formErrors.textContent = 'Выберите цель для категории типа "goal".';
                        formErrors.style.display = 'block';
                        return;
                    }
                    saveTransaction();
                })
                .catch(err => {
                    console.error('Ошибка проверки типа категории:', err);
                    formErrors.textContent = 'Ошибка проверки категории';
                    formErrors.style.display = 'block';
                });
        } else {
            saveTransaction();
        }
    });

    function saveTransaction() {
        currencyInput.value = form.querySelector('#currencySelector')?.value || userCurrency;

        if (!recurringFrequency.value) {
            nextDateInput.removeAttribute('name');
        } else {
            nextDateInput.setAttribute('name', 'RecurringTransaction[next_date]');
        }

        const formData = new FormData(form);
        const url = currentAction === 'create' ? urls.create :
            currentAction === 'createRecurring' ? urls.createRecurring :
                `${urls.update}?id=${currentId}`;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }
        })
            .then(res => res.ok ? res.json() : res.text().then(t => { throw new Error(t); }))
            .then(data => {
                if (data.success || data.id) {
                    modal.hide();
                    location.reload();
                } else {
                    formErrors.textContent = data.message || Object.values(data.errors || {}).flat().join('; ') || 'Ошибка при сохранении';
                    formErrors.style.display = 'block';
                }
            })
            .catch(err => {
                console.error('Ошибка сохранения:', err);
                formErrors.textContent = 'Ошибка: ' + err.message;
                formErrors.style.display = 'block';
            });
    }

    const recurringModalEl = document.getElementById('recurringModal');
    if (!recurringModalEl) {
        console.error('Модальное окно recurringModal не найдено');
        return;
    }
    const recurringModal = new bootstrap.Modal(recurringModalEl);
    const recurringList = document.getElementById('recurringList');
    const recurringEmpty = document.getElementById('recurringEmpty');

    function loadRecurring() {
        fetch(urls.recurringList)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    recurringList.innerHTML = data.data.map(item => `
                    <div class="transaction-card" data-id="${item.id}">
                        <div class="transaction-info">
                            <p><strong>Сумма:</strong> ${item.amount} ${currencySymbols[item.currency] || item.currency}</p>
                            <p><strong>Частота:</strong> ${item.frequency}</p>
                            <p><strong>Следующая дата:</strong> ${item.next_date}</p>
                            <p><strong>Категория:</strong> ${item.category}</p>
                            <p><strong>Описание:</strong> ${item.description}</p>
                            <p><strong>Статус:</strong> <span style="color: ${item.active ? 'green' : 'red'}">
                                ${item.active ? 'Активно' : 'Неактивно'}
                            </span></p>
                        </div>
                        <div class="transaction-actions">
                            <button class="deleteBtn js-delete-recurring" data-id="${item.id}">Удалить</button>
                        </div>
                    </div>
                `).join('');
                    recurringEmpty.style.display = 'none';
                } else {
                    recurringList.innerHTML = '';
                    recurringEmpty.style.display = 'block';
                }
            })
            .catch(err => {
                console.error('Ошибка загрузки регулярных платежей:', err);
                recurringEmpty.textContent = 'Ошибка загрузки';
                recurringEmpty.style.display = 'block';
            });
    }

    document.getElementById('viewRecurringBtn')?.addEventListener('click', () => {
        loadRecurring();
        recurringModal.show();
    });

    document.getElementById('createRecurringInModalBtn')?.addEventListener('click', () => {
        recurringModal.hide();
        currentAction = 'createRecurring';
        currentId = null;
        form.reset();
        currencyInput.value = userCurrency;
        formErrors.textContent = '';
        formErrors.style.display = 'none';
        modalEl.querySelector('.modal-title').textContent = 'Создать повторяющуюся транзакцию';
        toggleGoalSelector();
        recurringFrequency.closest('.mb-3').style.display = 'block';

        modal.show();
    });

    recurringList?.addEventListener('click', e => {
        if (e.target.classList.contains('js-delete-recurring')) {
            if (!confirm('Удалить регулярный платеж?')) return;
            const id = e.target.dataset.id;
            fetch(`${urls.recurringDelete}?id=${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ _csrf: document.querySelector('meta[name="csrf-token"]').content })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loadRecurring();
                    } else {
                        alert('Ошибка удаления');
                    }
                });
        }
    });
});