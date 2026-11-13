import './shared/persian-calendar.js';
import Alpine from 'alpinejs';

window.Alpine = window.Alpine || Alpine;

if (!window.__sheykhanAlpineBooted) {
    Alpine.start();
    window.__sheykhanAlpineBooted = true;
}

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
        // A failed custom/CDN hero must not leave a broken-image glyph over the hero.
        document.querySelectorAll('[data-home-hero-image]').forEach((image) => {
            const fallback = image.dataset.fallbackSrc;
            const desktopSource = image.dataset.desktopSrc || image.getAttribute('src') || '';
            const mobileSource = image.closest('picture')?.querySelector('[data-home-hero-mobile-source]');
            const useFallback = () => {
                // On mobile, first retry the desktop image if only the mobile source failed.
                if (
                    mobileSource?.hasAttribute('srcset')
                    && window.matchMedia('(max-width: 640px)').matches
                    && image.dataset.mobileFallbackAttempted !== 'true'
                ) {
                    mobileSource.removeAttribute('srcset');
                    image.dataset.mobileFallbackAttempted = 'true';
                    image.src = desktopSource;
                    return;
                }
                if (!fallback || image.dataset.fallbackApplied === 'true') return;
                image.dataset.fallbackApplied = 'true';
                mobileSource?.removeAttribute('srcset');
                image.src = fallback;
            };
            image.addEventListener('error', useFallback);
            if (image.complete && image.naturalWidth === 0) useFallback();
        });

        const root = document.querySelector('#main-content');

        if (!root) return;

        // Free micro-challenge: local answer feedback, no form submit or page reload.
        const challenge = root.querySelector('[data-free-challenge]');
        if (challenge) {
            const questions = [
                {
                    prompt: 'عدد بعدی در این الگو کدام است؟',
                    sequence: '۲  ·  ۶  ·  ۱۲  ·  ۲۰  ·  ؟',
                    answer: '۳۰',
                    options: ['۲۸', '۳۰', '۳۲'],
                    explanation: 'در هر مرحله ۲، ۴، ۶ و سپس ۸ واحد اضافه می‌شود.'
                },
                {
                    prompt: 'کدام شکل باید جای علامت سؤال قرار بگیرد؟',
                    sequence: '●  ▲  ●  ▲  ؟',
                    answer: '● دایره',
                    options: ['■ مربع', '● دایره', '▲ مثلث'],
                    explanation: 'الگو یکی‌درمیان دایره و مثلث است؛ پس نوبت دایره می‌رسد.'
                },
                {
                    prompt: 'اگر رنگ‌ها یکی‌درمیان تکرار شوند، رنگ بعدی چیست؟',
                    sequence: 'آبی  ·  سبز  ·  آبی  ·  سبز  ·  ؟',
                    answer: 'آبی',
                    options: ['قرمز', 'سبز', 'آبی'],
                    explanation: 'توالی آبی و سبز به‌صورت یکی‌درمیان تکرار می‌شود.'
                }
            ];
            const prompt = challenge.querySelector('[data-challenge-prompt]');
            const sequence = challenge.querySelector('[data-challenge-sequence]');
            const options = challenge.querySelector('[data-challenge-options]');
            const feedback = challenge.querySelector('[data-challenge-feedback]');
            const nextButton = challenge.querySelector('[data-challenge-next]');
            const counter = challenge.querySelector('[data-challenge-index]');
            let questionIndex = Math.floor(Math.random() * questions.length);
            let answered = false;

            const renderQuestion = () => {
                const question = questions[questionIndex];
                answered = false;
                if (prompt) prompt.textContent = question.prompt;
                if (sequence) sequence.textContent = question.sequence;
                if (counter) counter.textContent = `${numberFormat.format(questionIndex + 1)} از ${numberFormat.format(questions.length)}`;
                if (feedback) {
                    feedback.textContent = 'یکی از گزینه‌ها را انتخاب کن.';
                    feedback.classList.remove('is-correct', 'is-wrong');
                }
                if (nextButton) {
                    nextButton.hidden = true;
                    nextButton.textContent = questionIndex === questions.length - 1 ? 'شروع دوباره' : 'چالش بعدی ←';
                }
                if (!options) return;
                options.replaceChildren();
                question.options.forEach((label) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'home-free-challenge__option';
                    button.textContent = label;
                    button.addEventListener('click', () => {
                        if (answered) return;
                        answered = true;
                        const correct = label === question.answer;
                        options.querySelectorAll('button').forEach((option) => {
                            option.disabled = true;
                            if (option.textContent === question.answer) option.classList.add('is-correct');
                            else if (option === button && !correct) option.classList.add('is-wrong');
                        });
                        if (feedback) {
                            feedback.textContent = correct
                                ? `آفرین! پاسخ درست است. ${question.explanation}`
                                : `این بار نه؛ پاسخ درست «${question.answer}» است. ${question.explanation}`;
                            feedback.classList.toggle('is-correct', correct);
                            feedback.classList.toggle('is-wrong', !correct);
                        }
                        if (nextButton) nextButton.hidden = false;
                    });
                    options.append(button);
                });
            };

            nextButton?.addEventListener('click', () => {
                questionIndex = (questionIndex + 1) % questions.length;
                renderQuestion();
            });
            renderQuestion();
        }

        // Lightweight homepage hero carousel: no plugin, no page-wide polling.
        const hero = root.querySelector('[data-home-hero]');
        if (hero) {
            const slides = Array.from(hero.querySelectorAll('[data-home-hero-slide]'));

            if (slides.length > 1) {
                const dots = Array.from(hero.querySelectorAll('[data-home-hero-dot]'));
                const previousButton = hero.querySelector('[data-home-hero-prev]');
                const nextButton = hero.querySelector('[data-home-hero-next]');
                const count = hero.querySelector('[data-home-hero-count]');
                const reducedMotion = typeof window.matchMedia === 'function'
                    ? window.matchMedia('(prefers-reduced-motion: reduce)')
                    : { matches: false };
                const numberFormat = new Intl.NumberFormat('fa-IR');
                let activeIndex = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
                let rotateTimer = null;

                const stopRotation = () => {
                    if (rotateTimer !== null) {
                        window.clearInterval(rotateTimer);
                        rotateTimer = null;
                    }
                };

                const preloadSlide = (index) => {
                    const image = slides[index]?.querySelector('img');
                    if (!image || image.dataset.preloadRequested === 'true') return;

                    image.dataset.preloadRequested = 'true';
                    image.loading = 'eager';
                    image.fetchPriority = 'low';

                    // Promote only the next slide, rather than eager-loading every banner.
                };

                const activateSlide = (requestedIndex) => {
                    activeIndex = (requestedIndex + slides.length) % slides.length;

                    slides.forEach((slide, index) => {
                        const active = index === activeIndex;
                        slide.classList.toggle('is-active', active);
                        slide.setAttribute('aria-hidden', active ? 'false' : 'true');
                    });

                    dots.forEach((dot, index) => {
                        const active = index === activeIndex;
                        dot.classList.toggle('is-active', active);
                        dot.setAttribute('aria-pressed', active ? 'true' : 'false');
                    });

                    if (count) {
                        count.textContent = `${numberFormat.format(activeIndex + 1)} / ${numberFormat.format(slides.length)}`;
                    }

                    preloadSlide((activeIndex + 1) % slides.length);
                };

                const startRotation = () => {
                    stopRotation();
                    if (reducedMotion.matches || document.hidden) return;

                    rotateTimer = window.setInterval(() => {
                        activateSlide(activeIndex + 1);
                    }, 6500);
                };

                previousButton?.addEventListener('click', () => {
                    activateSlide(activeIndex - 1);
                    startRotation();
                });

                nextButton?.addEventListener('click', () => {
                    activateSlide(activeIndex + 1);
                    startRotation();
                });

                dots.forEach((dot) => {
                    dot.addEventListener('click', () => {
                        activateSlide(Number(dot.dataset.index) || 0);
                        startRotation();
                    });
                });

                hero.addEventListener('mouseenter', stopRotation);
                hero.addEventListener('mouseleave', startRotation);
                hero.addEventListener('focusin', stopRotation);
                hero.addEventListener('focusout', (event) => {
                    if (!hero.contains(event.relatedTarget)) startRotation();
                });
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) stopRotation();
                    else startRotation();
                });

                if (typeof reducedMotion.addEventListener === 'function') {
                    reducedMotion.addEventListener('change', startRotation);
                }

                activateSlide(activeIndex);
                startRotation();
            }
        }

        root.querySelectorAll('[data-order-proof-form]').forEach((form) => {
            const input = form.querySelector('[data-proof-input]');
            const feedback = form.querySelector('[data-proof-file-name]');
            const button = form.querySelector('[data-proof-submit]');
            if (!input) return;

            input.addEventListener('change', () => {
                const file = input.files?.[0];
                if (!file) return;

                if (file.size > 12 * 1024 * 1024) {
                    input.value = '';
                    if (feedback) {
                        feedback.setAttribute('role', 'alert');
                        feedback.textContent = 'حجم فایل بیشتر از ۱۲ مگابایت است؛ یک فایل کوچک‌تر انتخاب کن.';
                        feedback.classList.add('text-rose-700');
                    }
                    return;
                }

                if (feedback) {
                    feedback.setAttribute('role', 'status');
                    feedback.classList.remove('text-rose-700');
                    const size = file.size >= 1024 * 1024
                        ? `${(file.size / (1024 * 1024)).toFixed(1)} مگابایت`
                        : `${Math.max(1, Math.round(file.size / 1024))} کیلوبایت`;
                    feedback.textContent = `فایل انتخاب‌شده: ${file.name} · ${size}`;
                }
            });

            form.addEventListener('submit', () => {
                if (!input.files?.length || !button) return;
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                button.textContent = 'در حال ارسال رسید…';
            });
        });

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