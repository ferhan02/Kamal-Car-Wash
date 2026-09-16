(() => {
    const root = document.documentElement;
    const storageKey = 'kamal-theme';

    function preferredTheme() {
        const saved = localStorage.getItem(storageKey);
        if (saved === 'light' || saved === 'dark') return saved;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        root.dataset.theme = theme;
        localStorage.setItem(storageKey, theme);
        document.querySelectorAll('[data-theme-icon]').forEach(icon => {
            icon.className = theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        });
        document.querySelectorAll('[data-theme-label]').forEach(label => {
            label.textContent = theme === 'dark' ? 'Use light mode' : 'Use dark mode';
        });
    }

    applyTheme(preferredTheme());

    document.addEventListener('click', event => {
        const themeButton = event.target.closest('[data-theme-toggle]');
        if (themeButton) {
            applyTheme(root.dataset.theme === 'dark' ? 'light' : 'dark');
            return;
        }

        const menuButton = event.target.closest('[data-staff-menu-toggle]');
        if (menuButton) {
            const nav = document.querySelector('[data-staff-nav]');
            if (nav) nav.classList.toggle('open');
            return;
        }

        const passwordButton = event.target.closest('[data-password-toggle]');
        if (passwordButton) {
            const target = document.getElementById(passwordButton.dataset.passwordToggle);
            if (!target) return;
            const reveal = target.type === 'password';
            target.type = reveal ? 'text' : 'password';
            const icon = passwordButton.querySelector('i');
            if (icon) icon.className = reveal ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
            passwordButton.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
            return;
        }

        if (!event.target.closest('.staff-header')) {
            document.querySelector('[data-staff-nav]')?.classList.remove('open');
        }
    });

    const clock = document.querySelector('[data-staff-clock]');
    const updateClock = () => {
        if (!clock) return;
        clock.textContent = new Intl.DateTimeFormat(undefined, { hour: '2-digit', minute: '2-digit' }).format(new Date());
    };
    updateClock();
    setInterval(updateClock, 30000);

    document.querySelectorAll('.ios-toast').forEach(toast => {
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translate(-50%, -8px)';
            setTimeout(() => toast.remove(), 250);
        }, 3400);
    });

    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', event => {
            const message = element.dataset.confirm || 'Are you sure?';
            if (!window.confirm(message)) event.preventDefault();
        });
    });

    document.querySelectorAll('[data-auto-submit]').forEach(element => {
        element.addEventListener('change', () => element.form?.submit());
    });

    document.querySelectorAll('[data-file-preview]').forEach(input => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            const target = document.querySelector(input.dataset.filePreview);
            if (!file || !target) return;
            const reader = new FileReader();
            reader.onload = e => target.src = e.target.result;
            reader.readAsDataURL(file);
        });
    });
})();
