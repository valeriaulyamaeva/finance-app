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
            document.documentElement.style.setProperty('--body-bg', '#1f1f1f');
            document.documentElement.style.setProperty('--body-color', '#f3f3f3');
            document.documentElement.style.setProperty('--form-bg', '#2a2a2a');
            document.documentElement.style.setProperty('--form-border', '#4b5563');
            document.documentElement.style.setProperty('--input-bg', '#374151');
            document.documentElement.style.setProperty('--input-color', '#f3f3f3');
        } else {
            document.documentElement.style.setProperty('--body-bg', '#f9f7f4');
            document.documentElement.style.setProperty('--body-color', '#4b453f');
            document.documentElement.style.setProperty('--form-bg', '#fff');
            document.documentElement.style.setProperty('--form-border', '#d1d5db');
            document.documentElement.style.setProperty('--input-bg', '#fff');
            document.documentElement.style.setProperty('--input-color', '#4b453f');
        }
    }
    applyTheme(userTheme);
    themeSelect.addEventListener('change', e => applyTheme(e.target.value));
});
