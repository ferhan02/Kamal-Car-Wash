(() => {
    if (window.__kcwPageTransitionLoaded) {
        return;
    }

    window.__kcwPageTransitionLoaded = true;

    const root = document.documentElement;
    const reduceMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    const createOverlay = () => {
        if (document.querySelector('.kcw-page-transition-overlay')) {
            return;
        }

        const overlay = document.createElement('div');
        overlay.className = 'kcw-page-transition-overlay';
        overlay.setAttribute('aria-hidden', 'true');
        overlay.innerHTML = '<div class="kcw-page-transition-line"></div>';
        document.body.appendChild(overlay);
    };

    const markPageReady = () => {
        root.classList.remove('kcw-page-leaving');
        root.classList.add('kcw-page-transition-init');

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                root.classList.add('kcw-page-ready');
                root.classList.remove('kcw-page-transition-init');
            });
        });
    };

    const isTransitionableLink = (link, event) => {
        if (!link || reduceMotion) {
            return false;
        }

        if (
            event.defaultPrevented
            || event.button !== 0
            || event.metaKey
            || event.ctrlKey
            || event.shiftKey
            || event.altKey
        ) {
            return false;
        }

        if (
            link.hasAttribute('download')
            || link.target === '_blank'
            || link.dataset.noPageTransition !== undefined
            || link.matches('[data-confirm]')
            || link.href.includes('logout.php')
        ) {
            return false;
        }

        const rawHref = link.getAttribute('href');

        if (
            !rawHref
            || rawHref.startsWith('#')
            || rawHref.startsWith('mailto:')
            || rawHref.startsWith('tel:')
            || rawHref.startsWith('javascript:')
        ) {
            return false;
        }

        let destination;

        try {
            destination = new URL(link.href, window.location.href);
        } catch {
            return false;
        }

        if (destination.origin !== window.location.origin) {
            return false;
        }

        const sameDocument =
            destination.pathname === window.location.pathname
            && destination.search === window.location.search;

        if (sameDocument && destination.hash) {
            return false;
        }

        return destination.href !== window.location.href;
    };

    const leaveFor = (href) => {
        if (root.classList.contains('kcw-page-leaving')) {
            return;
        }

        root.classList.add('kcw-page-leaving');
        root.classList.remove('kcw-page-ready');

        window.setTimeout(() => {
            window.location.href = href;
        }, 215);
    };

    const init = () => {
        createOverlay();
        markPageReady();

        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');

            if (!isTransitionableLink(link, event)) {
                return;
            }

            event.preventDefault();
            leaveFor(link.href);
        });

        /* Show the exit state for normal form navigations without delaying or
           altering POST data / clicked submit-button names. */
        document.addEventListener('submit', (event) => {
            if (
                reduceMotion
                || event.defaultPrevented
                || event.target.matches('[data-no-page-transition]')
            ) {
                return;
            }

            root.classList.add('kcw-page-leaving');
            root.classList.remove('kcw-page-ready');
        });

        window.addEventListener('pageshow', () => {
            root.classList.remove('kcw-page-leaving');
            markPageReady();
        });

        window.addEventListener('pagehide', () => {
            root.classList.remove('kcw-page-ready');
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
