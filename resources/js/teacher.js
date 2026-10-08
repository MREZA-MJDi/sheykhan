import { bootPanel } from './panel-base.js';

document.addEventListener('DOMContentLoaded', () => {
    bootPanel();

    document.querySelectorAll('[data-copy-schedule-link]').forEach((button) => {
        button.addEventListener('click', async () => {
            const value = button.dataset.copyScheduleLink || '';
            if (!value) return;

            try {
                await navigator.clipboard.writeText(value);
                const previous = button.textContent;
                button.textContent = 'کپی شد ✓';
                window.setTimeout(() => {
                    button.textContent = previous;
                }, 1600);
            } catch {
                window.prompt('لینک جلسه را کپی کنید:', value);
            }
        });
    });

    const dashboard = document.querySelector('[data-teacher-dashboard]');

    if (dashboard) {
        const buttons = dashboard.querySelectorAll('[data-teacher-range]');
        const bars = dashboard.querySelectorAll('[data-teacher-bars] .teacher-bar');

        let datasets = {};

        try {
            datasets = JSON.parse(dashboard.dataset.chart || '{}');
        } catch {
            datasets = {};
        }

        const applyChart = (range) => {
            const dataset = datasets[range] || {};
            const values = Array.isArray(dataset.values) ? dataset.values : [];
            const labels = Array.isArray(dataset.labels) ? dataset.labels : [];

            buttons.forEach((button) => {
                button.classList.toggle('active', button.dataset.teacherRange === range);
                button.setAttribute('aria-pressed', button.dataset.teacherRange === range ? 'true' : 'false');
            });

            bars.forEach((bar, index) => {
                const value = Math.max(0, Math.min(100, Number(values[index]) || 0));
                bar.style.height = value + '%';

                const label = bar.querySelector('small');

                if (label) {
                    label.textContent = labels[index] || '';
                }
            });
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                applyChart(button.dataset.teacherRange || 'week');
            });
        });

        applyChart(buttons[0]?.dataset.teacherRange || 'week');
    }



    document.querySelectorAll('[data-course-classroom-select]').forEach((classroomSelect) => {
        const courseSelect = document.querySelector(classroomSelect.dataset.courseSelect);
        if (!courseSelect) return;

        const syncClassrooms = () => {
            const courseId = courseSelect.value;
            Array.from(classroomSelect.options).forEach((option) => {
                const matches = !option.dataset.courseId || option.dataset.courseId === courseId;
                option.hidden = !matches;
                if (!matches && option.selected) classroomSelect.value = '';
            });
        };

        courseSelect.addEventListener('change', syncClassrooms);
        syncClassrooms();
    });

    const teacherJalaliFields = document.querySelectorAll('[data-teacher-jalali]');

    const teacherNormalizeDigits = (value) => String(value ?? '')
        .replace(/[۰-۹]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
        .replace(/[٠-٩]/g, (d) => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));

    const teacherGregorianToJalali = (value) => {
        if (!value) return '';
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return '';

        return new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
            timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        }).format(date);
    };

    const teacherJalaliToGregorian = (value) => {
        const normalized = teacherNormalizeDigits(value).replace(/-/g, '/').trim();
        const match = normalized.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
        if (!match) return null;

        const jy = Number(match[1]);
        const jm = Number(match[2]);
        const jd = Number(match[3]);
        const formatter = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
            timeZone: 'UTC',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        });

        const start = new Date(Date.UTC(jy + 620, 0, 1, 12));
        for (let offset = 0; offset <= 800; offset++) {
            const candidate = new Date(start.getTime() + offset * 86400000);
            const parts = Object.fromEntries(
                formatter.formatToParts(candidate).map((item) => [item.type, item.value])
            );

            if (Number(teacherNormalizeDigits(parts.year)) === jy
                && Number(teacherNormalizeDigits(parts.month)) === jm
                && Number(teacherNormalizeDigits(parts.day)) === jd) {
                return [
                    candidate.getUTCFullYear(),
                    String(candidate.getUTCMonth() + 1).padStart(2, '0'),
                    String(candidate.getUTCDate()).padStart(2, '0'),
                ];
            }
        }

        return null;
    };

    teacherJalaliFields.forEach((field) => {
        const visibleDate = field.querySelector('[data-jalali-visible-date]');
        const visibleTime = field.querySelector('[data-jalali-visible-time]');
        const target = field.querySelector('[data-jalali-target]');
        const dateOnly = field.dataset.jalaliDateOnly === '1';

        if (!visibleDate || !target) return;

        const hydrate = () => {
            const raw = target.value;
            if (!raw) return;

            const match = String(raw).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
            if (!match) return;

            const jalali = teacherGregorianToJalali(
                match[4] ? `${match[1]}-${match[2]}-${match[3]}T${match[4]}:${match[5]}` : `${match[1]}-${match[2]}-${match[3]}T12:00`
            );

            if (jalali) visibleDate.value = jalali;
            if (visibleTime && match[4]) visibleTime.value = `${match[4]}:${match[5]}`;
        };

        const sync = () => {
            const gregorian = teacherJalaliToGregorian(visibleDate.value);
            if (!gregorian) {
                target.value = '';
                return false;
            }

            if (dateOnly) {
                target.value = gregorian.join('-');
                return true;
            }

            if (!visibleTime?.value) {
                target.value = '';
                return false;
            }

            target.value = `${gregorian.join('-')}T${visibleTime.value}`;
            return true;
        };

        hydrate();
        visibleDate.addEventListener('change', sync);
        visibleDate.addEventListener('blur', sync);
        visibleTime?.addEventListener('change', sync);

        field.closest('form')?.addEventListener('submit', (event) => {
            if (!sync()) {
                event.preventDefault();
                visibleDate.focus();
            }
        });
    });

    const liveForm = document.querySelector('[data-live-class-form]');

    if (liveForm) {
        const dateTrigger = liveForm.querySelector('[data-jalali-trigger]');
        const picker = liveForm.querySelector('[data-jalali-picker]');
        const days = liveForm.querySelector('[data-jalali-days]');
        const monthLabel = liveForm.querySelector('[data-jalali-month-label]');
        const hiddenDate = liveForm.querySelector('[data-jalali-date]');
        const hiddenScheduledAt = liveForm.querySelector('[data-scheduled-at]');
        const timeInput = liveForm.querySelector('[data-scheduled-time]');
        const monthNames = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
        const digits = (value) => String(value).replace(/[0-9]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[Number(d)]);
        const pad = (value) => String(value).padStart(2, '0');
        const isLeapGregorian = (year) => (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;

        const jalaliToGregorian = (jy, jm, jd) => {
            let y = jy + 1595;
            let daysValue = -355668 + (365 * y) + Math.floor(y / 33) * 8 + Math.floor(((y % 33) + 3) / 4) + jd;
            daysValue += jm < 7 ? (jm - 1) * 31 : ((jm - 7) * 30) + 186;

            let gy = 400 * Math.floor(daysValue / 146097);
            daysValue %= 146097;

            if (daysValue > 36524) {
                gy += 100 * Math.floor(--daysValue / 36524);
                daysValue %= 36524;
                if (daysValue >= 365) daysValue++;
            }

            gy += 4 * Math.floor(daysValue / 1461);
            daysValue %= 1461;

            if (daysValue > 365) {
                gy += Math.floor((daysValue - 1) / 365);
                daysValue = (daysValue - 1) % 365;
            }

            let gd = daysValue + 1;
            const monthDays = [31, isLeapGregorian(gy) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            let gm = 1;

            while (gm <= 12 && gd > monthDays[gm - 1]) {
                gd -= monthDays[gm - 1];
                gm++;
            }

            return [gy, gm, gd];
        };

        const jalaliMonthDays = (year, month) => month <= 6 ? 31 : (month <= 11 ? 30 : 29);

        let viewYear = Number(liveForm.dataset.jalaliYear);
        let viewMonth = Number(liveForm.dataset.jalaliMonth);
        let selected = hiddenDate.value || '';

        const syncScheduledAt = () => {
            if (!hiddenDate || !timeInput || !hiddenScheduledAt || !hiddenDate.value || !timeInput.value) return;
            const [jy, jm, jd] = hiddenDate.value.split('/').map(Number);
            const [gy, gm, gd] = jalaliToGregorian(jy, jm, jd);
            hiddenScheduledAt.value = `${gy}-${pad(gm)}-${pad(gd)}T${timeInput.value}`;
        };

        const renderPicker = () => {
            if (!days || !monthLabel) return;

            monthLabel.textContent = `${monthNames[viewMonth - 1]} ${digits(viewYear)}`;
            days.innerHTML = '';

            const [firstGy, firstGm, firstGd] = jalaliToGregorian(viewYear, viewMonth, 1);
            const firstWeekday = (new Date(firstGy, firstGm - 1, firstGd).getDay() + 1) % 7;

            for (let i = 0; i < firstWeekday; i++) {
                days.appendChild(document.createElement('i'));
            }

            for (let day = 1; day <= jalaliMonthDays(viewYear, viewMonth); day++) {
                const button = document.createElement('button');
                const value = `${viewYear}/${pad(viewMonth)}/${pad(day)}`;
                button.type = 'button';
                button.textContent = digits(day);
                button.className = value === selected ? 'is-selected' : '';
                button.addEventListener('click', () => {
                    selected = value;
                    hiddenDate.value = value;
                    dateTrigger.querySelector('b').textContent = `${digits(viewYear)}/${digits(pad(viewMonth))}/${digits(pad(day))}`;
                    picker.hidden = true;
                    syncScheduledAt();
                    renderPicker();
                });
                days.appendChild(button);
            }
        };

        if (selected) {
            const parts = selected.split('/').map(Number);
            if (parts.length === 3) {
                viewYear = parts[0];
                viewMonth = parts[1];
                dateTrigger.querySelector('b').textContent = parts.map((part) => digits(pad(part))).join('/');
            }
        }

        dateTrigger?.addEventListener('click', () => {
            picker.hidden = !picker.hidden;
            if (!picker.hidden) renderPicker();
        });

        liveForm.querySelector('[data-jalali-prev]')?.addEventListener('click', () => {
            viewMonth--;
            if (viewMonth < 1) { viewMonth = 12; viewYear--; }
            renderPicker();
        });

        liveForm.querySelector('[data-jalali-next]')?.addEventListener('click', () => {
            viewMonth++;
            if (viewMonth > 12) { viewMonth = 1; viewYear++; }
            renderPicker();
        });

        timeInput?.addEventListener('change', syncScheduledAt);
        liveForm.addEventListener('submit', (event) => {
            syncScheduledAt();
            if (!hiddenScheduledAt?.value) {
                event.preventDefault();
                dateTrigger?.focus();
            }
        });

        renderPicker();
    }

    const examBuilder = document.querySelector('[data-exam-builder]');

    if (!examBuilder) return;

    const container = examBuilder.querySelector('[data-exam-questions]');
    const template = examBuilder.querySelector('[data-exam-question-template]');
    const addButton = examBuilder.querySelector('[data-exam-add-question]');

    if (!container || !template || !addButton) return;

    const serializeOptions = (item, index) => {
        const optionsField = item.querySelector('[data-name="options_text"]');

        if (!optionsField) return;

        item.querySelectorAll('[data-option-hidden]').forEach((node) => node.remove());

        optionsField.value
            .split('\n')
            .map((value) => value.trim())
            .filter(Boolean)
            .forEach((value, optionIndex) => {
                const hidden = document.createElement('input');

                hidden.type = 'hidden';
                hidden.dataset.optionHidden = '1';
                hidden.name = `questions[${index}][options][${optionIndex}]`;
                hidden.value = value;

                item.appendChild(hidden);
            });
    };

    const syncQuestion = (item, index) => {
        item.querySelectorAll('[data-name]').forEach((field) => {
            field.name = `questions[${index}][${field.dataset.name}]`;
        });

        const optionsField = item.querySelector('[data-name="options_text"]');
        const typeField = item.querySelector('[data-name="type"]');

        if (optionsField && typeField) {
            const wrapper = optionsField.closest('label');

            if (wrapper) {
                wrapper.classList.toggle('hidden', typeField.value === 'text');
            }
        }

        serializeOptions(item, index);
    };

    const syncQuestions = () => {
        container.querySelectorAll('[data-exam-question]').forEach((item, index) => {
            syncQuestion(item, index);
        });
    };

    container.addEventListener('input', (event) => {
        const field = event.target.closest('[data-name="options_text"]');

        if (!field) return;

        const item = field.closest('[data-exam-question]');

        if (!item) return;

        const index = Array.from(container.querySelectorAll('[data-exam-question]')).indexOf(item);

        serializeOptions(item, index);
    });

    container.addEventListener('change', (event) => {
        const typeField = event.target.closest('[data-name="type"]');

        if (!typeField) return;

        syncQuestions();
    });

    container.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-exam-remove-question]');

        if (!removeButton) return;

        event.preventDefault();

        const item = removeButton.closest('[data-exam-question]');

        if (!item) return;

        item.remove();
        syncQuestions();
    });

    const addQuestion = () => {
        const fragment = template.content.cloneNode(true);

        container.appendChild(fragment);
        syncQuestions();

        const questions = container.querySelectorAll('[data-exam-question]');
        questions[questions.length - 1]
            ?.querySelector('[data-name="question"]')
            ?.focus();
    };

    addButton.addEventListener('click', addQuestion);
    addQuestion();
});