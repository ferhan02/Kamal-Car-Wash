(() => {
    const body = document.body;

    if (!body) {
        return;
    }

    const isStaff = body.classList.contains('staff-body');
    const isLanding = body.classList.contains('kcw-landing-motion');

    if (!isStaff && !isLanding) {
        return;
    }

    const reduceMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches;

    const seen = new Set();
    const motionElements = [];
    const directions = ['left', 'right', 'up', 'scale'];

    const ignoreSelector = [
        '.staff-header',
        '.staff-admin-toolbar',
        '.staff-mobile-dock',
        '.landing-header',
        '.kcw-profile-dropdown',
        '.kcw-alert-overlay',
        '.kcw-alert-card',
        '.kcw-glass-toast',
        '.ios-toast',
        '.crop-modal',
        '.crop-dialog',
        'script',
        'style',
        'link'
    ].join(',');

    const shouldIgnore = (element) => {
        return element.matches(ignoreSelector)
            || Boolean(element.closest(
                '.kcw-profile-dropdown, .kcw-alert-overlay, .crop-modal'
            ));
    };

    const addMotion = (
        element,
        direction = 'up',
        delay = 0,
        intro = false
    ) => {
        if (!element || seen.has(element) || shouldIgnore(element)) {
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

    if (isStaff) {
        const loginShell = document.querySelector('.login-shell');

        if (loginShell) {
            const visual = loginShell.querySelector('.login-visual');
            const panel = loginShell.querySelector('.login-panel');

            addMotion(visual, 'left', 40, true);
            addMotion(panel, 'right', 110, true);
        } else {
            const heading = document.querySelector('.staff-page-heading');
            const firstCards = Array.from(
                document.querySelectorAll(
                    '.staff-main > .ios-card, '
                    + '.staff-main > section > .ios-card, '
                    + '.staff-main > section > article'
                )
            ).slice(0, 3);

            addMotion(heading, 'left', 30, true);

            firstCards.forEach((card, index) => {
                addMotion(
                    card,
                    index % 2 === 0 ? 'right' : 'up',
                    90 + (index * 55),
                    true
                );
            });
        }

        const staffCandidates = document.querySelectorAll([
            '.staff-main > section',
            '.staff-main > article',
            '.staff-main .ios-card',
            '.staff-main .staff-card',
            '.staff-main .summary-tile',
            '.staff-main .ios-table-wrap',
            '.staff-main .mobile-staff > *',
            '.staff-footer-inner > *'
        ].join(','));

        let index = 0;

        staffCandidates.forEach((element) => {
            if (seen.has(element)) {
                return;
            }

            addMotion(
                element,
                directions[index % directions.length],
                (index % 3) * 55
            );
            index += 1;
        });
    }

    if (isLanding) {
        const content = document.querySelector('.landing-content');
        addMotion(content, 'left', 45, true);

        const featureHeading = document.querySelector(
            '.feature-section > .container > .eyebrow'
        );
        const featureTitle = document.querySelector(
            '.feature-section > .container > .section-heading'
        );

        addMotion(featureHeading, 'up', 0);
        addMotion(featureTitle, 'up', 55);

        document.querySelectorAll('.feature-card').forEach((card, index) => {
            addMotion(
                card,
                directions[index % directions.length],
                index * 70
            );
        });

        const portalCopy = document.querySelector('.portal-copy');
        const portalVisual = document.querySelector('.portal-visual');

        addMotion(portalCopy, 'left', 20);
        addMotion(portalVisual, 'right', 95);

        document.querySelectorAll(
            '.site-footer > .container > div'
        ).forEach((item, index) => {
            addMotion(
                item,
                index % 2 === 0 ? 'left' : 'right',
                (index % 3) * 55
            );
        });
    }

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

    const updateParallax = () => {
        const scrollY = window.scrollY;

        if (isStaff) {
            body.style.setProperty(
                '--kcw-page-parallax-y',
                `${Math.round(scrollY * -0.07)}px`
            );
        }

        if (isLanding) {
            body.style.setProperty(
                '--kcw-index-pattern-y',
                `${Math.round(scrollY * -0.07)}px`
            );

            const hero = document.querySelector('.landing-hero');

            if (hero) {
                const rect = hero.getBoundingClientRect();
                const progress = Math.max(
                    -1,
                    Math.min(1, -rect.top / Math.max(hero.offsetHeight, 1))
                );

                hero.style.setProperty(
                    '--kcw-index-hero-y',
                    `${Math.round(progress * 42)}px`
                );
            }
        }

        ticking = false;
    };

    const requestParallax = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(updateParallax);
    };

    updateParallax();

    window.addEventListener(
        'scroll',
        requestParallax,
        { passive: true }
    );

    window.addEventListener('resize', requestParallax);
})();
