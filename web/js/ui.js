/**
 * Global UI helpers: confirm modal + toast notifications.
 * Available as window.appConfirm() and window.appToast().
 */
(function () {
    // ─── Confirm modal ──────────────────────────────────────────
    function ensureConfirmModal() {
        let modal = document.getElementById('appConfirmModal');
        if (modal) return modal;

        modal = document.createElement('div');
        modal.id = 'appConfirmModal';
        modal.className = 'app-confirm-modal';
        modal.innerHTML = `
            <div class="app-confirm-backdrop"></div>
            <div class="app-confirm-dialog">
                <div class="app-confirm-icon">
                    <i class="fas fa-exclamation"></i>
                </div>
                <h3 class="app-confirm-title"></h3>
                <p class="app-confirm-message"></p>
                <div class="app-confirm-actions">
                    <button class="app-confirm-cancel" type="button">Отмена</button>
                    <button class="app-confirm-ok" type="button">OK</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        return modal;
    }

    window.appConfirm = function ({ title = 'Подтвердить', message = '', confirmText = 'OK', cancelText = 'Отмена', danger = false } = {}) {
        return new Promise((resolve) => {
            const modal = ensureConfirmModal();
            modal.querySelector('.app-confirm-title').textContent = title;
            modal.querySelector('.app-confirm-message').textContent = message;
            const okBtn = modal.querySelector('.app-confirm-ok');
            const cancelBtn = modal.querySelector('.app-confirm-cancel');
            const dialog = modal.querySelector('.app-confirm-dialog');
            const backdrop = modal.querySelector('.app-confirm-backdrop');
            okBtn.textContent = confirmText;
            cancelBtn.textContent = cancelText;
            dialog.classList.toggle('app-confirm-dialog--danger', danger);
            okBtn.classList.toggle('app-confirm-ok--danger', danger);

            modal.classList.add('app-confirm-modal--open');

            const cleanup = (result) => {
                modal.classList.remove('app-confirm-modal--open');
                okBtn.removeEventListener('click', onOk);
                cancelBtn.removeEventListener('click', onCancel);
                backdrop.removeEventListener('click', onCancel);
                document.removeEventListener('keydown', onKey);
                resolve(result);
            };
            const onOk = () => cleanup(true);
            const onCancel = () => cleanup(false);
            const onKey = (e) => {
                if (e.key === 'Escape') cleanup(false);
                if (e.key === 'Enter') cleanup(true);
            };

            okBtn.addEventListener('click', onOk);
            cancelBtn.addEventListener('click', onCancel);
            backdrop.addEventListener('click', onCancel);
            document.addEventListener('keydown', onKey);

            setTimeout(() => okBtn.focus(), 50);
        });
    };

    // ─── Toast notifications ────────────────────────────────────
    function ensureToastContainer() {
        let container = document.getElementById('appToastContainer');
        if (container) return container;
        container = document.createElement('div');
        container.id = 'appToastContainer';
        container.className = 'app-toast-container';
        document.body.appendChild(container);
        return container;
    }

    window.appToast = function (message, type = 'info', duration = 3500) {
        const container = ensureToastContainer();
        const toast = document.createElement('div');
        toast.className = `app-toast app-toast--${type}`;
        const icon = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle',
        }[type] || 'fa-info-circle';
        toast.innerHTML = `<i class="fas ${icon}"></i><span>${message}</span>`;
        container.appendChild(toast);

        requestAnimationFrame(() => toast.classList.add('app-toast--visible'));

        setTimeout(() => {
            toast.classList.remove('app-toast--visible');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };
})();
