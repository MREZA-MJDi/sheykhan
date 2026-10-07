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
