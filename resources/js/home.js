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
            const cropProperties = [
                ['--hero-crop-x', image.dataset.cropX, 50],
                ['--hero-crop-y', image.dataset.cropY, 46],
                ['--hero-mobile-crop-x', image.dataset.mobileCropX, 54],
                ['--hero-mobile-crop-y', image.dataset.mobileCropY, 50],
            ];
            cropProperties.forEach(([property, value, fallbackValue]) => {
                const parsed = Number(value);
                const normalized = Number.isFinite(parsed) ? Math.min(100, Math.max(0, parsed)) : fallbackValue;
                image.style.setProperty(property, `${normalized}%`);
            });

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

        const puzzle = root.querySelector('[data-hero-challenge]');
        if (puzzle) {
            const form = puzzle.querySelector('[data-hero-challenge-form]');
            const endpoint = puzzle.dataset.answerEndpoint || '/hero-challenges';
            const feedback = puzzle.querySelector('[data-challenge-feedback]');
            const title = puzzle.querySelector('[data-challenge-title]');
            const prompt = puzzle.querySelector('[data-challenge-prompt]');
            const position = puzzle.querySelector('[data-challenge-position]');
            const kind = puzzle.querySelector('[data-challenge-kind]');
            const sequence = puzzle.querySelector('[data-challenge-sequence]');
            const numberFormat = new Intl.NumberFormat('fa-IR');

            const showFeedback = (correct, message, detail = '', nextId = null) => {
                if (!feedback) return;
                feedback.replaceChildren();
                feedback.hidden = false;
                feedback.classList.toggle('is-correct', correct);
                feedback.classList.toggle('is-incorrect', !correct);

                const heading = document.createElement('strong');
                heading.textContent = message;
                feedback.append(heading);

                if (detail) {
                    const paragraph = document.createElement('p');
                    paragraph.textContent = detail;
                    feedback.append(paragraph);
                }

                if (correct && nextId) {
                    const nextButton = document.createElement('button');
                    nextButton.type = 'button';
                    nextButton.className = 'shx-public-puzzle__next';
                    nextButton.dataset.nextChallenge = String(nextId);
                    nextButton.textContent = puzzle.dataset.nextLabel || 'سؤال بعدی ←';
                    feedback.append(nextButton);
                } else if (correct) {
                    const paragraph = document.createElement('p');
                    paragraph.textContent = puzzle.dataset.completeLabel || '';
                    feedback.append(paragraph);
                    const coursesLink = document.createElement('a');
                    coursesLink.className = 'shx-public-puzzle__next';
                    coursesLink.href = puzzle.dataset.coursesUrl || '/courses';
                    coursesLink.textContent = puzzle.dataset.coursesLabel || '';
                    feedback.append(coursesLink);
                }
            };

            const requestJson = async (url, options = {}) => {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    ...options,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(options.headers || {}),
                    },
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const validationMessage = Object.values(payload.errors || {}).flat()[0];
                    throw new Error(validationMessage || payload.message || puzzle.dataset.networkError || 'Request failed');
                }
                return payload;
            };

            const renderOptions = (options) => {
                if (!form) return;
                const csrfInput = form.querySelector('input[name="_token"]');
                form.replaceChildren();
                if (csrfInput) form.append(csrfInput);
                options.forEach((option, index) => {
                    const button = document.createElement('button');
                    button.type = 'submit';
                    button.name = 'option_id';
                    button.value = String(option.id);
                    button.textContent = option.content;
                    button.setAttribute('aria-label', `گزینه ${numberFormat.format(index + 1)}: ${option.content}`);
                    form.append(button);
                });
            };

            const loadChallenge = async (challengeId) => {
                const challenge = await requestJson(`${endpoint}/${encodeURIComponent(challengeId)}`);
                if (title) title.textContent = challenge.title || '';
                if (prompt) prompt.textContent = challenge.prompt || '';
                if (position) {
                    position.textContent = `سؤال ${numberFormat.format(challenge.position)} از ${numberFormat.format(challenge.total)}`;
                }
                if (kind) kind.textContent = Array.isArray(challenge.sequence) && challenge.sequence.length
                    ? 'الگوی بعدی چیست؟'
                    : 'چالش منطق';

                if (sequence) {
                    sequence.replaceChildren();
                    (challenge.sequence || []).forEach((item) => {
                        const value = document.createElement('span');
                        value.textContent = item;
                        sequence.append(value);
                    });
                    const questionMark = document.createElement('span');
                    questionMark.className = 'is-question';
                    questionMark.textContent = '؟';
                    sequence.append(questionMark);
                    sequence.hidden = !challenge.sequence?.length;
                }

                if (feedback) {
                    feedback.replaceChildren();
                    feedback.hidden = true;
                    feedback.classList.remove('is-correct', 'is-incorrect');
                }

                if (form) {
                    form.action = `${endpoint}/${challenge.id}/answer`;
                    form.hidden = false;
                    renderOptions(challenge.options || []);
                }
            };

            form?.addEventListener('submit', async (event) => {
                const submitter = event.submitter || document.activeElement;
                const optionId = submitter?.value;
                if (!optionId) return;

                event.preventDefault();
                const buttons = Array.from(form.querySelectorAll('button[type="submit"]'));
                buttons.forEach((button) => {
                    button.disabled = true;
                    button.setAttribute('aria-busy', 'true');
                });

                const payload = new FormData(form);
                payload.set('option_id', optionId);

                try {
                    const result = await requestJson(form.action, { method: 'POST', body: payload });
                    if (result.correct) {
                        submitter.classList.add('is-correct');
                        form.hidden = true;
                        showFeedback(true, puzzle.dataset.correctLabel || 'درست است!', result.explanation || '', result.next);
                    } else {
                        submitter.classList.add('is-incorrect');
                        submitter.disabled = true;
                        submitter.removeAttribute('aria-busy');
                        buttons.filter((button) => button !== submitter).forEach((button) => {
                            button.disabled = false;
                            button.removeAttribute('aria-busy');
                        });
                        showFeedback(false, puzzle.dataset.incorrectLabel || 'این گزینه درست نبود؛ دوباره امتحان کن.', puzzle.dataset.incorrectHint || '');
                    }
                } catch (error) {
                    buttons.forEach((button) => {
                        button.disabled = false;
                        button.removeAttribute('aria-busy');
                    });
                    showFeedback(false, error.message || puzzle.dataset.networkError || 'ارتباط با سرور برقرار نشد.');
                }
            });

            puzzle.addEventListener('click', async (event) => {
                const nextButton = event.target.closest('[data-next-challenge]');
                if (!nextButton) return;
                event.preventDefault();
                nextButton.disabled = true;
                try {
                    await loadChallenge(nextButton.dataset.nextChallenge);
                } catch (error) {
                    nextButton.disabled = false;
                    showFeedback(false, error.message || puzzle.dataset.networkError || 'ارتباط با سرور برقرار نشد.');
                }
            });
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