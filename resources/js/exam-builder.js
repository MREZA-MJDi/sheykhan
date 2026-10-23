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

    const toFaDigits = (value) => String(value).replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
    const formatScore = (value) => {
        const rounded = Number.isFinite(value) ? Math.round(value * 100) / 100 : 0;
        return toFaDigits(String(rounded));
    };

    const typeLabel = (type) => ({
        text: 'تشریحی',
        single: 'چهارگزینه‌ای',
        multiple: 'چندپاسخی',
        checkbox: 'چندپاسخی',
    }[type] || 'تشریحی');

    const readQuestionOptions = (item) => {
        const entries = [...item.querySelectorAll('[data-option-entry]')];
        if (entries.length) {
            return entries.map((field) => field.value.trim()).filter(Boolean);
        }

        // Compatibility with a legacy question block that may still contain
        // the previous newline-separated options editor.
        const field = item.querySelector('[data-name="options_text"]');
        return field
            ? field.value.split(/\r?\n/).map((value) => value.trim()).filter(Boolean)
            : [];
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

    const renderCorrectAnswer = (item, index, type, options, previousSelection, requireCorrectAnswer) => {
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
                ? 'پاسخ‌های صحیح (سؤال چندپاسخی قدیمی)'
                : 'کلید پاسخ · یک گزینهٔ صحیح';
        }

        if (!options.length) {
            const empty = document.createElement('p');
            empty.className = 'teacher-exam-answer-key-empty';
            empty.textContent = 'ابتدا چهار گزینه را کامل کن تا کلید پاسخ ساخته شود.';
            choices.appendChild(empty);
        } else {
            options.forEach((option, optionIndex) => {
                const row = document.createElement('label');
                row.className = 'teacher-exam-correct-choice';

                const input = document.createElement('input');
                input.type = multi ? 'checkbox' : 'radio';
                input.name = multi
                    ? `questions[${index}][correct_answer][]`
                    : `questions[${index}][correct_answer]`;
                input.value = option;
                input.dataset.correctAnswerChoice = '1';
                input.checked = previousSelection.includes(option);
                input.setAttribute('aria-label', `پاسخ صحیح: ${option}`);
                if (!multi) input.required = Boolean(requireCorrectAnswer);

                const marker = document.createElement('span');
                marker.className = 'teacher-exam-correct-choice-marker';
                marker.textContent = ['الف', 'ب', 'ج', 'د'][optionIndex] || toFaDigits(optionIndex + 1);

                const copy = document.createElement('span');
                copy.className = 'teacher-exam-correct-choice-copy';
                copy.textContent = option;

                const check = document.createElement('span');
                check.className = 'teacher-exam-correct-choice-check';
                check.setAttribute('aria-hidden', 'true');
                check.textContent = '✓';

                row.append(marker, copy, input, check);
                choices.appendChild(row);
            });
        }

        if (help) {
            help.textContent = multi
                ? 'این سؤال قدیمی با مجموعهٔ گزینه‌های صحیح تصحیح می‌شود.'
                : 'دانش‌آموز فقط یکی از چهار گزینه را انتخاب می‌کند؛ پاسخ صحیح در تصحیح خودکار استفاده می‌شود.';
        }
    };

    const updatePaperSummary = () => {
        const questions = [...container.querySelectorAll('[data-exam-question]')];
        const total = questions.reduce((sum, item) => {
            const score = Number(item.querySelector('[data-name="score"]')?.value || 0);
            return sum + (Number.isFinite(score) && score > 0 ? score : 0);
        }, 0);

        examBuilder.querySelectorAll('[data-exam-question-count]').forEach((node) => {
            node.textContent = toFaDigits(questions.length);
        });
        examBuilder.querySelectorAll('[data-exam-total-score]').forEach((node) => {
            node.textContent = formatScore(total);
        });

        const titleInput = examBuilder.querySelector('[data-exam-title]');
        const title = titleInput?.value.trim() || 'عنوان آزمون شما';
        const titlePreview = examBuilder.querySelector('[data-exam-preview-title]');
        if (titlePreview) titlePreview.textContent = title;

        const courseSelect = examBuilder.querySelector('[data-course-select]');
        const coursePreview = examBuilder.querySelector('[data-exam-preview-course]');
        if (coursePreview) {
            coursePreview.textContent = courseSelect?.selectedOptions?.[0]?.textContent?.trim()
                ? `دوره: ${courseSelect.selectedOptions[0].textContent.trim()}`
                : 'دورهٔ انتخاب‌شده';
        }

        const durationInput = examBuilder.querySelector('[data-exam-duration]');
        const durationPreview = examBuilder.querySelector('[data-exam-preview-duration]');
        if (durationPreview) {
            const duration = Number(durationInput?.value || 0);
            durationPreview.textContent = duration > 0 ? `${toFaDigits(duration)} دقیقه` : 'بدون محدودیت';
        }

        const descriptionInput = examBuilder.querySelector('[data-exam-description]');
        const descriptionPreview = examBuilder.querySelector('[data-exam-preview-description]');
        if (descriptionPreview) {
            const description = descriptionInput?.value.trim() || '';
            descriptionPreview.textContent = description;
            descriptionPreview.hidden = !description;
        }

        questions.forEach((item, index) => {
            const number = item.querySelector('[data-question-number]');
            const kind = item.querySelector('[data-question-kind]');
            const type = item.querySelector('[data-name="type"]')?.value || 'text';
            const scoreInput = item.querySelector('[data-name="score"]');
            const score = Number(scoreInput?.value || 0);
            const printScore = item.querySelector('[data-print-score]');
            const foot = item.querySelector('[data-question-foot-note]');
            if (number) number.textContent = `سؤال ${toFaDigits(index + 1)}`;
            if (printScore) printScore.textContent = `${formatScore(score)} نمره`;
            item.dataset.questionType = type;
            if (kind) {
                kind.textContent = typeLabel(type);
                kind.dataset.questionType = type;
            }
            if (foot) {
                foot.textContent = type === 'text'
                    ? 'دانش‌آموز فضای نوشتن پاسخ دارد؛ پاسخ مرجع فقط برای مدرس است.'
                    : type === 'single'
                        ? 'در برگهٔ دانش‌آموز چهار گزینه با انتخاب تک‌پاسخ نمایش داده می‌شود.'
                        : 'این سؤال از نوع چندپاسخی قدیمی است.';
            }
        });
    };

    const syncQuestion = (item, index) => {
        item.querySelectorAll('[data-name]').forEach((field) => {
            if (field.dataset.name === 'options_text') {
                field.removeAttribute('name');
                return;
            }
            field.name = `questions[${index}][${field.dataset.name}]`;
        });

        const typeField = item.querySelector('[data-name="type"]');
        const optionsWrapper = item.querySelector('[data-question-options]');
        const referenceWrapper = item.querySelector('[data-question-reference]');
        const type = typeField?.value || 'text';
        const options = readQuestionOptions(item);
        const questionPrompt = item.querySelector('[data-name="question"]');
        const referenceAnswer = item.querySelector('[data-name="expected_answer"]');
        const status = examBuilder.querySelector('[name="status"]')?.value || 'draft';
        const hasDraftContent = Boolean(
            questionPrompt?.value.trim()
            || options.length
            || referenceAnswer?.value.trim()
        );
        const isEmptyDraftRow = status === 'draft' && !hasDraftContent;
        const answerSpace = item.querySelector('[data-answer-space-preview]');
        if (answerSpace) answerSpace.classList.toggle('hidden', type !== 'text');
        const previousSelection = [...item.querySelectorAll('[data-correct-answer-choice]:checked')]
            .map((input) => input.value);

        if (questionPrompt) questionPrompt.required = !isEmptyDraftRow;
        if (optionsWrapper) optionsWrapper.classList.toggle('hidden', type === 'text');
        if (referenceWrapper) referenceWrapper.classList.toggle('hidden', type !== 'text');

        // An incomplete draft can be saved and resumed later. Publishing turns
        // on the stricter browser checks that mirror server-side validation.
        item.querySelectorAll('[data-option-entry]').forEach((field) => {
            field.required = status === 'published' && type === 'single';
            field.setAttribute('aria-required', field.required ? 'true' : 'false');
        });

        const optionText = item.querySelector('[data-name="options_text"]');
        if (optionText && type === 'text') optionText.removeAttribute('required');

        serializeOptions(item, index, type === 'text' ? [] : options);
        renderCorrectAnswer(item, index, type, options, previousSelection, status === 'published');
    };

    const syncQuestions = () => {
        container.querySelectorAll('[data-exam-question]').forEach((item, index) => {
            syncQuestion(item, index);
        });
        updatePaperSummary();
    };

    container.addEventListener('input', (event) => {
        const field = event.target.closest('[data-option-entry], [data-name="options_text"], [data-name="score"], [data-name="question"], [data-name="expected_answer"]');
        if (!field) return;
        const item = field.closest('[data-exam-question]');
        if (!item) return;
        const index = [...container.querySelectorAll('[data-exam-question]')].indexOf(item);
        syncQuestion(item, index);
        updatePaperSummary();
    });

    container.addEventListener('change', (event) => {
        const typeField = event.target.closest('[data-name="type"]');
        if (typeField) {
            const item = typeField.closest('[data-exam-question]');
            if (item) {
                const index = [...container.querySelectorAll('[data-exam-question]')].indexOf(item);
                syncQuestion(item, index);
                updatePaperSummary();
            }
        }
    });

    examBuilder.querySelector('[name="status"]')?.addEventListener('change', syncQuestions);

    examBuilder.addEventListener('submit', () => {
        if (examBuilder.querySelector('[name="status"]')?.value !== 'draft') return;

        const blankRows = [...container.querySelectorAll('[data-exam-question]')].filter((item) => {
            const question = item.querySelector('[data-name="question"]')?.value.trim() || '';
            const answer = item.querySelector('[data-name="expected_answer"]')?.value.trim() || '';
            const options = readQuestionOptions(item);
            return !question && !answer && options.length === 0;
        });

        blankRows.forEach((item) => item.remove());
        if (blankRows.length) syncQuestions();
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
        container.appendChild(template.content.cloneNode(true));
        const questionItems = container.querySelectorAll('[data-exam-question]');
        const item = questionItems[questionItems.length - 1];
        if (!item) return;

        const prompt = item.querySelector('[data-name="question"]');
        const type = item.querySelector('[data-name="type"]');
        const score = item.querySelector('[data-name="score"]');
        const referenceAnswer = item.querySelector('[data-name="expected_answer"]');

        if (prompt) prompt.value = typeof initial.question === 'string' ? initial.question : '';
        if (type && ['text', 'single', 'multiple', 'checkbox'].includes(initial.type)) {
            type.value = initial.type;
        }
        if (score && initial.score !== undefined && initial.score !== null) score.value = initial.score;
        if (referenceAnswer && typeof initial.expected_answer === 'string') {
            referenceAnswer.value = initial.expected_answer;
        }

        const entries = [...item.querySelectorAll('[data-option-entry]')];
        const initialOptions = Array.isArray(initial.options)
            ? initial.options
            : (typeof initial.options_text === 'string'
                ? initial.options_text.split(/\r?\n/)
                : []);
        entries.forEach((field, index) => {
            field.value = typeof initialOptions[index] === 'string' ? initialOptions[index] : '';
        });

        syncQuestions();

        const expected = Array.isArray(initial.correct_answer)
            ? initial.correct_answer.map(String)
            : (initial.correct_answer === undefined || initial.correct_answer === null
                ? []
                : [String(initial.correct_answer)]);

        item.querySelectorAll('[data-correct-answer-choice]').forEach((input) => {
            input.checked = expected.includes(input.value);
        });

        if (focus) prompt?.focus();
    };

    addButton.addEventListener('click', () => addQuestion({}, true));

    let initialQuestions = [];
    try {
        const raw = JSON.parse(examBuilder.dataset.initialQuestions || '[]');
        initialQuestions = Array.isArray(raw)
            ? raw.filter((question) => question && typeof question === 'object')
            : [];
    } catch {
        initialQuestions = [];
    }

    examBuilder.querySelector('[data-print-exam]')?.addEventListener('click', () => window.print());
    examBuilder.querySelector('[data-exam-title]')?.addEventListener('input', updatePaperSummary);
    examBuilder.querySelector('[data-course-select]')?.addEventListener('change', updatePaperSummary);
    examBuilder.querySelector('[data-exam-duration]')?.addEventListener('input', updatePaperSummary);
    examBuilder.querySelector('[data-exam-description]')?.addEventListener('input', updatePaperSummary);

    if (initialQuestions.length) {
        initialQuestions.forEach((question) => addQuestion(question, false));
    } else {
        addQuestion({}, true);
    }

    updatePaperSummary();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeExamBuilder, { once: true });
} else {
    initializeExamBuilder();
}
