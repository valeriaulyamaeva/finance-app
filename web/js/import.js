document.addEventListener('DOMContentLoaded', function () {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('previewContainer');
    const previewBody = document.getElementById('previewBody');
    const importBtn = document.getElementById('importBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const selectAll = document.getElementById('selectAll');
    const loading = document.getElementById('loadingOverlay');
    const statsTotal = document.getElementById('statsTotal');
    const statsNew = document.getElementById('statsNew');
    const statsDuplicates = document.getElementById('statsDuplicates');

    let parsedData = [];
    let currentFilename = '';
    let currentFileType = '';

    // Upload zone events
    uploadZone.addEventListener('click', () => fileInput.click());
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.classList.add('drag-over');
    });
    uploadZone.addEventListener('dragleave', () => {
        uploadZone.classList.remove('drag-over');
    });
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.classList.remove('drag-over');
        if (e.dataTransfer.files.length) {
            handleFile(e.dataTransfer.files[0]);
        }
    });
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) {
            handleFile(fileInput.files[0]);
        }
    });

    function handleFile(file) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (!['csv', 'pdf'].includes(ext)) {
            showToast('Поддерживаются только CSV и PDF файлы', 'error');
            return;
        }

        currentFilename = file.name;
        currentFileType = ext;

        const formData = new FormData();
        formData.append('file', file);

        loading.classList.add('visible');

        fetch(importConfig.urls.parse, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData,
        })
            .then(r => r.json())
            .then(data => {
                loading.classList.remove('visible');
                if (!data.success) {
                    showToast(data.message || 'Ошибка обработки файла', 'error');
                    return;
                }
                parsedData = data.transactions;
                renderPreview();
            })
            .catch(err => {
                loading.classList.remove('visible');
                showToast('Ошибка соединения с сервером', 'error');
            });
    }

    function renderPreview() {
        previewBody.innerHTML = '';

        let total = parsedData.length;
        let duplicates = parsedData.filter(t => t.is_duplicate).length;

        statsTotal.textContent = total;
        statsNew.textContent = total - duplicates;
        statsDuplicates.textContent = duplicates;

        parsedData.forEach((tx, idx) => {
            const tr = document.createElement('tr');
            if (tx.is_duplicate) tr.classList.add('duplicate');
            if (!tx.category_id && !tx.is_duplicate) tr.classList.add('no-category');

            const amountClass = tx.type === 'income' ? 'amount-income' : 'amount-expense';
            const amountSign = tx.type === 'income' ? '+' : '-';

            tr.innerHTML = `
                <td>
                    <input type="checkbox" class="tx-check" data-idx="${idx}"
                        ${tx.is_duplicate ? 'disabled' : 'checked'}>
                </td>
                <td>${tx.date}</td>
                <td class="${amountClass}">${amountSign}${tx.amount.toFixed(2)} ${tx.currency}</td>
                <td>${escapeHtml(tx.description)}</td>
                <td>${escapeHtml(tx.operation_type)}</td>
                <td>${tx.mcc || '-'}</td>
                <td>
                    <select class="category-select" data-idx="${idx}" ${tx.is_duplicate ? 'disabled' : ''}>
                        <option value="">-- Без категории --</option>
                        ${importConfig.categories.map(c =>
                `<option value="${c.id}" ${c.id == tx.category_id ? 'selected' : ''}>${escapeHtml(c.name)} (${c.type})</option>`
            ).join('')}
                    </select>
                </td>
                <td>
                    ${tx.is_duplicate ? '<span class="duplicate-badge">Дубликат</span>' : ''}
                </td>
            `;
            previewBody.appendChild(tr);
        });

        previewContainer.classList.add('visible');
        uploadZone.style.display = 'none';
        updateSelectAll();
    }

    // Select all checkbox
    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.tx-check:not(:disabled)').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    function updateSelectAll() {
        const checks = document.querySelectorAll('.tx-check:not(:disabled)');
        const checked = document.querySelectorAll('.tx-check:not(:disabled):checked');
        selectAll.checked = checks.length > 0 && checks.length === checked.length;
    }

    previewBody.addEventListener('change', (e) => {
        if (e.target.classList.contains('tx-check')) {
            updateSelectAll();
        }
    });

    // Import button
    importBtn.addEventListener('click', function () {
        const selected = [];
        document.querySelectorAll('.tx-check:checked').forEach(cb => {
            const idx = parseInt(cb.dataset.idx);
            const tx = {...parsedData[idx]};

            // Get user-selected category
            const select = document.querySelector(`.category-select[data-idx="${idx}"]`);
            tx.category_id = select.value ? parseInt(select.value) : null;

            selected.push(tx);
        });

        const noCategory = selected.filter(t => !t.category_id);
        if (noCategory.length > 0) {
            showToast(`У ${noCategory.length} транзакций не выбрана категория — они будут пропущены`, 'error');
            // Remove transactions without category
            const filtered = selected.filter(t => t.category_id);
            if (!filtered.length) {
                showToast('Нет транзакций с выбранной категорией', 'error');
                return;
            }
            selected.length = 0;
            selected.push(...filtered);
        }

        if (!selected.length) {
            showToast('Выберите хотя бы одну транзакцию', 'error');
            return;
        }

        loading.classList.add('visible');

        fetch(importConfig.urls.confirm, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                transactions: selected,
                filename: currentFilename,
                file_type: currentFileType,
            }),
        })
            .then(r => r.json())
            .then(data => {
                loading.classList.remove('visible');
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => window.location.href = '/transaction', 1500);
                } else {
                    showToast(data.message || 'Ошибка импорта', 'error');
                }
            })
            .catch(() => {
                loading.classList.remove('visible');
                showToast('Ошибка соединения с сервером', 'error');
            });
    });

    // Cancel button
    cancelBtn.addEventListener('click', function () {
        parsedData = [];
        previewContainer.classList.remove('visible');
        uploadZone.style.display = '';
        fileInput.value = '';
    });

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast-message ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }
});
