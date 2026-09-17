(() => {
    const root = document.documentElement;
    const storageKey = 'kamal-theme';

    function preferredTheme() {
        const saved = localStorage.getItem(storageKey);

        if (saved === 'light' || saved === 'dark') {
            return saved;
        }

        return window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
    }

    function applyTheme(theme, notify = false) {
        root.dataset.theme = theme;
        localStorage.setItem(storageKey, theme);

        document.querySelectorAll('[data-theme-icon]').forEach((icon) => {
            icon.className = theme === 'dark'
                ? 'fa-solid fa-sun'
                : 'fa-solid fa-moon';
        });

        document.querySelectorAll('[data-theme-label]').forEach((label) => {
            label.textContent = theme === 'dark'
                ? 'Use light mode'
                : 'Use dark mode';
        });

        if (notify) {
            toast(
                theme === 'dark'
                    ? 'Dark appearance enabled.'
                    : 'Light appearance enabled.',
                {
                    title: theme === 'dark' ? 'Dark mode' : 'Light mode',
                    tone: 'info',
                    duration: 1800
                }
            );
        }
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
        if (!message) {
            return;
        }

        const tone = options.tone || 'info';
        const title = options.title || {
            success: 'Done',
            danger: 'Something needs attention',
            warning: 'Check this',
            info: 'Kamal Car Wash'
        }[tone];

        const element = document.createElement('div');
        element.className = `ios-toast ${tone}`;
        element.setAttribute('role', tone === 'danger' ? 'alert' : 'status');
        element.innerHTML = `
            <i class="fa-solid ${iconFor(tone)}"></i>
            <div>
                <strong></strong>
                <span></span>
            </div>
        `;

        element.querySelector('strong').textContent = title;
        element.querySelector('span').textContent = message;
        document.body.appendChild(element);

        requestAnimationFrame(() => element.classList.add('show'));

        setTimeout(() => {
            element.classList.remove('show');
            setTimeout(() => element.remove(), 260);
        }, options.duration || 3400);
    }

    function confirmGlass(options = {}) {
        return new Promise((resolve) => {
            const tone = options.tone || 'danger';
            const overlay = document.createElement('div');

            overlay.className = 'kcw-alert-overlay staff-alert-overlay';
            overlay.innerHTML = `
                <div
                    class="kcw-alert-card ${tone}"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="staff-alert-title"
                >
                    <button
                        class="kcw-alert-close"
                        type="button"
                        aria-label="Close"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                    <div class="kcw-alert-icon">
                        <i class="fa-solid ${iconFor(tone)}"></i>
                    </div>

                    <h2 id="staff-alert-title"></h2>
                    <p></p>

                    <div class="kcw-alert-actions">
                        <button
                            type="button"
                            class="kcw-alert-btn secondary"
                            data-cancel
                        ></button>

                        <button
                            type="button"
                            class="kcw-alert-btn primary"
                            data-confirm-ok
                        ></button>
                    </div>
                </div>
            `;

            overlay.querySelector('h2').textContent = options.title || 'Are you sure?';
            overlay.querySelector('p').textContent = options.message || 'Please confirm this action.';
            overlay.querySelector('[data-cancel]').textContent = options.cancelText || 'Cancel';
            overlay.querySelector('[data-confirm-ok]').textContent = options.confirmText || 'Continue';

            document.body.appendChild(overlay);
            requestAnimationFrame(() => overlay.classList.add('open'));

            const finish = (answer) => {
                overlay.classList.remove('open');
                setTimeout(() => overlay.remove(), 180);
                resolve(answer);
            };

            overlay
                .querySelector('[data-confirm-ok]')
                .addEventListener('click', () => finish(true), { once: true });

            overlay
                .querySelector('[data-cancel]')
                .addEventListener('click', () => finish(false), { once: true });

            overlay
                .querySelector('.kcw-alert-close')
                .addEventListener('click', () => finish(false), { once: true });

            overlay.addEventListener('click', (event) => {
                if (event.target === overlay) {
                    finish(false);
                }
            });
        });
    }

    function closeProfileMenus(exceptRoot = null) {
        document.querySelectorAll('[data-profile-menu-root]').forEach((menuRoot) => {
            if (menuRoot === exceptRoot) {
                return;
            }

            const trigger = menuRoot.querySelector('[data-profile-menu-toggle]');
            const menu = menuRoot.querySelector('[data-profile-menu]');

            trigger?.setAttribute('aria-expanded', 'false');
            menu?.classList.remove('open');
        });
    }

    window.KamalUI = {
        toast,
        confirm: confirmGlass
    };

    applyTheme(preferredTheme());

    document.addEventListener('click', async (event) => {
        const themeButton = event.target.closest('[data-theme-toggle]');

        if (themeButton) {
            applyTheme(
                root.dataset.theme === 'dark' ? 'light' : 'dark',
                true
            );
            return;
        }

        const profileTrigger = event.target.closest('[data-profile-menu-toggle]');

        if (profileTrigger) {
            const menuRoot = profileTrigger.closest('[data-profile-menu-root]');
            const menu = menuRoot?.querySelector('[data-profile-menu]');

            if (!menuRoot || !menu) {
                return;
            }

            const willOpen = !menu.classList.contains('open');
            closeProfileMenus(menuRoot);
            menu.classList.toggle('open', willOpen);
            profileTrigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            return;
        }

        if (!event.target.closest('[data-profile-menu-root]')) {
            closeProfileMenus();
        }

        const menuButton = event.target.closest('[data-staff-menu-toggle]');

        if (menuButton) {
            document.querySelector('[data-staff-nav]')?.classList.toggle('open');
            closeProfileMenus();
            return;
        }

        const passwordButton = event.target.closest('[data-password-toggle]');

        if (passwordButton) {
            const target = document.getElementById(
                passwordButton.dataset.passwordToggle
            );

            if (!target) {
                return;
            }

            const reveal = target.type === 'password';
            target.type = reveal ? 'text' : 'password';

            const icon = passwordButton.querySelector('i');
            if (icon) {
                icon.className = reveal
                    ? 'fa-solid fa-eye-slash'
                    : 'fa-solid fa-eye';
            }

            passwordButton.setAttribute(
                'aria-label',
                reveal ? 'Hide password' : 'Show password'
            );
            return;
        }

        const confirmTarget = event.target.closest(
            '[data-confirm], a[href*="logout.php"]'
        );

        if (confirmTarget && confirmTarget.dataset.kcwConfirmed !== '1') {
            event.preventDefault();

            const isLogout = confirmTarget.matches('a[href*="logout.php"]');
            const okay = await confirmGlass({
                title: confirmTarget.dataset.confirmTitle || (
                    isLogout ? 'Sign out?' : 'Confirm action'
                ),
                message: confirmTarget.dataset.confirm || (
                    isLogout
                        ? 'You will need to sign in again to access the staff portal.'
                        : 'Are you sure you want to continue?'
                ),
                confirmText: confirmTarget.dataset.confirmText || (
                    isLogout ? 'Sign out' : 'Continue'
                ),
                cancelText: confirmTarget.dataset.cancelText || 'Cancel',
                tone: confirmTarget.dataset.confirmTone || 'danger'
            });

            if (okay) {
                confirmTarget.dataset.kcwConfirmed = '1';

                if (confirmTarget.tagName === 'A') {
                    window.location.href = confirmTarget.href;
                } else if (confirmTarget.form) {
                    confirmTarget.form.requestSubmit();
                } else {
                    confirmTarget.click();
                }
            }

            return;
        }

        if (!event.target.closest('.staff-header')) {
            document.querySelector('[data-staff-nav]')?.classList.remove('open');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeProfileMenus();
            document.querySelector('[data-staff-nav]')?.classList.remove('open');
        }
    });

    document.querySelectorAll('.ios-toast').forEach((existing) => {
        const message = existing.querySelector('span')?.textContent?.trim();
        const title = existing.querySelector('strong')?.textContent?.trim();
        const tone = existing.classList.contains('error')
            ? 'danger'
            : existing.classList.contains('success')
                ? 'success'
                : 'info';

        existing.remove();
        toast(message, { title, tone });
    });

    document.querySelectorAll('[data-auto-submit]').forEach((element) => {
        element.addEventListener('change', () => element.form?.submit());
    });

    document.querySelectorAll('[data-file-preview]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            const targets = document.querySelectorAll(input.dataset.filePreview);

            if (!file || !targets.length) {
                return;
            }

            if (file.size > 2000000) {
                input.value = '';
                toast(
                    'Please choose an image smaller than 2 MB.',
                    {
                        title: 'Photo is too large',
                        tone: 'warning'
                    }
                );
                return;
            }

            const reader = new FileReader();
            reader.onload = (readerEvent) => {
                targets.forEach((target) => {
                    target.src = readerEvent.target.result;
                });
            };
            reader.readAsDataURL(file);

            toast(
                'The new photo is ready. Save your profile to apply it.',
                {
                    title: 'Photo selected',
                    tone: 'info',
                    duration: 2400
                }
            );
        });
    });
})();

/* Shared page-transition loader. */
(() => {
    const current = document.currentScript;

    if (!current?.src || window.__kcwPageTransitionLoaderStarted) {
        return;
    }

    window.__kcwPageTransitionLoaderStarted = true;

    const cssHref = new URL('../css/page-transition.css', current.src).href;
    const scriptSrc = new URL('page-transition.js', current.src).href;

    if (!document.querySelector('link[data-kcw-page-transition]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = cssHref;
        link.dataset.kcwPageTransition = 'true';
        document.head.appendChild(link);
    }

    if (!document.querySelector('script[data-kcw-page-transition]')) {
        const script = document.createElement('script');
        script.src = scriptSrc;
        script.defer = true;
        script.dataset.kcwPageTransition = 'true';
        document.head.appendChild(script);
    }
})();
