import { bootPanel } from './panel-base.js';

function initDashboardChart() {
    const dashboard = document.querySelector('[data-teacher-dashboard]');
    if (!dashboard) return;

    const buttons = dashboard.querySelectorAll('[data-teacher-range]');
    const bars = dashboard.querySelectorAll('[data-teacher-bars] .teacher-bar');

    let datasets = { week: { values: [] }, month: { values: [] } };

    try {
        datasets = JSON.parse(dashboard.dataset.chart || '{}');
    } catch {
        datasets = { week: { values: [] }, month: { values: [] } };
    }

    const render = (key) => {
        const dataset = datasets[key] || {};
        const values = dataset.values || [];

        bars.forEach((bar, index) => {
            bar.style.height = String(Math.max(0, Math.min(100, values[index] || 0))) + '%';

            const label = bar.querySelector('small');
            if (label && dataset.labels) {
                label.textContent = dataset.labels[index] || '';
            }
        });
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            buttons.forEach((item) => item.classList.remove('active'));
            button.classList.add('active');
            render(button.dataset.teacherRange);
        });
    });

    render('week');
}

function initExamBuilder() {
    const examBuilder = document.querySelector('[data-exam-builder]');
    if (!examBuilder) return;

    const container = examBuilder.querySelector('[data-exam-questions]');
    const template = examBuilder.querySelector('[data-exam-question-template]');
    const addButton = examBuilder.querySelector('[data-exam-add-question]');

    const syncQuestion = (item, index) => {
        item.querySelector('.teacher-question-number')?.replaceChildren(
            document.createTextNode('سؤال ' + (index + 1))
        );

        item.querySelectorAll('[data-name]').forEach((field) => {
            const key = field.dataset.name;
            field.name = key ? `questions[${index}][${key}]` : '';
        });

        const typeField = item.querySelector('[data-name="type"]');
        const optionsField = item.querySelector('[data-name="options_text"]');
        const singleAnswerField = item.querySelector('[data-name="correct_answer"]');
        const multiAnswerField = item.querySelector('[data-correct-options-text]');
        const optionsLabel = item.querySelector('[data-options-field]');
        const singleAnswerLabel = item.querySelector('[data-single-answer-field]');
        const multiAnswerLabel = item.querySelector('[data-multiple-answer-field]');

        item.querySelectorAll('[data-exam-correct-hidden]').forEach((node) => node.remove());

        const serializeOptions = () => {
            item.querySelectorAll('[data-exam-option-hidden]').forEach((node) => node.remove());

            if (!optionsField || typeField?.value === 'text') return;

            optionsField.value
                .split('\\n')
                .map((value) => value.trim())
                .filter(Boolean)
                .forEach((value, optionIndex) => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.dataset.examOptionHidden = '1';
                    hidden.name = `questions[${index}][options][${optionIndex}]`;
                    hidden.value = value;
                    item.appendChild(hidden);
                });
        };

        const serializeCorrectAnswer = () => {
            item.querySelectorAll('[data-exam-correct-hidden]').forEach((node) => node.remove());

            const type = typeField?.value || 'text';

            if (type === 'multiple' || type === 'checkbox') {
                if (singleAnswerField) singleAnswerField.name = '';

                if (multiAnswerField) {
                    multiAnswerField.name = '';

                    multiAnswerField.value
                        .split('\\n')
                        .map((value) => value.trim())
                        .filter(Boolean)
                        .forEach((value, answerIndex) => {
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.dataset.examCorrectHidden = '1';
                            hidden.name = `questions[${index}][correct_answer][${answerIndex}]`;
                            hidden.value = value;
                            item.appendChild(hidden);
                        });
                }
            } else if (type === 'single') {
                if (singleAnswerField) {
                    singleAnswerField.name = `questions[${index}][correct_answer]`;
                }
                if (multiAnswerField) multiAnswerField.name = '';
            } else {
                if (singleAnswerField) singleAnswerField.name = '';
                if (multiAnswerField) multiAnswerField.name = '';
            }
        };

        const syncType = () => {
            const type = typeField?.value || 'text';
            const objective = type !== 'text';
            const multi = type === 'multiple' || type === 'checkbox';

            optionsLabel?.classList.toggle('hidden', !objective);
            singleAnswerLabel?.classList.toggle('hidden', !('single' === type));
            multiAnswerLabel?.classList.toggle('hidden', !multi);

            serializeOptions();
            serializeCorrectAnswer();
        };

        optionsField?.addEventListener('input', serializeOptions);
        multiAnswerField?.addEventListener('input', serializeCorrectAnswer);
        typeField?.addEventListener('change', syncType);

        item.querySelector('[data-exam-remove-question]')?.addEventListener('click', () => {
            item.remove();
            syncQuestionNames();
        });

        syncType();
    };

    const syncQuestionNames = () => {
        container?.querySelectorAll('[data-exam-question]').forEach(syncQuestion);
    };

    const addQuestion = () => {
        const fragment = template?.content?.cloneNode(true);
        if (!fragment) return;

        container.appendChild(fragment);
        syncQuestionNames();
    };

    addButton?.addEventListener('click', addQuestion);

    if (Number(examBuilder.dataset.existingQuestions || 0) > 0) {
        syncQuestionNames();
    } else {
        addQuestion();
    }
}

function initSectionReorder() {
    const list = document.querySelector('[data-section-list]');
    if (!list) return;

    const items = [...list.querySelectorAll('[data-section-item]')];
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    items.forEach((item) => {
        const handle = item.querySelector('[draggable="true"]');
        if (!handle) return;

        handle.addEventListener('dragstart', () => {
            item.classList.add('is-dragging');
        });

        handle.addEventListener('dragend', async () => {
            item.classList.remove('is-dragging');

            const courseId = list.closest('[data-course-builder]')?.dataset.courseId;
            const reorderUrl = list.closest('[data-course-builder]')?.dataset.reorderUrl;

            if (!courseId || !reorderUrl || !csrf) return;

            const ids = [...list.querySelectorAll('[data-section-id]')]
                .map((node) => Number(node.dataset.sectionId));

            try {
                const response = await fetch(reorderUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ ids }),
                });

                if (!response.ok) throw new Error('reorder failed');
            } catch {
                window.location.reload();
            }
        });

        item.addEventListener('dragover', (event) => {
            event.preventDefault();

            const dragging = list.querySelector('.is-dragging');
            if (!dragging || dragging === item) return;

            const rect = item.getBoundingClientRect();
            const after = event.clientY > rect.top + rect.height / 2;

            item.parentNode.insertBefore(
                dragging,
                after ? item.nextSibling : item
            );
        });
    });
}

function initLessonReorder() {
    document.querySelectorAll('[data-lesson-list]').forEach((list) => {
        const items = [...list.querySelectorAll('[data-lesson-card]')];
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const reorderUrl = list.dataset.lessonReorderUrl;

        items.forEach((item) => {
            const handle = item.querySelector('[data-lesson-id] .teacher-lesson-drag') || item.querySelector('.teacher-lesson-drag');
            if (!handle) return;

            handle.addEventListener('dragstart', () => {
                item.classList.add('is-dragging');
            });

            handle.addEventListener('dragend', async () => {
                item.classList.remove('is-dragging');

                if (!csrf || !reorderUrl) return;

                const ids = [...list.querySelectorAll('[data-lesson-id]')]
                    .map((node) => Number(node.dataset.lessonId));

                try {
                    const response = await fetch(reorderUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ ids }),
                    });

                    if (!response.ok) throw new Error('lesson reorder failed');
                } catch {
                    window.location.reload();
                }
            });

            item.addEventListener('dragover', (event) => {
                event.preventDefault();

                const dragging = list.querySelector('.is-dragging');
                if (!dragging || dragging === item) return;

                const rect = item.getBoundingClientRect();
                const after = event.clientY > rect.top + rect.height / 2;

                item.parentNode.insertBefore(
                    dragging,
                    after ? item.nextSibling : item
                );
            });
        });
    });
}

function initMediaUploadUX() {
    document.querySelectorAll('[data-media-type]').forEach((select) => {
        const form = select.closest('form');
        const fileInput = form?.querySelector('[data-media-file]');
        const downloadCheckbox = form?.querySelector('[data-media-downloadable]');

        if (!form || !fileInput) return;

        const sync = () => {
            const type = select.value;

            if (type === 'video') {
                fileInput.accept = 'video/mp4,video/webm,video/quicktime';
                if (downloadCheckbox) {
                    downloadCheckbox.checked = false;
                    downloadCheckbox.disabled = true;
                }
            } else if (type === 'pdf') {
                fileInput.accept = 'application/pdf';
                if (downloadCheckbox) downloadCheckbox.disabled = false;
            } else if (type === 'thumbnail') {
                fileInput.accept = 'image/jpeg,image/png,image/webp';
                if (downloadCheckbox) downloadCheckbox.disabled = false;
            } else {
                fileInput.accept = '.pdf,.zip,.jpg,.jpeg,.png,.webp';
                if (downloadCheckbox) downloadCheckbox.disabled = false;
            }
        };

        select.addEventListener('change', sync);
        sync();
    });
}

function initCourseScopedClassrooms() {
    document.querySelectorAll('[data-course-scope-select]').forEach((courseSelect) => {
        const targetId = courseSelect.dataset.classroomTarget;
        const classroomSelect = document.getElementById(targetId);

        if (!classroomSelect) return;

        const sync = () => {
            const courseId = String(courseSelect.value || '');
            const currentValue = String(classroomSelect.value || '');

            [...classroomSelect.options].forEach((option) => {
                if (!option.dataset.courseId) {
                    option.hidden = false;
                    return;
                }

                option.hidden = String(option.dataset.courseId) !== courseId;
            });

            const selectedOption = [...classroomSelect.options].find(
                (option) => !option.hidden && String(option.value) === currentValue
            );

            if (!selectedOption && classroomSelect.value) {
                classroomSelect.value = '';
            }
        };

        courseSelect.addEventListener('change', sync);
        sync();
    });
}

function initConfirmForms() {
    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirm || 'آیا از انجام این عملیات مطمئن هستید؟';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    bootPanel();

    document.documentElement.classList.add('teacher-ready');

    initDashboardChart();
    initExamBuilder();
    initSectionReorder();
    initLessonReorder();
    initMediaUploadUX();
    initCourseScopedClassrooms();
    initConfirmForms();
});
