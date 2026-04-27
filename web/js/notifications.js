document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('notificationBtn');
    const badge = document.getElementById('notificationCount');
    const dropdown = document.getElementById('notificationDropdown');
    const list = document.getElementById('notificationList');
    const emptyEl = document.getElementById('notifEmpty');
    const markAllBtn = document.getElementById('markAllReadBtn');

    if (!btn || !dropdown || !list) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function setBadge(count) {
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.classList.add('visible');
            } else {
                badge.textContent = '';
                badge.classList.remove('visible');
            }
        }
    }

    function timeAgo(dateStr) {
        if (!dateStr) return '';
        const diff = Math.floor((Date.now() - new Date(dateStr.replace(' ', 'T')).getTime()) / 60000);
        if (isNaN(diff) || diff < 0) return '';
        if (diff < 1) return 'сейчас';
        if (diff < 60) return diff + ' мин';
        if (diff < 1440) return Math.floor(diff / 60) + ' ч';
        return Math.floor(diff / 1440) + ' дн';
    }

    function icon(type) {
        if (type === 'budget_exceed') return '<i class="fas fa-exclamation-triangle" style="color:#ef4444"></i>';
        if (type === 'goal_reached') return '<i class="fas fa-trophy" style="color:#f59e0b"></i>';
        if (type === 'reminder') return '<i class="fas fa-clock" style="color:#3b82f6"></i>';
        return '<i class="fas fa-bell" style="color:#9ca3af"></i>';
    }

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML;
    }

    function render(items) {
        list.innerHTML = '';
        if (!items || items.length === 0) {
            if (emptyEl) emptyEl.classList.add('visible');
            return;
        }
        if (emptyEl) emptyEl.classList.remove('visible');
        items.forEach(n => {
            const li = document.createElement('li');
            li.dataset.id = n.id;
            if (!n.read_status) li.classList.add('unread');
            li.innerHTML = `
                <div class="notif-icon">${icon(n.type)}</div>
                <div class="notif-body">
                    <span class="notif-msg">${esc(n.message)}</span>
                    <span class="notif-time">${timeAgo(n.created_at)}</span>
                </div>
                ${!n.read_status ? '<button type="button" class="notif-read-btn"><i class="fas fa-check"></i></button>' : ''}
            `;
            list.appendChild(li);
        });
    }

    function load() {
        fetch('/notification/index')
            .then(r => {
                if (!r.ok) throw new Error(r.status);
                return r.json();
            })
            .then(data => {
                setBadge(data.unread_count || 0);
                render(data.notifications || []);
            })
            .catch(() => setBadge(0));
    }

    function loadBadge() {
        fetch('/notification/index')
            .then(r => {
                if (!r.ok) throw new Error(r.status);
                return r.json();
            })
            .then(data => setBadge(data.unread_count || 0))
            .catch(() => setBadge(0));
    }

    function postAction(url) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            }
        }).then(r => {
            if (!r.ok) throw new Error(r.status);
            return r.json();
        });
    }

    // Toggle
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('open');
        if (dropdown.classList.contains('open')) load();
    });

    document.addEventListener('click', (e) => {
        if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
            dropdown.classList.remove('open');
        }
    });

    // Mark single
    list.addEventListener('click', (e) => {
        const readBtn = e.target.closest('.notif-read-btn');
        if (!readBtn) return;
        const li = readBtn.closest('li');
        if (!li?.dataset.id) return;

        postAction('/notification/mark-read?id=' + li.dataset.id)
            .then(data => {
                if (data.success) {
                    li.classList.remove('unread');
                    readBtn.remove();
                    loadBadge();
                }
            })
            .catch(err => console.error('mark-read error:', err));
    });

    // Mark all
    if (markAllBtn) {
        markAllBtn.addEventListener('click', () => {
            postAction('/notification/mark-all-read')
                .then(data => {
                    if (data.success) {
                        list.querySelectorAll('.unread').forEach(li => {
                            li.classList.remove('unread');
                            li.querySelector('.notif-read-btn')?.remove();
                        });
                        setBadge(0);
                    }
                })
                .catch(err => console.error('mark-all error:', err));
        });
    }

    // Init
    loadBadge();
    setInterval(loadBadge, 180000); // 3 min — less load
});
