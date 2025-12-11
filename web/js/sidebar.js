document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');

    const open = () => {
        sidebar.classList.add('show');
        document.body.classList.add('sidebar-open');
    };

    const close = () => {
        sidebar.classList.remove('show');
        document.body.classList.remove('sidebar-open');
    };

    toggleBtn?.addEventListener('click', open);
    closeBtn?.addEventListener('click', close);

    document.body.addEventListener('click', (e) => {
        if (sidebar.classList.contains('show') &&
            !sidebar.contains(e.target) &&
            !toggleBtn.contains(e.target)) {
            close();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('show')) close();
    });
});