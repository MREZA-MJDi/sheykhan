import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/**
 * =========================================================
 * SHEYKHAN PUBLIC HOME
 * Vanilla JS interaction layer
 * =========================================================
 */

(() => {
    'use strict';

    const ready = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener(
                'DOMContentLoaded',
                callback,
                { once: true }
            );
        } else {
            callback();
        }
    };

    ready(() => {

        /*
        |--------------------------------------------------------------------------
        | Reveal on scroll
        |--------------------------------------------------------------------------
        */

        const revealElements = document.querySelectorAll(
            '#main-content .home-reveal, .home-reveal'
        );

        if (revealElements.length) {

            if (!('IntersectionObserver' in window)) {

                revealElements.forEach((element) => {
                    element.classList.add('is-visible');
                });

            } else {

                const revealObserver = new IntersectionObserver(
                    (entries, observer) => {

                        entries.forEach((entry) => {

                            if (!entry.isIntersecting) {
                                return;
                            }

                            entry.target.classList.add('is-visible');

                            observer.unobserve(entry.target);
                        });

                    },
                    {
                        root: null,
                        rootMargin: '0px 0px -8% 0px',
                        threshold: 0.08,
                    }
                );

                revealElements.forEach((element) => {
                    revealObserver.observe(element);
                });
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Smooth anchor navigation
        |--------------------------------------------------------------------------
        */

        const anchorLinks = document.querySelectorAll(
            'a[href^="#"]'
        );

        anchorLinks.forEach((link) => {

            link.addEventListener('click', (event) => {

                const href = link.getAttribute('href');

                if (!href || href === '#') {
                    return;
                }

                const target = document.querySelector(href);

                if (!target) {
                    return;
                }

                event.preventDefault();

                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            });

        });


        /*
        |--------------------------------------------------------------------------
        | Hero feature card micro interaction
        |--------------------------------------------------------------------------
        */

        const heroCard = document.querySelector(
            '.home-feature-course'
        );

        if (heroCard) {

            heroCard.addEventListener(
                'mouseenter',
                () => {
                    heroCard.dataset.hovered = 'true';
                }
            );

            heroCard.addEventListener(
                'mouseleave',
                () => {
                    delete heroCard.dataset.hovered;
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Prevent image layout jump
        |--------------------------------------------------------------------------
        */

        const lazyImages = document.querySelectorAll(
            'img[loading="lazy"]'
        );

        lazyImages.forEach((image) => {

            image.addEventListener(
                'error',
                () => {

                    image.classList.add(
                        'home-image-error'
                    );

                    image.style.visibility = 'hidden';

                },
                { once: true }
            );

        });


        /*
        |--------------------------------------------------------------------------
        | Counter animation
        |--------------------------------------------------------------------------
        */

        const counters = document.querySelectorAll(
            '.home-proof strong'
        );

        const parsePersianNumber = (value) => {

            if (!value) {
                return null;
            }

            const normalized = value
                .replace(/[۰-۹]/g, (digit) =>
                    '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)
                )
                .replace(/[٬,]/g, '');

            const match = normalized.match(
                /[\d]+/
            );

            if (!match) {
                return null;
            }

            const number = Number(match[0]);

            return Number.isFinite(number)
                ? number
                : null;
        };

        const toPersianDigits = (value) => {

            return String(value).replace(
                /\d/g,
                (digit) =>
                    '۰۱۲۳۴۵۶۷۸۹'[digit]
            );
        };

        const animateCounter = (
            element,
            target
        ) => {

            if (!Number.isFinite(target)) {
                return;
            }

            if (target > 100000) {
                return;
            }

            const suffix = element.textContent.includes('+')
                ? '+'
                : '';

            const duration = 1100;
            const start = performance.now();

            const update = (now) => {

                const progress = Math.min(
                    (now - start) / duration,
                    1
                );

                const eased =
                    1 -
                    Math.pow(
                        1 - progress,
                        3
                    );

                const current = Math.round(
                    target * eased
                );

                element.textContent =
                    toPersianDigits(current) +
                    suffix;

                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            };

            requestAnimationFrame(update);
        };

        if (
            counters.length &&
            'IntersectionObserver' in window
        ) {

            const counterObserver =
                new IntersectionObserver(
                    (entries, observer) => {

                        entries.forEach((entry) => {

                            if (!entry.isIntersecting) {
                                return;
                            }

                            const target =
                                parsePersianNumber(
                                    entry.target.textContent
                                );

                            animateCounter(
                                entry.target,
                                target
                            );

                            observer.unobserve(
                                entry.target
                            );
                        });

                    },
                    {
                        threshold: .5,
                    }
                );

            counters.forEach((counter) => {
                counterObserver.observe(counter);
            });
        }


        /*
        |--------------------------------------------------------------------------
        | Card keyboard accessibility
        |--------------------------------------------------------------------------
        */

        const interactiveCards =
            document.querySelectorAll(
                '.home-store-card, .home-feature-course'
            );

        interactiveCards.forEach((card) => {

            card.addEventListener(
                'keydown',
                (event) => {

                    if (
                        event.key !== 'Enter' &&
                        event.key !== ' '
                    ) {
                        return;
                    }

                    if (
                        event.currentTarget.tagName
                        !== 'A'
                    ) {
                        return;
                    }

                    event.preventDefault();

                    event.currentTarget.click();
                }
            );

        });


        /*
        |--------------------------------------------------------------------------
        | Horizontal scroll support
        |
        | Useful for future mobile course/product rails.
        |--------------------------------------------------------------------------
        */

        const horizontalRails =
            document.querySelectorAll(
                '[data-home-horizontal]'
            );

        horizontalRails.forEach((rail) => {

            let isDown = false;
            let startX = 0;
            let scrollLeft = 0;

            rail.addEventListener(
                'pointerdown',
                (event) => {

                    isDown = true;

                    startX =
                        event.clientX;

                    scrollLeft =
                        rail.scrollLeft;

                    rail.setPointerCapture(
                        event.pointerId
                    );
                }
            );

            rail.addEventListener(
                'pointermove',
                (event) => {

                    if (!isDown) {
                        return;
                    }

                    const distance =
                        event.clientX -
                        startX;

                    rail.scrollLeft =
                        scrollLeft -
                        distance;
                }
            );

            const stopDrag = () => {
                isDown = false;
            };

            rail.addEventListener(
                'pointerup',
                stopDrag
            );

            rail.addEventListener(
                'pointercancel',
                stopDrag
            );

            rail.addEventListener(
                'pointerleave',
                stopDrag
            );

        });


        /*
        |--------------------------------------------------------------------------
        | Scroll state
        |--------------------------------------------------------------------------
        */

        const updateScrollState = () => {

            document.documentElement.toggleAttribute(
                'data-home-scrolled',
                window.scrollY > 24
            );

        };

        updateScrollState();

        window.addEventListener(
            'scroll',
            updateScrollState,
            {
                passive: true,
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Back/forward browser navigation
        |--------------------------------------------------------------------------
        */

        window.addEventListener(
            'pageshow',
            () => {
                document.documentElement
                    .removeAttribute(
                        'data-home-loading'
                    );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Page loaded
        |--------------------------------------------------------------------------
        */

        document.documentElement
            .removeAttribute(
                'data-home-loading'
            );

    });

})();
