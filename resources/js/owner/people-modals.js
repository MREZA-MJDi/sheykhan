/* Owner people create flows: keep the directory visible and open long forms in native dialogs. */
(() => {
    'use strict';

    const forms = document.querySelectorAll('form[data-owner-people-create]');
    if (!forms.length) return;

    const workspace = document.querySelector('.owner-people-workspace');
    const actions = document.createElement('div');
    actions.className = 'owner-people-actions';
    actions.setAttribute('aria-label', 'ایجاد عضو جدید');
    const intro = workspace?.querySelector(':scope > div:first-child');
    if (intro) intro.after(actions);

    forms.forEach((form) => {
        const kind = form.dataset.ownerPeopleCreate;
        const title = form.dataset.ownerPeopleCreateTitle || 'ایجاد عضو';
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'owner-people-create-trigger';
        trigger.textContent = kind === 'student' ? '＋ ایجاد دانش‌آموز'
            : kind === 'parent' ? '＋ ثبت اولیا'
                : '＋ ساخت حساب مدرس';
        trigger.setAttribute('aria-haspopup', 'dialog');

        const dialog = document.createElement('dialog');
        dialog.className = 'owner-people-create-dialog';
        dialog.setAttribute('aria-label', title);

        const header = document.createElement('header');
        header.className = 'owner-people-create-dialog__head';
        const heading = document.createElement('strong');
        heading.textContent = title;
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'owner-people-create-dialog__close';
        close.textContent = '×';
        close.setAttribute('aria-label', 'بستن پنجره');
        close.addEventListener('click', () => dialog.close());
        header.append(heading, close);

        const body = document.createElement('div');
        body.className = 'owner-people-create-dialog__body';
        const formShell = document.createElement('div');
        formShell.className = 'owner-student-create-panel';
        formShell.append(form);
        body.append(formShell);
        dialog.append(header, body);
        document.body.append(dialog);

        actions.append(trigger);
        trigger.addEventListener('click', () => {
            if (!dialog.open) dialog.showModal();
            const focusTarget = form.querySelector('input:not([type="hidden"]), select, textarea');
            focusTarget?.focus({ preventScroll: true });
        });

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close();
        });

        const hasValidationErrors = form.querySelector(
            '[role="alert"], .text-rose-600, .owner-student-create-errors, .owner-student-no-grades'
        );
        if (hasValidationErrors) {
            dialog.showModal();
        }
    });

    document.querySelectorAll('form[data-owner-schedule-create]').forEach((form) => {
        const title = form.dataset.ownerScheduleTitle || 'افزودن زمان هفتگی';
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'owner-people-create-trigger owner-schedule-create-trigger';
        trigger.textContent = '＋ افزودن زمان هفتگی';
        trigger.setAttribute('aria-haspopup', 'dialog');

        const dialog = document.createElement('dialog');
        dialog.className = 'owner-people-create-dialog owner-schedule-create-dialog';
        dialog.setAttribute('aria-label', title);

        const header = document.createElement('header');
        header.className = 'owner-people-create-dialog__head';
        const heading = document.createElement('strong');
        heading.textContent = title;
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'owner-people-create-dialog__close';
        close.textContent = '×';
        close.setAttribute('aria-label', 'بستن پنجره');
        close.addEventListener('click', () => dialog.close());
        header.append(heading, close);

        const body = document.createElement('div');
        body.className = 'owner-people-create-dialog__body';
        body.append(form);
        dialog.append(header, body);
        document.body.append(dialog);

        const editor = form.closest('.owner-schedule-editor');
        editor?.insertBefore(trigger, editor.firstChild);
        trigger.addEventListener('click', () => {
            if (!dialog.open) dialog.showModal();
            form.querySelector('select, input')?.focus({ preventScroll: true });
        });
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close();
        });
        if (form.querySelector('[role="alert"]')) dialog.showModal();
    });

})();
