(() => {
    'use strict';

    const ready = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
            return;
        }
        callback();
    };

    ready(() => {
        document.querySelectorAll('[data-frame-hero]').forEach((root) => {
            const slides = Array.from(root.querySelectorAll('[data-frame-slide]'));
            const previous = root.querySelector('[data-frame-prev]');
            const next = root.querySelector('[data-frame-next]');
            const gotoButtons = Array.from(root.querySelectorAll('[data-frame-goto]'));
            const currentLabel = root.querySelector('[data-frame-current]');
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (slides.length < 2) return;

            let activeIndex = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
            let transitioning = false;
            let unlockTimer = 0;

            const digits = (value) => String(value).replace(/[0-9]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
            const updateControls = () => {
                if (currentLabel) currentLabel.textContent = digits(String(activeIndex + 1).padStart(2, '0'));
                gotoButtons.forEach((button, index) => {
                    const current = index === activeIndex;
                    button.classList.toggle('is-active', current);
                    button.setAttribute('aria-current', current ? 'true' : 'false');
                });
            };

            const makeNextImageReady = (index) => {
                const image = slides[index]?.querySelector('[data-frame-image]');
                if (!image || image.complete || (!image.currentSrc && !image.src)) return;
                if (image.loading === 'lazy') image.loading = 'eager';
            };

            const setActiveImmediately = (targetIndex) => {
                slides.forEach((slide, index) => {
                    const current = index === targetIndex;
                    slide.classList.toggle('is-active', current);
                    slide.classList.remove('is-entering');
                    slide.setAttribute('aria-hidden', current ? 'false' : 'true');
                    if (current) {
                        slide.removeAttribute('inert');
                    } else {
                        slide.setAttribute('inert', '');
                    }
                    slide.style.zIndex = '';
                    slide.style.clipPath = '';
                    slide.style.transform = '';
                    slide.style.opacity = '';
                    slide.style.filter = '';
                });
                activeIndex = targetIndex;
                updateControls();
            };

            const goTo = (requestedIndex, direction = 1) => {
                if (transitioning) return;
                const targetIndex = (requestedIndex + slides.length) % slides.length;
                if (targetIndex === activeIndex) return;

                const outgoing = slides[activeIndex];
                const incoming = slides[targetIndex];
                makeNextImageReady(targetIndex);

                if (reducedMotion || typeof incoming.animate !== 'function') {
                    setActiveImmediately(targetIndex);
                    return;
                }

                transitioning = true;
                root.classList.add('is-transitioning');
                window.clearTimeout(unlockTimer);

                incoming.removeAttribute('inert');
                incoming.setAttribute('aria-hidden', 'false');
                incoming.classList.add('is-active', 'is-entering');
                incoming.style.zIndex = '3';
                outgoing.style.zIndex = '2';

                const revealX = direction > 0 ? 66 : 34;
                const revealY = 46;
                const startClip = 'circle(0% at ' + revealX + '% ' + revealY + '%)';
                const endClip = 'circle(155% at ' + revealX + '% ' + revealY + '%)';
                const enterY = direction > 0 ? '2.8%' : '-2.8%';
                const exitY = direction > 0 ? '-2.4%' : '2.4%';

                const reveal = incoming.animate([
                    { clipPath: startClip, transform: 'translateY(' + enterY + ') scale(1.025)', opacity: 1 },
                    { clipPath: endClip, transform: 'translateY(0) scale(1)', opacity: 1 },
                ], {
                    duration: 790,
                    easing: 'cubic-bezier(.16, 1, .3, 1)',
                    fill: 'both',
                });

                const exit = outgoing.animate([
                    { transform: 'translateY(0) scale(1)', filter: 'brightness(1)', opacity: 1 },
                    { transform: 'translateY(' + exitY + ') scale(.985)', filter: 'brightness(.55)', opacity: .58 },
                ], {
                    duration: 720,
                    easing: 'cubic-bezier(.76, 0, .24, 1)',
                    fill: 'both',
                });

                activeIndex = targetIndex;
                updateControls();

                Promise.allSettled([reveal.finished, exit.finished]).then(() => {
                    outgoing.classList.remove('is-active');
                    outgoing.setAttribute('aria-hidden', 'true');
                    outgoing.setAttribute('inert', '');
                    incoming.classList.add('is-active');
                    incoming.classList.remove('is-entering');
                    incoming.removeAttribute('inert');
                    incoming.setAttribute('aria-hidden', 'false');

                    [outgoing, incoming].forEach((slide) => {
                        slide.style.zIndex = '';
                        slide.style.clipPath = '';
                        slide.style.transform = '';
                        slide.style.opacity = '';
                        slide.style.filter = '';
                    });

                    unlockTimer = window.setTimeout(() => {
                        root.classList.remove('is-transitioning');
                        transitioning = false;
                    }, 30);
                });
            };

            previous?.addEventListener('click', () => goTo(activeIndex - 1, -1));
            next?.addEventListener('click', () => goTo(activeIndex + 1, 1));
            gotoButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const targetIndex = Number(button.dataset.frameGoto);
                    if (!Number.isInteger(targetIndex) || targetIndex < 0 || targetIndex >= slides.length) return;
                    goTo(targetIndex, targetIndex > activeIndex ? 1 : -1);
                });
            });

            root.addEventListener('keydown', (event) => {
                if (event.altKey || event.ctrlKey || event.metaKey) return;
                if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    goTo(activeIndex + 1, 1);
                } else if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    goTo(activeIndex - 1, -1);
                }
            });

            slides.forEach((slide, index) => {
                const image = slide.querySelector('[data-frame-image]');
                if (!image) return;
                image.addEventListener('error', () => slide.classList.add('has-no-image'), { once: true });
                image.addEventListener('load', () => slide.classList.remove('has-no-image'), { once: true });
                if (index === activeIndex && image.complete && image.naturalWidth === 0) {
                    slide.classList.add('has-no-image');
                }
            });

            updateControls();
        });
    });
})();
