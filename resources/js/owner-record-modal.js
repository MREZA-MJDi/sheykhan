/* Accessible, progressive-enhancement modal layer for owner CRUD screens. */
(() => {
    'use strict';

    const isSameOrigin = (url) => {
        try { return new URL(url, window.location.href).origin === window.location.origin; }
        catch { return false; }
    };

    const findDialog = (trigger) => {
        const id = trigger.dataset.ownerModalOpen;
        return id ? document.getElementById(id) : null;
    };

    const openStatic = (dialog, trigger = null) => {
        if (!dialog || typeof dialog.showModal !== 'function') return;
        dialog._ownerModalReturnFocus = trigger || document.activeElement;
        if (!dialog.open) dialog.showModal();
        const focusTarget = dialog.querySelector('[autofocus], input:not([type="hidden"]), select, textarea, button:not([data-owner-modal-close])');
        focusTarget?.focus({ preventScroll: true });
    };

    document.querySelectorAll('[data-owner-modal-open-on-load]').forEach((dialog) => openStatic(dialog));

    document.addEventListener('click', async (event) => {
        const close = event.target.closest('[data-owner-modal-close]');
        if (close) {
            const dialog = close.closest('dialog');
            if (dialog?.open) dialog.close();
            return;
        }

        const staticTrigger = event.target.closest('[data-owner-modal-open]');
        if (staticTrigger) {
            event.preventDefault();
            openStatic(findDialog(staticTrigger), staticTrigger);
            return;
        }

        const remoteTrigger = event.target.closest('[data-owner-modal-url]');
        if (!remoteTrigger) return;

        const url = remoteTrigger.dataset.ownerModalUrl || remoteTrigger.getAttribute('href');
        if (!url || !isSameOrigin(url)) return;

        const dialog = document.querySelector('[data-owner-record-modal]');
        const body = dialog?.querySelector('[data-owner-record-modal-body]');
        const title = dialog?.querySelector('[data-owner-record-modal-title]');
        if (!dialog || !body || typeof dialog.showModal !== 'function') {
            window.location.assign(url);
            return;
        }

        event.preventDefault();
        if (title) title.textContent = remoteTrigger.dataset.ownerModalTitle || 'ویرایش اطلاعات';
        body.replaceChildren();
        const loading = document.createElement('p');
        loading.className = 'owner-modal-loading';
        loading.textContent = 'در حال آماده‌سازی فرم…';
        body.append(loading);
        dialog._ownerModalReturnFocus = remoteTrigger;
        dialog.showModal();

        try {
            const response = await fetch(url, {
                credentials: 'same-origin',
                headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) throw new Error('modal-load-failed');
            const html = await response.text();
            body.innerHTML = html;
            if (window.Alpine?.initTree) window.Alpine.initTree(body);
            window.dispatchEvent(new CustomEvent('owner:modal:mounted', { detail: { root: body, dialog } }));
            body.querySelector('input:not([type="hidden"]), select, textarea, button')?.focus({ preventScroll: true });
        } catch {
            dialog.close();
            window.location.assign(url);
        }
    });

    document.addEventListener('click', (event) => {
        const dialog = event.target.closest('dialog.owner-floating-modal, dialog[data-owner-record-modal]');
        if (!dialog || event.target !== dialog) return;
        dialog.close();
    });

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        const dialog = form.closest('dialog[data-owner-record-modal]');
        if (!dialog || form.matches('[data-owner-native-submit], [enctype="multipart/form-data"], [data-upload-form]')) return;

        event.preventDefault();
        if (!form.reportValidity()) return;

        const submitter = event.submitter;
        const submitButton = submitter instanceof HTMLButtonElement ? submitter : form.querySelector('button[type="submit"], button:not([type])');
        if (submitButton?.disabled) return;

        const originalText = submitButton?.textContent;
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'در حال ذخیره…';
        }

        form.querySelector('[data-owner-modal-errors]')?.remove();

        try {
            const response = await fetch(form.action, {
                method: (form.method || 'POST').toUpperCase(),
                body: new FormData(form, submitter || undefined),
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            if (response.status === 422) {
                const payload = await response.json();
                const messages = Object.values(payload.errors || {}).flat();
                const notice = document.createElement('div');
                notice.dataset.ownerModalErrors = '';
                notice.className = 'owner-modal-errors';
                notice.setAttribute('role', 'alert');
                const heading = document.createElement('strong');
                heading.textContent = 'اطلاعات نیاز به اصلاح دارد';
                notice.append(heading);
                const list = document.createElement('ul');
                messages.forEach((message) => {
                    const item = document.createElement('li');
                    item.textContent = String(message);
                    list.append(item);
                });
                notice.append(list);
                form.prepend(notice);
                notice.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                return;
            }

            if (!response.ok) throw new Error('modal-submit-failed');

            if (response.redirected && isSameOrigin(response.url)) {
                window.location.assign(response.url);
                return;
            }

            const payload = response.headers.get('content-type')?.includes('application/json')
                ? await response.json()
                : null;
            if (payload?.redirect && isSameOrigin(payload.redirect)) {
                window.location.assign(payload.redirect);
                return;
            }

            dialog.close();
            window.location.reload();
        } catch {
            const notice = document.createElement('div');
            notice.dataset.ownerModalErrors = '';
            notice.className = 'owner-modal-errors';
            notice.setAttribute('role', 'alert');
            notice.textContent = 'ذخیره اطلاعات انجام نشد. اتصال را بررسی کن و دوباره تلاش کن.';
            form.prepend(notice);
        } finally {
            if (submitButton && submitButton.isConnected && !dialog.querySelector('[data-owner-modal-errors]')) {
                submitButton.disabled = false;
                submitButton.textContent = originalText || 'ذخیره';
            } else if (submitButton && submitButton.isConnected) {
                submitButton.disabled = false;
                submitButton.textContent = originalText || 'ذخیره';
            }
        }
    });

    document.addEventListener('close', (event) => {
        const dialog = event.target;
        if (!(dialog instanceof HTMLDialogElement) || !dialog.classList.contains('owner-floating-modal')) return;
        const returnFocus = dialog._ownerModalReturnFocus;
        if (returnFocus instanceof HTMLElement && returnFocus.isConnected) {
            returnFocus.focus({ preventScroll: true });
        }
        dialog._ownerModalReturnFocus = null;
    }, true);

    const reopen = document.querySelector('[data-owner-record-modal-reopen-url]');
    if (reopen) {
        const url = reopen.dataset.ownerRecordModalReopenUrl;
        const title = reopen.dataset.ownerRecordModalTitle || 'اصلاح اطلاعات';
        const trigger = document.createElement('a');
        trigger.href = url;
        trigger.dataset.ownerModalUrl = url;
        trigger.dataset.ownerModalTitle = title;
        trigger.hidden = true;
        document.body.append(trigger);
        trigger.click();
        trigger.remove();
    }
})();
