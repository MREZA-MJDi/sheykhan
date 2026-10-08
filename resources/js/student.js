import { bootPanel } from './panel-base.js';

document.addEventListener('DOMContentLoaded', () => {
    bootPanel();
    document.documentElement.classList.add('student-ready');
});


const announce = (type, title, message) => {
    const existing = document.querySelector('.student-alert');
    const alert = document.createElement('div');
    alert.className = `student-alert student-alert-${type === 'success' ? 'success' : 'danger'}`;
    alert.setAttribute('role', type === 'success' ? 'status' : 'alert');
    alert.setAttribute('aria-live', type === 'success' ? 'polite' : 'assertive');
    alert.innerHTML = `
        <span class="student-alert-icon" aria-hidden="true">${type === 'success' ? '✓' : '!'}</span>
        <div><strong>${title}</strong><span>${message}</span></div>
    `;
    existing?.remove();
    document.querySelector('.role-main')?.prepend(alert);
};

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-student-progress-form]');
    if (!form) return;

    event.preventDefault();

    const button = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);

    if (button) {
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
    }

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: formData,
            credentials: 'same-origin',
        });

        const payload = await response.json();

        if (!response.ok || !payload.ok) {
            throw new Error(payload.message || 'ذخیره پیشرفت انجام نشد.');
        }

        const progressInput = form.querySelector('[data-progress-input]');
        const secondsInput = form.querySelector('[data-seconds-input]');

        if (progressInput) progressInput.value = payload.progress_percent ?? progressInput.value;
        if (secondsInput) secondsInput.value = payload.seconds_watched ?? secondsInput.value;

        announce('success', 'ذخیره شد', 'پیشرفت این درس با موفقیت ثبت شد.');
    } catch (error) {
        announce('danger', 'ذخیره انجام نشد', error?.message || 'لطفاً دوباره تلاش کنید.');
    } finally {
        if (button) {
            button.disabled = false;
            button.removeAttribute('aria-busy');
        }
    }
});


/* Student exam interaction layer: local recovery + navigation + server-authoritative timer. */
document.addEventListener('DOMContentLoaded', () => {
    const exam = document.querySelector('[data-student-exam]');
    if (!exam) return;

    const form = exam.querySelector('[data-exam-form]');
    const questions = [...exam.querySelectorAll('[data-exam-question]')];
    const dots = [...exam.querySelectorAll('[data-answer-jump]')];
    const timer = exam.querySelector('[data-exam-timer]');
    const timerValue = exam.querySelector('[data-timer-value]');
    const saveState = exam.querySelector('[data-save-state]');
    const submitButton = exam.querySelector('[data-submit-exam]');
    const storageKey = exam.dataset.storageKey;
    const deadline = Number(exam.dataset.deadline || 0) * 1000;
    const hasTimer = exam.dataset.hasTimer === '1';
    let current = 0;
    let flagged = new Set();
    let submitted = false;
    let timerId = null;

    const fa = (value) => String(value).replace(/\d/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[Number(d)]);

    const inputsForQuestion = (question) =>
        [...question.querySelectorAll('[data-answer-input]')];

    const hasAnswer = (question) => {
        const inputs = inputsForQuestion(question);

        if (inputs.some((input) => input.type === 'radio' && input.checked)) return true;
        if (inputs.some((input) => input.type === 'checkbox' && input.checked)) return true;

        const text = inputs.find((input) => input.tagName === 'TEXTAREA' || input.type === 'text');
        return Boolean(text && text.value.trim());
    };

    const readState = () => {
        if (!storageKey) return {};
        try {
            return JSON.parse(sessionStorage.getItem(storageKey) || '{}');
        } catch {
            return {};
        }
    };

    const writeState = () => {
        if (!storageKey || submitted) return;

        const state = {
            flags: [...flagged],
            answers: {},
            savedAt: Date.now(),
        };

        questions.forEach((question) => {
            const id = question.querySelector('[data-answer-input]')?.name;
            if (!id) return;

            const inputs = inputsForQuestion(question);
            const checked = inputs.filter((input) => input.checked).map((input) => input.value);
            const text = inputs.find((input) => input.tagName === 'TEXTAREA');

            state.answers[id] = text
                ? text.value
                : checked;
        });

        try {
            sessionStorage.setItem(storageKey, JSON.stringify(state));
            saveState?.classList.remove('is-dirty');
            const label = saveState?.querySelector('span');
            if (label) label.textContent = 'پاسخ‌ها روی دستگاه ذخیره شدند';
        } catch {
            // Storage failure must never block the exam.
        }
    };

    const restoreState = () => {
        const state = readState();
        if (!state.answers && !state.flags) return;

        questions.forEach((question) => {
            const input = question.querySelector('[data-answer-input]');
            if (!input || !state.answers) return;

            const answer = state.answers[input.name];
            if (answer === undefined) return;

            const inputs = inputsForQuestion(question);
            if (Array.isArray(answer)) {
                inputs.forEach((item) => {
                    item.checked = answer.includes(item.value);
                });
            } else if (inputs[0]?.tagName === 'TEXTAREA') {
                inputs[0].value = answer ?? '';
            }
        });

        flagged = new Set((state.flags || []).map(String));
    };

    const markDirty = () => {
        saveState?.classList.add('is-dirty');
        const label = saveState?.querySelector('span');
        if (label) label.textContent = 'تغییرات ذخیره می‌شود…';
        updateUi();
        window.clearTimeout(exam._saveTimer);
        exam._saveTimer = window.setTimeout(writeState, 450);
    };

    const updateUi = () => {
        let answered = 0;

        questions.forEach((question, index) => {
            const isAnswered = hasAnswer(question);
            if (isAnswered) answered += 1;

            question.classList.toggle('is-answered', isAnswered);
            question.classList.toggle('is-current', index === current);

            const dot = dots[index];
            if (dot) {
                dot.classList.toggle('is-answered', isAnswered);
                dot.classList.toggle('is-current', index === current);
                dot.classList.toggle('is-flagged', flagged.has(String(question.dataset.questionNumber)));
            }

            const flagButton = question.querySelector('[data-flag-question]');
            const isFlagged = flagged.has(String(question.dataset.questionNumber));
            flagButton?.classList.toggle('is-flagged', isFlagged);
            flagButton?.setAttribute('aria-pressed', isFlagged ? 'true' : 'false');
            if (flagButton) {
                flagButton.textContent = isFlagged ? '★ نشان‌گذاری‌شده' : '☆ نشان‌گذاری';
            }
        });

        exam.querySelectorAll('[data-answered-count]').forEach((node) => {
            node.textContent = fa(answered);
        });
        const side = exam.querySelector('[data-answered-count-side]');
        if (side) side.textContent = fa(answered);

        const flagCount = exam.querySelector('[data-flagged-count]');
        if (flagCount) flagCount.textContent = fa(flagged.size);

        const mobileCurrent = exam.querySelector('[data-mobile-current]');
        if (mobileCurrent) mobileCurrent.textContent = fa(current + 1);
    };

    const goTo = (index, behavior = 'smooth') => {
        if (!questions.length) return;
        current = Math.max(0, Math.min(questions.length - 1, index));
        questions[current].scrollIntoView({ behavior, block: 'start' });
        updateUi();
    };

    const formatTime = (seconds) => {
        const safe = Math.max(0, seconds);
        const hours = Math.floor(safe / 3600);
        const minutes = Math.floor((safe % 3600) / 60);
        const secs = safe % 60;

        if (hours > 0) {
            return `${fa(String(hours).padStart(2, '0'))}:${fa(String(minutes).padStart(2, '0'))}:${fa(String(secs).padStart(2, '0'))}`;
        }

        return `${fa(String(minutes).padStart(2, '0'))}:${fa(String(secs).padStart(2, '0'))}`;
    };

    const tick = () => {
        if (!hasTimer || !timerValue || !deadline) return;

        const remaining = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
        timerValue.textContent = formatTime(remaining);

        timer?.classList.toggle('is-warning', remaining > 0 && remaining <= 300 && remaining > 60);
        timer?.classList.toggle('is-danger', remaining > 0 && remaining <= 60);

        if (remaining <= 0) {
            window.clearInterval(timerId);
            if (submitted || !form) return;

            submitted = true;
            try {
                sessionStorage.removeItem(storageKey);
            } catch {}

            submitButton?.setAttribute('disabled', 'disabled');
            submitButton?.classList.add('is-disabled');
            saveState?.querySelector('span')?.replaceChildren(document.createTextNode('زمان تمام شد؛ برگه در حال تحویل است…'));

            // The server remains the source of truth and will reject late submissions.
            form.submit();
        }
    };

    restoreState();
    updateUi();

    inputsForQuestion(questions[0] || document.createElement('div'));

    form?.addEventListener('input', markDirty);
    form?.addEventListener('change', markDirty);

    exam.querySelectorAll('[data-flag-question]').forEach((button) => {
        button.addEventListener('click', () => {
            const number = button.closest('[data-exam-question]')?.dataset.questionNumber;
            if (!number) return;

            const key = String(number);
            if (flagged.has(key)) {
                flagged.delete(key);
                button.classList.remove('is-flagged');
                button.setAttribute('aria-pressed', 'false');
                button.textContent = '☆ نشان‌گذاری';
            } else {
                flagged.add(key);
                button.classList.add('is-flagged');
                button.setAttribute('aria-pressed', 'true');
                button.textContent = '★ نشان‌گذاری‌شده';
            }
            markDirty();
        });
    });

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => goTo(index));
    });

    exam.querySelectorAll('[data-next-question]').forEach((button, index) => {
        button.addEventListener('click', () => goTo(Math.min(index + 1, questions.length - 1)));
    });

    exam.querySelector('[data-mobile-question-prev]')?.addEventListener('click', () => goTo(current - 1));
    exam.querySelector('[data-mobile-question-next]')?.addEventListener('click', () => goTo(current + 1));

    form?.addEventListener('submit', (event) => {
        if (submitted) return;

        const unanswered = questions.filter((question) => !hasAnswer(question)).length;
        if (unanswered > 0) {
            const message = `${fa(unanswered)} سؤال هنوز بدون پاسخ است. مطمئنی می‌خواهی برگه را تحویل بدهی؟`;
            if (!window.confirm(message)) {
                event.preventDefault();
                return;
            }
        } else if (!window.confirm('همه‌چیز آماده است؟ بعد از تحویل دیگر نمی‌توانی پاسخ‌ها را تغییر بدهی.')) {
            event.preventDefault();
            return;
        }

        submitted = true;
        try {
            sessionStorage.removeItem(storageKey);
        } catch {}

        submitButton?.setAttribute('disabled', 'disabled');
        submitButton?.setAttribute('aria-busy', 'true');
        submitButton?.querySelector('span') && (submitButton.querySelector('span').textContent = 'در حال تحویل…');
    });

    const visibilityHandler = () => {
        if (!document.hidden) {
            tick();
            updateUi();
        }
    };
    document.addEventListener('visibilitychange', visibilityHandler);

    if (hasTimer && deadline) {
        tick();
        timerId = window.setInterval(tick, 1000);
    }

    window.setTimeout(() => {
        if (questions[0]) goTo(0, 'auto');
    }, 0);
});
