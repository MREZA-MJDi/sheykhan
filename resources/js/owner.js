import { bootPanel } from './panel-base.js';

document.addEventListener('DOMContentLoaded', () => {
    bootPanel();

    document.querySelectorAll('form').forEach((form) => {
        if (form.dataset.noSubmitLock === 'true') return;

        form.addEventListener('submit', (event) => {
            const submitter = event.submitter;

            if (submitter?.dataset.confirm && !window.confirm(submitter.dataset.confirm)) {
                event.preventDefault();
                return;
            }

            if (form.dataset.submitted === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitted = 'true';

            if (submitter) {
                submitter.disabled = true;
                submitter.setAttribute('aria-busy','true');
                submitter.dataset.originalText = submitter.textContent;
                submitter.textContent = 'در حال ثبت...';
            }
        });
    });

    document.querySelectorAll('[data-classroom-select]').forEach((classroomSelect) => {
        const courseSelect = document.getElementById(classroomSelect.dataset.courseSelect);
        if (!courseSelect) return;

        const syncClassrooms = () => {
            const selectedCourse = courseSelect.value;
            let visible = 0;

            classroomSelect.querySelectorAll('option[data-course-id]').forEach((option) => {
                const matches = option.dataset.courseId === selectedCourse;
                option.hidden = !matches;
                option.disabled = !matches;
                if (matches) visible += 1;
            });

            const selected = classroomSelect.selectedOptions[0];
            if (selected?.disabled) classroomSelect.value = '';

            classroomSelect.closest('form')?.querySelector('[data-no-classrooms]')
                ?.toggleAttribute('hidden', visible > 0);
        };

        courseSelect.addEventListener('change', syncClassrooms);
        syncClassrooms();
    });
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-submitted="true"]').forEach((form) => {
        form.dataset.submitted = 'false';
        form.querySelectorAll('button[aria-busy="true"]').forEach((button) => {
            button.disabled = false;
            button.removeAttribute('aria-busy');
            if (button.dataset.originalText) {
                button.textContent = button.dataset.originalText;
            }
        });
    });
});