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

    function ensureAlertRoot() {
        let rootEl = document.getElementById('kcw-alert-root');
        if (rootEl) return rootEl;
        rootEl = document.createElement('div');
        rootEl.id = 'kcw-alert-root';
        document.body.appendChild(rootEl);
        return rootEl;
    }

    function iconFor(tone) {
        return {
            success: 'fa-circle-check',
            danger: 'fa-triangle-exclamation',
            warning: 'fa-circle-exclamation',
            info: 'fa-circle-info'
        }[tone] || 'fa-circle-info';
    }

    function toast(message, options = {}) {
        if (!message) return;
        const tone = options.tone || 'info';
        const title = options.title || ({ success: 'Done', danger: 'Something needs attention', warning: 'Check this', info: 'Kamal Car Wash' }[tone]);
        const toastEl = document.createElement('div');
        toastEl.className = `kcw-glass-toast ${tone}`;
        toastEl.setAttribute('role', tone === 'danger' ? 'alert' : 'status');
        toastEl.innerHTML = `<i class="fa-solid ${iconFor(tone)}"></i><div><strong></strong><span></span></div>`;
        toastEl.querySelector('strong').textContent = title;
        toastEl.querySelector('span').textContent = message;
        document.body.appendChild(toastEl);
        requestAnimationFrame(() => toastEl.classList.add('show'));
        window.setTimeout(() => {
            toastEl.classList.remove('show');
            window.setTimeout(() => toastEl.remove(), 260);
        }, options.duration || 3200);
    }

    function confirmGlass(options = {}) {
        return new Promise((resolve) => {
            const host = ensureAlertRoot();
            const tone = options.tone || 'danger';
            const overlay = document.createElement('div');
            overlay.className = 'kcw-alert-overlay';
            overlay.innerHTML = `
                <div class="kcw-alert-card ${tone}" role="dialog" aria-modal="true" aria-labelledby="kcw-alert-title">
                    <button class="kcw-alert-close" type="button" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                    <div class="kcw-alert-icon"><i class="fa-solid ${iconFor(tone)}"></i></div>
                    <h2 id="kcw-alert-title"></h2>
                    <p></p>
                    <div class="kcw-alert-actions">
                        <button type="button" class="kcw-alert-btn secondary" data-kcw-cancel></button>
                        <button type="button" class="kcw-alert-btn primary" data-kcw-confirm></button>
                    </div>
                </div>`;
            overlay.querySelector('h2').textContent = options.title || 'Are you sure?';
            overlay.querySelector('p').textContent = options.message || 'Please confirm this action.';
            overlay.querySelector('[data-kcw-cancel]').textContent = options.cancelText || 'Cancel';
            overlay.querySelector('[data-kcw-confirm]').textContent = options.confirmText || 'Continue';
            host.appendChild(overlay);
            requestAnimationFrame(() => overlay.classList.add('open'));

            const finish = (answer) => {
                overlay.classList.remove('open');
                window.setTimeout(() => overlay.remove(), 180);
                resolve(answer);
            };
            overlay.querySelector('[data-kcw-confirm]').addEventListener('click', () => finish(true), { once: true });
            overlay.querySelector('[data-kcw-cancel]').addEventListener('click', () => finish(false), { once: true });
            overlay.querySelector('.kcw-alert-close').addEventListener('click', () => finish(false), { once: true });
            overlay.addEventListener('click', (event) => { if (event.target === overlay) finish(false); });
            document.addEventListener('keydown', function esc(event) {
                if (event.key === 'Escape') { document.removeEventListener('keydown', esc); finish(false); }
            });
        });
    }

    window.KamalUI = { toast, confirm: confirmGlass };

    document.addEventListener('DOMContentLoaded', () => {
        syncThemeButtons();

        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
                localStorage.setItem(storageKey, root.dataset.theme);
                syncThemeButtons();
                toast(root.dataset.theme === 'dark' ? 'Dark appearance enabled.' : 'Light appearance enabled.', {
                    title: root.dataset.theme === 'dark' ? 'Dark mode' : 'Light mode',
                    tone: 'info',
                    duration: 1800
                });
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

        document.querySelectorAll('[data-kcw-toast]').forEach((element) => {
            toast(element.dataset.kcwToast || element.textContent.trim(), {
                title: element.dataset.kcwToastTitle || undefined,
                tone: element.dataset.kcwToastTone || 'info'
            });
            element.remove();
        });

        document.addEventListener('click', async (event) => {
            const element = event.target.closest('[data-confirm], a[href*="logout.php"]');
            if (!element || element.dataset.kcwConfirmed === '1') return;
            if (element.matches('a[href*="logout.php"]') || element.hasAttribute('data-confirm')) {
                event.preventDefault();
                const isLogout = element.matches('a[href*="logout.php"]');
                const okay = await confirmGlass({
                    title: element.dataset.confirmTitle || (isLogout ? 'Sign out?' : 'Confirm action'),
                    message: element.dataset.confirm || (isLogout ? 'You will need to sign in again to access your account.' : 'Are you sure you want to continue?'),
                    confirmText: element.dataset.confirmText || (isLogout ? 'Sign out' : 'Continue'),
                    cancelText: element.dataset.cancelText || 'Cancel',
                    tone: element.dataset.confirmTone || 'danger'
                });
                if (!okay) return;
                element.dataset.kcwConfirmed = '1';
                if (element.tagName === 'A') window.location.href = element.href;
                else if (element.form) element.form.requestSubmit();
                else element.click();
            }
        });
    });
})();
