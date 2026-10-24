import '../exam-builder.js';
import { fa, g2j, MN } from './jalali.js';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const themeButton = document.getElementById('th');
    const searchInput = document.getElementById('gs');
    const main = document.querySelector('.workspace-content');

    const applyTheme = (value) => {
        const theme = value === 'dark' ? 'dark' : 'light';
        root.dataset.theme = theme;
        try { localStorage.setItem('theme', theme); } catch (_) {}
        if (themeButton) {
            themeButton.setAttribute('aria-pressed', String(theme === 'dark'));
            themeButton.setAttribute('aria-label', theme === 'dark' ? 'فعال‌کردن تم روشن' : 'فعال‌کردن تم تیره');
            themeButton.title = theme === 'dark' ? 'تم روشن' : 'تم تیره';
        }
    };

    let savedTheme = root.dataset.theme === 'dark' ? 'dark' : 'light';
    try {
        const stored = localStorage.getItem('theme');
        if (stored === 'dark' || stored === 'light') savedTheme = stored;
    } catch (_) {}
    applyTheme(savedTheme);
    themeButton?.addEventListener('click', () => applyTheme(root.dataset.theme === 'dark' ? 'light' : 'dark'));

    if (searchInput && main) {
        const emptySearchState = main.querySelector('[data-dashboard-search-empty]');
        const emptyStates = Array.from(main.querySelectorAll('.empty-state'));
        const normalize = (value) => String(value || '')
            .toLocaleLowerCase('fa')
            .replace(/[يى]/g, 'ی')
            .replace(/ك/g, 'ک')
            .normalize('NFKC')
            .trim();

        const collectTargets = () => {
            const candidates = Array.from(main.querySelectorAll(
                '[data-dashboard-searchable], .teacher-workspace-item, .teacher-workspace-card, tbody tr, .tl, .ls'
            ));
            return candidates.filter((element) => !candidates.some((parent) =>
                parent !== element && parent.contains(element)
            ));
        };

        searchInput.addEventListener('input', () => {
            const query = normalize(searchInput.value);
            let visibleCount = 0;

            collectTargets().forEach((element) => {
                const matches = !query || normalize(element.textContent).includes(query);
                element.hidden = !matches;
                if (matches) visibleCount += 1;
            });

            // Empty-state copy describes the unfiltered dashboard, not a search result.
            emptyStates.forEach((element) => { element.hidden = Boolean(query); });
            if (emptySearchState) emptySearchState.hidden = !query || visibleCount > 0;
        });
    }

    // Only Laravel-provided chart JSON is rendered here; no sample data is generated.
    document.querySelectorAll('[data-dashboard-chart]').forEach((chart) => {
        const bars = chart.querySelector('[data-chart-bars]');
        const emptyState = chart.querySelector('[data-chart-empty]');
        const buttons = Array.from(chart.querySelectorAll('[data-chart-range]'));
        if (!bars || buttons.length === 0) return;
        let data = {};
        try { data = JSON.parse(chart.getAttribute('data-chart') || '{}'); } catch (_) {}

        const render = (range) => {
            const selected = data[range] || { labels: [], values: [] };
            const labels = Array.isArray(selected.labels) ? selected.labels : [];
            const values = Array.isArray(selected.values) ? selected.values : [];
            const count = Math.min(labels.length, values.length);
            bars.replaceChildren();
            const hasProgressData = count > 0 && values.slice(0, count).some((value) => Number(value) > 0);
            if (!hasProgressData) {
                if (emptyState) emptyState.hidden = false;
                return;
            }
            for (let index = 0; index < count; index += 1) {
                const value = Math.max(0, Math.min(100, Number(values[index]) || 0));
                const column = document.createElement('div');
                const number = document.createElement('b');
                const fill = document.createElement('i');
                const label = document.createElement('small');
                number.textContent = fa(Math.round(value)) + '٪';
                fill.style.height = value + '%';
                label.textContent = String(labels[index] ?? '');
                column.append(number, fill, label);
                bars.appendChild(column);
            }
            if (emptyState) emptyState.hidden = hasProgressData;
        };
        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const range = button.dataset.chartRange || 'week';
                buttons.forEach((item) => {
                    const active = item === button;
                    item.classList.toggle('on', active);
                    item.setAttribute('aria-pressed', String(active));
                });
                render(range);
            });
        });
        render('week');
    });

    // Optional Jalali text labels are derived from ISO dates; form values remain unchanged.
    document.querySelectorAll('[data-jalali-date]').forEach((element) => {
        const raw = element.getAttribute('data-jalali-date');
        const match = raw && raw.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!match) return;
        const converted = g2j(Number(match[1]), Number(match[2]), Number(match[3]));
        element.textContent = fa(converted[0]) + ' ' + MN[converted[1] - 1] + ' ' + fa(converted[2]);
    });

    document.addEventListener('click', (event) => {
        const target = event.target.closest('[data-confirm]');
        if (!target) return;
        if (!window.confirm(target.dataset.confirm || 'از انجام این عملیات مطمئن هستید؟')) {
            event.preventDefault();
            event.stopPropagation();
        }
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.dataset.confirmSubmit !== 'true') return;
        if (!window.confirm(form.dataset.confirm || 'از انجام این عملیات مطمئن هستید؟')) event.preventDefault();
    });

    document.querySelectorAll('[data-teacher-preview-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const kind = input.dataset.teacherPreviewInput;
            const file = input.files?.[0];
            const preview = document.querySelector('[data-teacher-preview-container="' + kind + '"]');
            const feedback = document.querySelector('[data-teacher-preview-feedback="' + kind + '"]');
            if (!file || !preview) return;

            const maxSizeMb = Number(input.dataset.maxSizeMb || 0);
            if (!file.type.startsWith('image/')) {
                if (feedback) feedback.textContent = 'لطفاً یک فایل تصویری معتبر انتخاب کنید.';
                input.value = '';
                return;
            }
            if (maxSizeMb > 0 && file.size > maxSizeMb * 1024 * 1024) {
                if (feedback) feedback.textContent = 'حجم فایل نباید بیشتر از ' + fa(maxSizeMb) + ' مگابایت باشد.';
                input.value = '';
                return;
            }

            const url = URL.createObjectURL(file);
            const image = document.createElement('img');
            image.src = url;
            image.alt = kind === 'avatar' ? 'پیش‌نمایش آواتار انتخاب‌شده' : 'پیش‌نمایش کاور انتخاب‌شده';
            image.dataset.teacherPreviewImage = kind;
            image.decoding = 'async';
            image.onload = () => URL.revokeObjectURL(url);
            image.onerror = () => {
                URL.revokeObjectURL(url);
                image.remove();
                if (feedback) feedback.textContent = 'نمایش این تصویر ممکن نشد؛ فایل دیگری انتخاب کنید.';
            };

            if (kind === 'cover') {
                preview.classList.remove('no-cover');
                preview.classList.add('has-cover');
                preview.querySelectorAll('img').forEach((existing) => existing.remove());
                const label = preview.querySelector('.teacher-cover-preview-label');
                if (label) preview.insertBefore(image, label);
                else preview.prepend(image);
            } else {
                preview.replaceChildren(image);
            }

            if (feedback) {
                feedback.textContent = file.name + ' · ' + fa(Math.ceil(file.size / 1024)) + ' کیلوبایت · پیش‌نمایش آماده است.';
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-overlay-close]')) document.body.classList.remove('op1');
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') document.body.classList.remove('op1');
    });
});