(() => {
    const storageKey = 'kamal-theme';
    const root = document.documentElement;
    const savedTheme = localStorage.getItem(storageKey);
    const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    root.dataset.theme = savedTheme || (systemPrefersDark ? 'dark' : 'light');

    function syncThemeButtons() {
        const isDark = root.dataset.theme === 'dark';
        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            const icon = button.querySelector('i');
            const label = button.querySelector('[data-theme-label]');
            button.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
            button.setAttribute('title', isDark ? 'Light mode' : 'Dark mode');
            if (icon) icon.className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            if (label) label.textContent = isDark ? 'Light mode' : 'Dark mode';
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        syncThemeButtons();

        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
                localStorage.setItem(storageKey, root.dataset.theme);
                syncThemeButtons();
            });
        });

        document.querySelectorAll('[data-nav-toggle]').forEach((button) => {
            const targetId = button.getAttribute('aria-controls');
            const nav = targetId ? document.getElementById(targetId) : null;
            if (!nav) return;
            button.addEventListener('click', () => {
                const open = nav.classList.toggle('open');
                button.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            nav.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => {
                    nav.classList.remove('open');
                    button.setAttribute('aria-expanded', 'false');
                });
            });
        });

        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            const inputId = button.getAttribute('data-password-toggle');
            const input = document.getElementById(inputId);
            if (!input) return;
            button.addEventListener('click', () => {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                const icon = button.querySelector('i');
                if (icon) icon.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            });
        });

        document.querySelectorAll('[data-confirm]').forEach((element) => {
            element.addEventListener('click', (event) => {
                const message = element.getAttribute('data-confirm') || 'Are you sure?';
                if (!window.confirm(message)) event.preventDefault();
            });
        });
    });
})();
