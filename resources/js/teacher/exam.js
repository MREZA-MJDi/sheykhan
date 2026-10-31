// Teacher exam builder wired to StoreExamRequest/ExamQuestionWriter field names.
const faNum = (value) => Number(value || 0).toLocaleString('fa-IR');

export function initExam() {
  document.querySelectorAll('[data-tp-exam]').forEach((form) => {
    const box = form.querySelector('[data-tp-questions]');
    const totalEls = Array.from(form.querySelectorAll('[data-tp-total]'));
    if (!box || totalEls.length === 0) return;

    const sync = () => {
      box.querySelectorAll('[data-tp-q]').forEach((question, index) => {
        const number = question.querySelector('[data-tp-qno]');
        if (number) number.textContent = faNum(index + 1);
        question.querySelectorAll('[name]').forEach((field) => {
          field.name = field.name.replace(/^questions\[[^\]]*\]/, `questions[${index}]`);
        });
        question.querySelectorAll('[data-tp-option-input]').forEach((field) => {
          const option = field.value;
          const control = field.closest('.tp-question-option')?.querySelector('[data-tp-correct]');
          if (control) control.value = option;
        });
      });
      const total = Array.from(box.querySelectorAll('[data-tp-score]'))
        .reduce((sum, input) => sum + Math.max(0, Number(input.value) || 0), 0);
      totalEls.forEach((element) => { element.textContent = faNum(total); });
      const emptyState = form.querySelector('[data-tp-empty-questions]');
      if (emptyState) emptyState.hidden = box.querySelectorAll('[data-tp-q]').length > 0;
    };

    const bindHeader = (field) => {
      const key = field.dataset.tpModel;
      const value = field.tagName === 'SELECT' ? field.selectedOptions[0]?.textContent : field.value;
      form.querySelectorAll(`[data-tp-bind="${key}"]`).forEach((target) => {
        target.textContent = key === 'duration_minutes' ? faNum(value) : (value || (key === 'title' ? 'عنوان آزمون' : ''));
      });
    };

    form.querySelectorAll('[data-tp-model]').forEach((field) => {
      field.addEventListener('input', () => bindHeader(field));
      field.addEventListener('change', () => bindHeader(field));
      bindHeader(field);
    });

    form.addEventListener('click', (event) => {
      const add = event.target.closest('[data-tp-add-q]');
      if (add) {
        const template = form.querySelector(`[data-tp-tpl="${add.dataset.tpAddQ}"]`);
        if (template?.content) {
          box.appendChild(template.content.cloneNode(true));
          sync();
          box.lastElementChild?.querySelector('textarea, input[type="text"]')?.focus();
        }
      }
      const remove = event.target.closest('[data-tp-q-remove]');
      if (remove) {
        remove.closest('[data-tp-q]')?.remove();
        sync();
      }
      if (event.target.closest('[data-tp-print]')) window.print();
    });

    form.addEventListener('input', (event) => {
      if (event.target.matches('[data-tp-score], [data-tp-option-input]')) sync();
    });
    form.addEventListener('change', (event) => {
      if (event.target.matches('[data-tp-score], [data-tp-option-input]')) sync();
    });

    sync();
  });
}