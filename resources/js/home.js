(() => {
    'use strict';

    const ready = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
            return;
        }

        callback();
    };

    const safeSelector = (selector) => {
        try {
            return document.querySelector(selector);
        } catch {
            return null;
        }
    };

    ready(() => {
        const root = document.querySelector('#main-content.home-page');

        if (!root) return;

        const revealElements = root.querySelectorAll('.home-reveal');

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(
                (entries, instance) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;

                        entry.target.classList.add('is-visible');
                        instance.unobserve(entry.target);
                    });
                },
                {
                    rootMargin: '0px 0px -8% 0px',
                    threshold: 0.08,
                }
            );

            revealElements.forEach((element) => observer.observe(element));
        } else {
            revealElements.forEach((element) => element.classList.add('is-visible'));
        }

        root.querySelectorAll('a[href^="#"]').forEach((link) => {
            link.addEventListener('click', (event) => {
                const href = link.getAttribute('href');

                if (!href || href === '#') return;

                const target = safeSelector(href);

                if (!target) return;

                event.preventDefault();

                target.scrollIntoView({
                    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches
                        ? 'auto'
                        : 'smooth',
                    block: 'start',
                });

                if (history.replaceState) {
                    history.replaceState(null, '', href);
                }
            });
        });

        root.querySelectorAll('img[loading="lazy"]').forEach((image) => {
            image.addEventListener('error', () => {
                image.classList.add('home-image-error');
                image.setAttribute('data-image-failed', 'true');
            }, { once: true });
        });

        root.querySelectorAll('[data-home-keyboard-action]').forEach((element) => {
            element.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') return;

                event.preventDefault();
                element.click();
            });
        });

        root.querySelectorAll('[data-home-horizontal]').forEach((rail) => {
            let active = false;
            let startX = 0;
            let startScroll = 0;

            rail.addEventListener('pointerdown', (event) => {
                if (event.pointerType === 'touch') return;

                active = true;
                startX = event.clientX;
                startScroll = rail.scrollLeft;
                rail.setPointerCapture?.(event.pointerId);
            });

            rail.addEventListener('pointermove', (event) => {
                if (!active) return;

                rail.scrollLeft = startScroll - (event.clientX - startX);
            });

            const stop = () => {
                active = false;
            };

            rail.addEventListener('pointerup', stop);
            rail.addEventListener('pointercancel', stop);
        });

        const updateScrollState = () => {
            document.documentElement.toggleAttribute(
                'data-home-scrolled',
                window.scrollY > 24
            );
        };

        updateScrollState();

        window.addEventListener('scroll', updateScrollState, { passive: true });

        window.addEventListener('pageshow', () => {
            document.documentElement.removeAttribute('data-home-loading');
        });

        document.documentElement.removeAttribute('data-home-loading');
    });
})();