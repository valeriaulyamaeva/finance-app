document.addEventListener('DOMContentLoaded', () => {
    if (!window.investmentConfig) {
        console.error('investmentConfig not loaded');
        return;
    }
    const { urls } = window.investmentConfig;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const modalEl = document.getElementById('investmentModal');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    const form = document.getElementById('investmentForm');
    const formErrors = document.getElementById('formErrors');
    const titleEl = document.getElementById('investmentModalTitle');

    let currentId = null;

    function showError(msg) {
        if (formErrors) {
            formErrors.textContent = msg;
            formErrors.classList.remove('d-none');
        }
    }
    function hideError() {
        formErrors?.classList.add('d-none');
    }

    const typeSelect = document.getElementById('inv-type');
    const marketBox = document.getElementById('marketPriceBox');
    const priceResult = document.getElementById('marketPriceResult');
    const tickerInput = document.getElementById('inv-ticker');
    const qtyInput = document.getElementById('inv-quantity');
    const currentInput = document.getElementById('inv-current');
    const currencySelect = document.getElementById('inv-currency');
    const investedInput = document.getElementById('inv-invested');

    const LIVE_TYPES = ['crypto', 'stocks', 'fund', 'bonds'];

    function toggleMarketBox() {
        if (!marketBox || !typeSelect) return;
        marketBox.style.display = LIVE_TYPES.includes(typeSelect.value) ? 'block' : 'none';
    }
    typeSelect?.addEventListener('change', toggleMarketBox);

    document.getElementById('addInvestmentBtn')?.addEventListener('click', () => {
        currentId = null;
        form?.reset();
        if (priceResult) priceResult.textContent = '';
        if (titleEl) titleEl.textContent = 'Новый актив';
        toggleMarketBox();
        hideError();
        modal?.show();
    });

    // Fetch live price → auto-fill current value
    document.getElementById('fetchPriceBtn')?.addEventListener('click', () => {
        const ticker = tickerInput?.value.trim();
        const type = typeSelect?.value;
        const qty = parseFloat(qtyInput?.value) || 0;
        if (!ticker) {
            if (priceResult) {
                priceResult.className = 'market-price-result market-price-result--error';
                priceResult.textContent = 'Введите тикер';
            }
            return;
        }
        if (priceResult) {
            priceResult.className = 'market-price-result';
            priceResult.textContent = 'Загрузка...';
        }

        fetch(`${urls.price}?type=${encodeURIComponent(type)}&ticker=${encodeURIComponent(ticker)}`)
            .then(r => r.json())
            .then(data => {
                if (!priceResult) return;
                if (data.success) {
                    const price = data.price;
                    priceResult.className = 'market-price-result market-price-result--ok';
                    priceResult.innerHTML = `Цена: <b>${price} ${data.currency}</b>` +
                        (qty > 0 ? ` × ${qty} = <b>${(price * qty).toFixed(2)} ${data.currency}</b>` : '');
                    // Auto-fill current value + currency
                    if (currencySelect) currencySelect.value = data.currency;
                    if (qty > 0 && currentInput) {
                        currentInput.value = (price * qty).toFixed(2);
                    }
                } else {
                    priceResult.className = 'market-price-result market-price-result--error';
                    priceResult.textContent = data.message || 'Не удалось получить цену';
                }
            })
            .catch(() => {
                if (priceResult) {
                    priceResult.className = 'market-price-result market-price-result--error';
                    priceResult.textContent = 'Ошибка соединения';
                }
            });
    });

    // Refresh all prices
    document.getElementById('refreshPricesBtn')?.addEventListener('click', function () {
        const btn = this;
        btn.disabled = true;
        const icon = btn.querySelector('i');
        icon?.classList.add('fa-spin');

        fetch(urls.refresh, {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrf, 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (data.updated > 0) {
                        window.appToast(`Обновлено активов: ${data.updated}`, 'success');
                        setTimeout(() => location.reload(), 800);
                    } else {
                        window.appToast('Нет активов с тикером для обновления', 'info');
                        btn.disabled = false;
                        icon?.classList.remove('fa-spin');
                    }
                } else {
                    window.appToast('Ошибка обновления', 'error');
                    btn.disabled = false;
                    icon?.classList.remove('fa-spin');
                }
            })
            .catch(() => {
                window.appToast('Ошибка соединения', 'error');
                btn.disabled = false;
                icon?.classList.remove('fa-spin');
            });
    });

    document.querySelector('.cards-container')?.addEventListener('click', async (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;
        const id = btn.dataset.id;

        if (btn.classList.contains('editBtn')) {
            fetch(`${urls.view}?id=${id}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const inv = data.investment;
                        currentId = id;
                        document.getElementById('inv-name').value = inv.name;
                        document.getElementById('inv-type').value = inv.type;
                        document.getElementById('inv-ticker').value = inv.ticker || '';
                        document.getElementById('inv-quantity').value = inv.quantity || '';
                        document.getElementById('inv-invested').value = inv.invested_amount;
                        document.getElementById('inv-current').value = inv.current_value;
                        document.getElementById('inv-currency').value = inv.currency;
                        document.getElementById('inv-date').value = inv.purchase_date || '';
                        document.getElementById('inv-note').value = inv.note || '';
                        if (priceResult) priceResult.textContent = '';
                        if (titleEl) titleEl.textContent = 'Редактирование актива';
                        toggleMarketBox();
                        hideError();
                        modal?.show();
                    }
                });
        }

        if (btn.classList.contains('deleteBtn')) {
            const ok = await window.appConfirm({
                title: 'Удалить актив?',
                message: 'Это действие нельзя отменить.',
                confirmText: 'Удалить',
                danger: true,
            });
            if (!ok) return;
            fetch(`${urls.delete}?id=${id}`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrf, 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) location.reload();
                    else window.appToast(data.message || 'Ошибка', 'error');
                });
        }
    });

    function save() {
        if (!form) return;
        hideError();
        const formData = new FormData(form);
        const url = currentId ? `${urls.update}?id=${currentId}` : urls.create;

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-Token': csrf, 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showError(data.message || 'Ошибка валидации');
                }
            })
            .catch(() => showError('Ошибка сети'));
    }

    document.getElementById('investmentSaveBtn')?.addEventListener('click', save);
    form?.addEventListener('submit', (e) => { e.preventDefault(); save(); });
});
