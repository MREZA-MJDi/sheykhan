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
        const intro = document.querySelector('[data-tiamir-intro]');
        if (!intro) return;

        const duration = 5000;
        const storageKey = 'sheykhan:tiamir-intro:seen';
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let alreadySeen = false;

        try {
            alreadySeen = window.sessionStorage.getItem(storageKey) === '1';
        } catch {
            alreadySeen = false;
        }

        if (alreadySeen || reducedMotion) {
            intro.remove();
            return;
        }

        const canvas = intro.querySelector('[data-tiamir-canvas]');
        const context = canvas ? canvas.getContext('2d', { alpha: true }) : null;
        const skipButton = intro.querySelector('[data-tiamir-skip]');
        const clock = intro.querySelector('[data-tiamir-clock]');
        const logo = intro.querySelector('[data-tiamir-logo]');
        let finished = false;
        let animationFrame = 0;
        let resizeHandler = null;
        let keyHandler = null;
        let timer = 0;
        let startedAt = performance.now();
        let width = window.innerWidth;
        let height = window.innerHeight;
        let pixelRatio = Math.min(window.devicePixelRatio || 1, 1.35);
        let particles = [];
        let lastClock = -1;

        document.body.classList.add('tiamir-intro-active');

        const initializeCanvas = () => {
            if (!canvas || !context) return;

            width = window.innerWidth;
            height = window.innerHeight;
            pixelRatio = Math.min(window.devicePixelRatio || 1, 1.35);
            canvas.width = Math.max(1, Math.floor(width * pixelRatio));
            canvas.height = Math.max(1, Math.floor(height * pixelRatio));
            context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);

            const count = Math.min(210, Math.max(90, Math.floor((width * height) / 6200)));
            particles = Array.from({ length: count }, () => {
                const angle = Math.random() * Math.PI * 2;
                const radius = 8 + Math.random() * 70;
                return {
                    angle: angle,
                    x: width / 2 + Math.cos(angle) * radius,
                    y: height / 2 + Math.sin(angle) * radius,
                    vx: Math.cos(angle) * (80 + Math.random() * width * .36),
                    vy: Math.sin(angle) * (70 + Math.random() * height * .43),
                    delay: 440 + Math.random() * 650,
                    life: 1250 + Math.random() * 1600,
                    size: .55 + Math.random() * 1.8,
                    alpha: .32 + Math.random() * .68,
                    streak: Math.random() > .76,
                };
            });
        };

        const draw = (now) => {
            if (finished) return;

            const elapsed = now - startedAt;
            if (context && canvas) {
                context.clearRect(0, 0, width, height);
                context.globalCompositeOperation = 'lighter';

                const impact = Math.max(0, Math.min(1, (elapsed - 430) / 1450));
                const centerX = width / 2;
                const centerY = height / 2;

                particles.forEach((particle) => {
                    const particleTime = elapsed - particle.delay;
                    const travel = particleTime <= 0 ? 0 : Math.min(1, particleTime / particle.life);
                    const x = particle.x + particle.vx * impact * travel;
                    const y = particle.y + particle.vy * impact * travel;
                    const alpha = particleTime <= 0
                        ? .045 * particle.alpha
                        : Math.max(0, (1 - travel) * particle.alpha * .78);

                    if (alpha <= .006) return;

                    context.fillStyle = 'rgba(218, 223, 232, ' + alpha.toFixed(3) + ')';
                    if (particle.streak && particleTime > 0) {
                        context.fillRect(x, y, particle.size * (2 + travel * 10), Math.max(.5, particle.size * .45));
                    } else {
                        context.fillRect(x, y, particle.size, particle.size);
                    }
                });

                if (elapsed > 410 && elapsed < 2050) {
                    const burstStrength = Math.max(0, 1 - ((elapsed - 410) / 1640));
                    for (let i = 0; i < 7; i += 1) {
                        if (Math.random() > burstStrength * .72) continue;
                        const bandY = Math.random() * height;
                        const bandX = Math.random() * width;
                        const bandWidth = 10 + Math.random() * width * .16;
                        context.fillStyle = 'rgba(225, 230, 238, ' + (Math.random() * .15 * burstStrength).toFixed(3) + ')';
                        context.fillRect(bandX, bandY, bandWidth, 1 + Math.random() * 2);
                    }
                }

                if (elapsed > 300 && elapsed < 4200 && Math.random() > .91) {
                    const y = Math.random() * height;
                    context.fillStyle = 'rgba(220, 225, 233, .22)';
                    context.fillRect(Math.random() * width * .18, y, width * (.25 + Math.random() * .52), 1);
                }

                context.globalCompositeOperation = 'source-over';
            }

            const remaining = Math.max(0, Math.ceil((duration - elapsed) / 1000));
            if (clock && remaining !== lastClock) {
                clock.textContent = '00:0' + remaining;
                lastClock = remaining;
            }

            animationFrame = window.requestAnimationFrame(draw);
        };

        const finish = () => {
            if (finished) return;
            finished = true;
            window.clearTimeout(timer);
            if (animationFrame) window.cancelAnimationFrame(animationFrame);
            if (resizeHandler) window.removeEventListener('resize', resizeHandler);
            if (keyHandler) document.removeEventListener('keydown', keyHandler);

            try {
                window.sessionStorage.setItem(storageKey, '1');
            } catch {
                // Storage can be disabled; the intro still completes normally.
            }

            document.body.classList.remove('tiamir-intro-active');
            intro.classList.add('is-exiting');

            const main = document.querySelector('#main-content');
            if (main && intro.contains(document.activeElement)) {
                main.setAttribute('tabindex', '-1');
                main.focus({ preventScroll: true });
            }

            window.setTimeout(() => intro.remove(), 680);
        };

        if (logo) {
            const showLogo = () => intro.classList.add('has-logo');
            logo.addEventListener('load', showLogo, { once: true });
            logo.addEventListener('error', () => intro.classList.remove('has-logo'), { once: true });
            if (logo.complete && logo.naturalWidth > 0) showLogo();
        }

        resizeHandler = initializeCanvas;
        resizeHandler();
        window.addEventListener('resize', resizeHandler, { passive: true });
        keyHandler = (event) => {
            if (event.key === 'Escape') {
                finish();
                return;
            }
            if (event.key === 'Tab') {
                event.preventDefault();
                skipButton?.focus({ preventScroll: true });
            }
        };
        document.addEventListener('keydown', keyHandler);
        skipButton?.addEventListener('click', finish);
        skipButton?.focus({ preventScroll: true });

        animationFrame = window.requestAnimationFrame(draw);
        timer = window.setTimeout(finish, duration);
    });
})();
