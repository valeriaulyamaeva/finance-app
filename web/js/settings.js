document.addEventListener('DOMContentLoaded', () => {
    const msg = document.getElementById('saveMessage');
    const btn = document.getElementById('saveSettingsBtn');

    btn.addEventListener('click', async () => {
        const formData = new FormData();
        formData.append('User[username]', document.getElementById('username').value);
        formData.append('User[email]', document.getElementById('email').value);
        formData.append('User[password]', document.getElementById('password').value);
        formData.append('User[theme]', document.getElementById('theme').value);
        formData.append('User[currency]', document.getElementById('currency').value);

        try {
            const res = await fetch(saveUrl, {
                method: 'POST',
                body: formData,
                headers: {'X-CSRF-Token': document.querySelector("meta[name='csrf-token']").content}
            });
            const data = await res.json();

            if (data.success) {
                msg.style.display = 'block';
                msg.style.color = '#16a34a';
                setTimeout(() => location.reload(), 1000);
            } else {
                msg.style.display = 'block';
                msg.style.color = '#dc2626';
                msg.textContent = data.message || 'Ошибка при сохранении';
            }
        } catch (error) {
            msg.style.display = 'block';
            msg.style.color = '#dc2626';
            msg.textContent = 'Ошибка: ' + error.message;
        }
    });

    const themeSelect = document.getElementById('theme');
    function applyTheme(theme) {
        if (theme === 'dark') {
            document.body.classList.add('dark-theme');
        } else {
            document.body.classList.remove('dark-theme');
        }
    }
    applyTheme(userTheme);
    themeSelect.addEventListener('change', e => applyTheme(e.target.value));
});
