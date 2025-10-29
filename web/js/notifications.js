document.addEventListener('DOMContentLoaded', () => {
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationCountEl = document.getElementById('notificationCount');
    const notificationListEl = document.getElementById('notificationList');
    const modal = document.getElementById('notificationModal');
    const closeBtn = modal.querySelector('.close');
    const markAllReadBtn = modal.querySelector('.mark-all-read');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const csrfParam = document.querySelector('meta[name="csrf-param"]')?.getAttribute('content');

    let intervalId;

    if (!notificationBtn || !notificationCountEl || !notificationListEl || !modal || !closeBtn || !markAllReadBtn) return;

    function fetchNotifications() {
        fetch('/notification/index')
            .then(res => res.ok ? res.json() : Promise.reject(`HTTP ${res.status}`))
            .then(data => {
                notificationCountEl.textContent = data.unread_count > 0 ? data.unread_count : '';
                notificationListEl.innerHTML = data.notifications.length === 0
                    ? '<li class="empty">Нет уведомлений</li>'
                    : data.notifications.map(n => `
                        <li data-id="${n.id}" class="${n.read_status ? '' : 'unread'}">
                            <span>${n.message}</span>
                            <div>
                                ${n.read_status ? '' : '<button class="mark-read">✓</button>'}
                            </div>
                        </li>
                    `).join('');
            })
            .catch(err => console.error('Ошибка загрузки уведомлений:', err));
    }

    function markAsRead(id, li) {
        fetch(`/notification/mark-read?id=${id}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', [csrfParam]: csrfToken }
        })
            .then(res => res.ok ? res.json() : Promise.reject(`HTTP ${res.status}`))
            .then(data => {
                if (data.success) {
                    li.style.transition = 'opacity 0.3s ease, height 0.3s ease, margin 0.3s ease, padding 0.3s ease';
                    li.style.opacity = '0';
                    li.style.height = '0';
                    li.style.margin = '0';
                    li.style.padding = '0';
                    setTimeout(() => li.remove(), 300);
                    // Обновляем счетчик
                    const count = parseInt(notificationCountEl.textContent) || 0;
                    notificationCountEl.textContent = count > 1 ? count - 1 : '';
                } else {
                    console.error('Failed to mark as read:', data.error);
                }
            })
            .catch(err => console.error('Ошибка отметки уведомления как прочитанное:', err));
    }

    notificationListEl.addEventListener('click', e => {
        const li = e.target.closest('li');
        if (!li || li.classList.contains('empty')) return;
        const id = li.dataset.id;
        if (e.target.classList.contains('mark-read')) markAsRead(id, li);
    });

    markAllReadBtn.textContent = '✓'; // делаем галочку
    markAllReadBtn.style.background = 'transparent';
    markAllReadBtn.style.border = 'none';
    markAllReadBtn.style.cursor = 'pointer';
    markAllReadBtn.style.fontSize = '16px';
    markAllReadBtn.style.padding = '0';

    markAllReadBtn.addEventListener('click', () => {
        fetch('/notification/mark-all-read', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', [csrfParam]: csrfToken }
        })
            .then(res => res.ok ? res.json() : Promise.reject(`HTTP ${res.status}`))
            .then(data => {
                if (data.success) {
                    const unreadItems = notificationListEl.querySelectorAll('li.unread');
                    unreadItems.forEach(item => {
                        item.style.transition = 'opacity 0.3s ease, height 0.3s ease, margin 0.3s ease, padding 0.3s ease';
                        item.style.opacity = '0';
                        item.style.height = '0';
                        item.style.margin = '0';
                        item.style.padding = '0';
                        setTimeout(() => item.remove(), 300);
                    });
                    notificationCountEl.textContent = '';
                }
            })
            .catch(err => console.error('Ошибка отметки всех уведомлений:', err));
    });

    function openModal() {
        modal.style.display = 'flex';
        fetchNotifications();
        intervalId = setInterval(fetchNotifications, 30000);
    }

    function closeModal() {
        modal.style.display = 'none';
        clearInterval(intervalId);
    }

    notificationBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    window.addEventListener('click', e => { if (e.target === modal) closeModal(); });
});
