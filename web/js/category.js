document.addEventListener('DOMContentLoaded', () => {
    const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
    let currentAction = 'create';

    const createUrl = categoryUrls.create;
    const updateUrl = categoryUrls.update;
    const deleteUrl = categoryUrls.delete;

    function bindCardEvents(card) {
        const id = card.dataset.id;

        card.querySelector('.editBtn').addEventListener('click', () => {
            currentAction = 'update';
            document.getElementById('categoryId').value = id;
            document.getElementById('categoryName').value = card.querySelector('h3').textContent;
            document.getElementById('categoryType').value = card.querySelector('p').textContent.toLowerCase();
            document.getElementById('formErrors').textContent = '';
            modal.show();
        });

        card.querySelector('.deleteBtn').addEventListener('click', () => {
            if (!confirm('Вы уверены, что хотите удалить категорию?')) return;

            fetch(deleteUrl + '?id=' + id, {
                method: 'POST',
                headers: {
                    'X-Requested-With':'XMLHttpRequest',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) card.remove();
                    else alert(data.message || 'Ошибка при удалении категории');
                })
                .catch(err => alert(err.message));
        });
    }

    document.querySelectorAll('.card').forEach(bindCardEvents);

    document.getElementById('addCategoryBtn').addEventListener('click', () => {
        currentAction = 'create';
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryId').value = '';
        document.getElementById('formErrors').textContent = '';
    });

    document.getElementById('categoryForm').addEventListener('submit', e => {
        e.preventDefault();

        const formData = new FormData();
        formData.append('Category[name]', document.getElementById('categoryName').value.trim());
        formData.append('Category[type]', document.getElementById('categoryType').value);
        formData.append('Category[id]', document.getElementById('categoryId').value);

        const url = currentAction === 'create' ? createUrl : updateUrl + '?id=' + formData.get('Category[id]');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {'X-Requested-With':'XMLHttpRequest', 'X-CSRF-Token': csrfToken}
        })
            .then(res => res.json())
            .then(data => {
                if (data.id) {
                    let card;
                    if (currentAction === 'create') {
                        card = document.createElement('div');
                        card.classList.add('card');
                        card.dataset.id = data.id;
                        document.querySelector('.cards-container').prepend(card);
                    } else {
                        card = document.querySelector('.card[data-id="' + data.id + '"]');
                    }

                    card.innerHTML =
                        '<div>' +
                        '<h3>' + data.name + '</h3>' +
                        '<p>' + data.type.charAt(0).toUpperCase() + data.type.slice(1) + '</p>' +
                        '</div>' +
                        '<div class="actions">' +
                        '<button class="editBtn" title="Редактировать">✏️</button>' +
                        '<button class="deleteBtn" title="Удалить">🗑️</button>' +
                        '</div>';

                    bindCardEvents(card);
                    modal.hide();
                } else if (data) {
                    document.getElementById('formErrors').textContent = Object.values(data).flat().join('; ');
                }
            })
            .catch(err => { document.getElementById('formErrors').textContent = err.message; });
    });
});
