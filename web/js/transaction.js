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

    // Проверка наличия всех элементов
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

    // Функция для проверки типа категории и показа/скрытия селектора целей
    function toggleGoalSelector() {
        const selectedId = categorySelect.value;
        if (!selectedId) {
            goalSelector.style.display = 'none';
            goalSelect.value = '';
            goalSelect.required = false;
            return;
        }

        // AJAX-запрос для проверки типа категории
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

    // Привязываем событие изменения категории
    categorySelect.addEventListener('change', toggleGoalSelector);

    // Обработка повторяющихся транзакций
    recurringFrequency.addEventListener('change', () => {
        if (recurringFrequency.value) {
            nextDateWrapper.style.display = 'block';
            nextDateInput.required = true;
        } else {
            nextDateWrapper.style.display = 'none';
            nextDateInput.required = false;
            nextDateInput.value = '';
        }
    });

    // Создание транзакции
    document.getElementById('createTransactionBtn').addEventListener('click', () => {
        currentAction = 'create';
        currentId = null;
        form.reset();
        currencyInput.value = userCurrency;
        formErrors.textContent = '';
        formErrors.style.display = 'none';
        modalEl.querySelector('.modal-title').textContent = 'Создать транзакцию';
        toggleGoalSelector(); // Проверяем начальное состояние
        modal.show();
    });

    // Создание повторяющейся транзакции
    document.getElementById('createRecurringBtn').addEventListener('click', () => {
        currentAction = 'createRecurring';
        currentId = null;
        form.reset();
        currencyInput.value = userCurrency;
        formErrors.textContent = '';
        formErrors.style.display = 'none';
        modalEl.querySelector('.modal-title').textContent = 'Создать повторяющуюся транзакцию';
        toggleGoalSelector(); // Проверяем начальное состояние
        modal.show();
    });

    // Редактирование и удаление транзакций
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
                        toggleGoalSelector(); // Проверяем категорию при редактировании
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

    // Сохранение транзакции с проверкой goal_id
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

    // Функция для отправки формы
    function saveTransaction() {
        currencyInput.value = form.querySelector('#currencySelector')?.value || userCurrency;
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
});