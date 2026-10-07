import { bootPanel } from './panel-base.js';

document.addEventListener('DOMContentLoaded', () => {
    bootPanel();

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