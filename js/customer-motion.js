(() => {
    const body = document.body;

    if (!body || body.classList.contains('staff-body')) {
        return;
    }

    const reduceMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    const ignoredSelectors = [
        '.site-topbar',
        '.site-header',
        '.site-nav',
        '.customer-header-actions',
        '.kcw-profile-menu',
        '.kcw-profile-dropdown',
        '.kcw-alert-overlay',
        '.kcw-alert-card',
        '.kcw-glass-toast',
        '.crop-modal',
        '.crop-dialog',
        'script',
        'style',
        'link'
    ];

    const shouldIgnore = (element) => {
        return ignoredSelectors.some((selector) => element.matches(selector));
    };

    const motionElements = [];
    const seen = new Set();
    const directions = ['left', 'right', 'up', 'scale'];

    const addMotion = (element, direction = 'up', delay = 0, intro = false) => {
        if (
            !element
            || seen.has(element)
            || shouldIgnore(element)
            || element.closest('.kcw-profile-dropdown, .crop-modal, .kcw-alert-overlay')
        ) {
            return;
        }

        seen.add(element);
        element.classList.add('kcw-motion');
        element.dataset.kcwMotion = direction;
        element.style.setProperty('--kcw-motion-delay', `${delay}ms`);

        if (intro) {
            element.classList.add('kcw-motion-intro');
        }

        motionElements.push(element);
    };

    const findFirstContentSection = () => {
        const candidates = Array.from(
            document.querySelectorAll('body > section, body > main, main')
        );

        return candidates.find((element) => {
            return !element.classList.contains('site-footer');
        }) || null;
    };

    const firstSection = findFirstContentSection();

    if (firstSection) {
        const heroCopy = firstSection.querySelector(
            '.hero-copy, .booking-hero .container, .profile-hero .container, '
            + '.vehicle-hero .container, .about-hero .container, '
            + '.contact-hero .container, .receipt-hero .container, '
            + '.payment-hero .container'
        );

        const heroSide = firstSection.querySelector(
            '.hero-panel, aside, .reserve-visual, [class*="visual"]'
        );

        if (heroCopy) {
            addMotion(heroCopy, 'left', 40, true);
        }

        if (heroSide && heroSide !== heroCopy) {
            addMotion(heroSide, 'right', 110, true);
        }

        if (!heroCopy) {
            const introContainer = firstSection.querySelector(':scope > .container')
                || firstSection.querySelector('.container')
                || firstSection;

            const directChildren = Array.from(introContainer.children)
                .filter((child) => !shouldIgnore(child));

            if (directChildren.length > 1) {
                directChildren.slice(0, 3).forEach((child, index) => {
                    addMotion(
                        child,
                        index % 2 === 0 ? 'left' : 'right',
                        index * 70,
                        true
                    );
                });
            } else {
                addMotion(introContainer, 'up', 40, true);
            }
        }
    }

    const structuralSelectors = [
        'body > section:not(:first-of-type) > .container > *',
        'main > *',
        '.profile-layout > *',
        '.vehicle-layout > *',
        '.booking-layout > *',
        '.contact-layout > *',
        '.about-grid > *',
        '.receipt-list > *',
        '.booking-list > *',
        '.package-grid > *',
        '.vehicle-list > *'
    ];

    const candidates = Array.from(
        document.querySelectorAll(structuralSelectors.join(','))
    );

    let motionIndex = 0;

    candidates.forEach((element) => {
        if (
            seen.has(element)
            || element.closest('.site-header, .site-topbar')
            || element.classList.contains('site-footer')
        ) {
            return;
        }

        const direction = directions[motionIndex % directions.length];
        const delay = (motionIndex % 3) * 55;

        addMotion(element, direction, delay);
        motionIndex += 1;
    });

    const footerGroups = document.querySelectorAll(
        '.site-footer > .container > div:not(.footer-bottom), '
        + '.site-footer .footer-bottom'
    );

    footerGroups.forEach((element, index) => {
        addMotion(
            element,
            index % 2 === 0 ? 'left' : 'right',
            (index % 3) * 55
        );
    });

    if (reduceMotion) {
        motionElements.forEach((element) => {
            element.classList.add('is-visible');
        });
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                entry.target.classList.toggle(
                    'is-visible',
                    entry.isIntersecting
                );
            });
        },
        {
            threshold: 0.14,
            rootMargin: '0px 0px -6% 0px'
        }
    );

    motionElements.forEach((element) => observer.observe(element));

    let ticking = false;

    const updatePageParallax = () => {
        const offset = Math.round(window.scrollY * -0.075);

        body.style.setProperty(
            '--kcw-pattern-parallax-y',
            `${offset}px`
        );

        ticking = false;
    };

    const requestPageParallax = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(updatePageParallax);
    };

    updatePageParallax();

    window.addEventListener(
        'scroll',
        requestPageParallax,
        { passive: true }
    );

    window.addEventListener(
        'resize',
        requestPageParallax
    );
})();
