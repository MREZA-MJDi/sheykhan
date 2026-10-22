// Shared exam builder for teacher and academy owner screens.
const initializeExamBuilder = () => {
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


    const examBuilder = document.querySelector('[data-exam-builder]');

    if (!examBuilder) return;

    const container = examBuilder.querySelector('[data-exam-questions]');
    const template = examBuilder.querySelector('[data-exam-question-template]');
    const addButton = examBuilder.querySelector('[data-exam-add-question]');

    if (!container || !template || !addButton) return;

    const readQuestionOptions = (item) => {
        const field = item.querySelector('[data-name="options_text"]');
        if (!field) return [];

        return field.value
            .split(/\r?\n/)
            .map((value) => value.trim())
            .filter(Boolean);
    };

    const serializeOptions = (item, index, options) => {
        item.querySelectorAll('[data-option-hidden]').forEach((node) => node.remove());

        options.forEach((value, optionIndex) => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.dataset.optionHidden = '1';
            hidden.name = `questions[${index}][options][${optionIndex}]`;
            hidden.value = value;
            item.appendChild(hidden);
        });
    };

    const renderCorrectAnswer = (item, index, type, options, previousSelection) => {
        const wrapper = item.querySelector('[data-question-correct]');
        const choices = item.querySelector('[data-correct-answer-choices]');
        const label = item.querySelector('[data-correct-answer-label]');
        const help = item.querySelector('[data-correct-answer-help]');

        if (!wrapper || !choices) return;

        wrapper.classList.toggle('hidden', type === 'text');
        choices.replaceChildren();

        if (type === 'text') return;

        const multi = type === 'multiple' || type === 'checkbox';
        if (label) {
            label.textContent = multi
                ? 'پاسخ‌های صحیح (می‌توانی چند گزینه را انتخاب کنی)'
                : 'پاسخ صحیح (فقط یک گزینه)';
        }

        if (!options.length) {
            const empty = document.createElement('p');
            empty.className = 'rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-6 text-amber-800';
            empty.textContent = 'اول گزینه‌ها را بنویس تا بتوانی پاسخ صحیح را انتخاب کنی.';
            choices.appendChild(empty);
        } else {
            options.forEach((option, optionIndex) => {
                const row = document.createElement('label');
                row.className = 'flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 text-sm leading-6';

                const input = document.createElement('input');
                input.type = multi ? 'checkbox' : 'radio';
                input.name = multi
                    ? `questions[${index}][correct_answer][]`
                    : `questions[${index}][correct_answer]`;
                input.value = option;
                input.dataset.correctAnswerChoice = '1';
                input.checked = previousSelection.includes(option);
                input.className = 'mt-1';
                input.setAttribute('aria-label', 'پاسخ صحیح: ' + option);
                if (!multi) input.required = true;

                const copy = document.createElement('span');
                copy.className = 'min-w-0 flex-1';
                copy.textContent = `${optionIndex + 1}. ${option}`;

                row.append(input, copy);
                choices.appendChild(row);
            });
        }

        if (help) {
            help.textContent = multi
                ? 'این سؤال وقتی درست است که مجموعه پاسخ‌های دانش‌آموز با گزینه‌های صحیح دقیقاً برابر باشد.'
                : 'پاسخ صحیح در تصحیح خودکار با همین گزینه مقایسه می‌شود.';
        }
    };

    const syncQuestion = (item, index) => {
        item.querySelectorAll('[data-name]').forEach((field) => {
            if (field.dataset.name === 'options_text') {
                // The editor is for humans. Only its serialized options[] values
                // are submitted to the server.
                field.removeAttribute('name');
                return;
            }

            field.name = `questions[${index}][${field.dataset.name}]`;
        });

        const optionsField = item.querySelector('[data-name="options_text"]');
        const typeField = item.querySelector('[data-name="type"]');
        const optionsWrapper = item.querySelector('[data-question-options]');
        if (!typeField) return;

        const type = typeField.value;
        const options = readQuestionOptions(item);
        const previousSelection = Array.from(
            item.querySelectorAll('[data-correct-answer-choice]:checked')
        ).map((input) => input.value);

        if (optionsWrapper) optionsWrapper.classList.toggle('hidden', type === 'text');
        if (optionsField && type === 'text') optionsField.removeAttribute('required');

        serializeOptions(item, index, type === 'text' ? [] : options);
        renderCorrectAnswer(item, index, type, options, previousSelection);
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
        syncQuestion(item, index);
    });

    container.addEventListener('change', (event) => {
        const typeField = event.target.closest('[data-name="type"]');
        if (typeField) {
            const item = typeField.closest('[data-exam-question]');
            const index = Array.from(container.querySelectorAll('[data-exam-question]')).indexOf(item);
            if (item) syncQuestion(item, index);
            return;
        }

        const choice = event.target.closest('[data-correct-answer-choice]');
        if (choice) {
            // Keep native radio/checkbox behavior. The server performs the final
            // type-aware validation when the teacher saves the exam.
            return;
        }
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

    const addQuestion = (initial = {}, focus = true) => {
        const fragment = template.content.cloneNode(true);
        container.appendChild(fragment);

        const item = container.querySelectorAll('[data-exam-question]');
        const questionItem = item[item.length - 1];
        const text = questionItem?.querySelector('[data-name="question"]');
        const type = questionItem?.querySelector('[data-name="type"]');
        const score = questionItem?.querySelector('[data-name="score"]');
        const options = questionItem?.querySelector('[data-name="options_text"]');

        if (text) text.value = typeof initial.question === 'string' ? initial.question : '';
        if (type && ['text', 'single', 'multiple', 'checkbox'].includes(initial.type)) {
            type.value = initial.type;
        }
        if (score && initial.score !== undefined && initial.score !== null) score.value = initial.score;
        if (options) options.value = Array.isArray(initial.options) ? initial.options.join('\n') : '';

        syncQuestions();

        // Rehydrate the selected answer key only after the corresponding
        // radio/checkboxes have been rendered from the options list.
        const expected = Array.isArray(initial.correct_answer)
            ? initial.correct_answer.map(String)
            : (initial.correct_answer === undefined || initial.correct_answer === null
                ? []
                : [String(initial.correct_answer)]);

        questionItem?.querySelectorAll('[data-correct-answer-choice]').forEach((input) => {
            input.checked = expected.includes(input.value);
        });

        if (focus) text?.focus();
    };

    addButton.addEventListener('click', () => addQuestion({}, true));

    let initialQuestions = [];
    try {
        const raw = JSON.parse(examBuilder.dataset.initialQuestions || '[]');
        initialQuestions = Array.isArray(raw) ? raw.filter((question) => question && typeof question === 'object') : [];
    } catch {
        initialQuestions = [];
    }

    if (initialQuestions.length) {
        initialQuestions.forEach((question) => addQuestion(question, false));
    } else {
        addQuestion({}, true);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeExamBuilder, { once: true });
} else {
    initializeExamBuilder();
}
