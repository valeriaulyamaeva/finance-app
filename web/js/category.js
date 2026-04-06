document.addEventListener('DOMContentLoaded', () => {
    const modalElement = document.getElementById('categoryModal');
    const modal = new bootstrap.Modal(modalElement);
    const form = document.getElementById('categoryForm');
    const formErrors = document.getElementById('formErrors');

    let currentAction = 'create';

    const { create: createUrl, update: updateUrl, delete: deleteUrl } = categoryUrls;

    function updateCardInDom(data, isNew = false) {
        let card;
        if (isNew) {
            card = document.createElement('div');
            card.className = 'card border-0 shadow-sm mb-3';
            document.querySelector('.cards-container').prepend(card);
        } else {
            card = document.querySelector(`.card[data-id="${data.id}"]`);
        }

        card.dataset.id = data.id;
        card.dataset.type = data.type;

        const typeLabels = { income: 'Доход', expense: 'Расход', goal: 'Цель' };
        const label = typeLabels[data.type] || data.type;

        card.innerHTML = `
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="h5 mb-1">${escapeHtml(data.name)}</h3>
                    <span class="badge rounded-pill bg-light text-dark border">${label}</span>
                </div>
                <div class="card-actions">
                    <button class="btn btn-link text-primary editBtn" title="Редактировать"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-link text-danger deleteBtn" title="Удалить"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `;
        bindCardEvents(card);
    }

    function bindCardEvents(card) {
        // Редактирование
        card.querySelector('.editBtn').addEventListener('click', () => {
            currentAction = 'update';
            const id = card.dataset.id;
            const name = card.querySelector('h3').textContent;
            const type = card.dataset.type;

            document.getElementById('categoryId').value = id;
            document.getElementById('categoryName').value = name;
            document.getElementById('categoryType').value = type;

            document.getElementById('modalCategoryTitle').textContent = 'Редактировать категорию';
            formErrors.classList.add('d-none');
            modal.show();
        });

        // Удаление
        card.querySelector('.deleteBtn').addEventListener('click', async () => {
            if (!confirm('Вы уверены?')) return;

            try {
                const response = await fetch(`${deleteUrl}?id=${card.dataset.id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                if (data.success) card.remove();
                else alert(data.error || 'Ошибка удаления');
            } catch (e) { alert('Ошибка сети'); }
        });
    }

    document.querySelectorAll('.card').forEach(bindCardEvents);

    document.getElementById('addCategoryBtn').addEventListener('click', () => {
        currentAction = 'create';
        form.reset();
        document.getElementById('categoryId').value = '';
        document.getElementById('modalCategoryTitle').textContent = 'Новая категория';
        formErrors.classList.add('d-none');
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const id = document.getElementById('categoryId').value;
        const formData = new FormData(form);

        const url = currentAction === 'create' ? createUrl : `${updateUrl}?id=${id}`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (response.ok && data.id) {
                updateCardInDom(data, currentAction === 'create');
                modal.hide();
                form.reset();
            } else {
                formErrors.textContent = data.error || 'Ошибка валидации';
                formErrors.classList.remove('d-none');
            }
        } catch (err) {
            formErrors.textContent = 'Произошла ошибка при отправке';
            formErrors.classList.remove('d-none');
        }
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});