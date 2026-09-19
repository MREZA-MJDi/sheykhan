function bootPanel() {

    const openButton = document.querySelector('[data-role-menu-open]');

    openButton?.addEventListener('click', () => {
        document.body.classList.toggle('role-menu-open');
        openButton.setAttribute(
            'aria-expanded',
            document.body.classList.contains('role-menu-open') ? 'true' : 'false'
        );
    });

    document.addEventListener('click', (event) => {
        if (!document.body.classList.contains('role-menu-open')) return;

        const sidebar = document.querySelector('.role-sidebar');
        if (!sidebar || sidebar.contains(event.target) || openButton?.contains(event.target)) return;

        document.body.classList.remove('role-menu-open');
        openButton?.setAttribute('aria-expanded', 'false');
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 1050) {
            document.body.classList.remove('role-menu-open');
            openButton?.setAttribute('aria-expanded', 'false');
        }
    });
}
}

document.addEventListener('DOMContentLoaded', () => {
    bootPanel();

    document.documentElement.classList.add('owner-ready');

    const dashboard = document.querySelector('[data-teacher-dashboard]');
    if (dashboard) {
        const buttons = dashboard.querySelectorAll('[data-teacher-range]');
        const bars = dashboard.querySelectorAll('[data-teacher-bars] .teacher-bar');

        let datasets = { week: { values: [] }, month: { values: [] } };

        try {
            datasets = JSON.parse(dashboard.dataset.chart || '{}');
        } catch {
            datasets = { week: { values: [] }, month: { values: [] } };
        }

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                buttons.forEach((item) => item.classList.remove('active'));
                button.classList.add('active');

                const dataset = datasets[button.dataset.teacherRange] || {};
                const values = dataset.values || [];

                bars.forEach((bar, index) => {
                    bar.style.height = String(values[index] || 0) + '%';

                    const label = bar.querySelector('small');
                    if (label && dataset.labels) {
                        label.textContent = dataset.labels[index] || '';
                    }
                });
            });
        });

        const examBuilder = document.querySelector('[data-exam-builder]');
        if (examBuilder) {
            const container = examBuilder.querySelector('[data-exam-questions]');
            const template = examBuilder.querySelector('[data-exam-question-template]');
            const addButton = examBuilder.querySelector('[data-exam-add-question]');

            const syncQuestionNames = () => {
                container?.querySelectorAll('[data-exam-question]').forEach((item, index) => {
                    item.querySelectorAll('[data-name]').forEach((field) => {
                        const name = field.dataset.name;
                        field.name = `questions[${index}][${name}]`;
                    });

                    const optionsField = item.querySelector('[data-name="options_text"]');
                    const typeField = item.querySelector('[data-name="type"]');

                    const serializeOptions = () => {
                        if (!optionsField) return;

                        const values = optionsField.value
                            .split('\n')
                            .map((value) => value.trim())
                            .filter(Boolean);

                        item.querySelectorAll('[data-option-hidden]').forEach((node) => node.remove());

                        values.forEach((value, optionIndex) => {
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.dataset.optionHidden = '1';
                            hidden.name = `questions[${index}][options][${optionIndex}]`;
                            hidden.value = value;
                            item.appendChild(hidden);
                        });
                    };

                    optionsField?.addEventListener('input', serializeOptions);

                    typeField?.addEventListener('change', () => {
                        const label = optionsField?.closest('label');

                        if (!label) return;

                        label.classList.toggle('hidden', typeField.value === 'text');
                    });

                    item.querySelector('[data-exam-remove-question]')?.addEventListener('click', () => {
                        item.remove();
                        syncQuestionNames();
                    });

                    serializeOptions();
                    typeField?.dispatchEvent(new Event('change'));
                });
            };

            const addQuestion = () => {
                const fragment = template?.content?.cloneNode(true);

                if (!fragment) return;

                container.appendChild(fragment);
                syncQuestionNames();
            };

            addButton?.addEventListener('click', addQuestion);
            addQuestion();
        }
    }
});
