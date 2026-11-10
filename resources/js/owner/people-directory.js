/* Owner people directory: accessible server-side search and numbered page navigation. */
(() => {
    'use strict';

    const roots = Array.from(document.querySelectorAll('[data-owner-people-directory]'));
    if (!roots.length) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const hiddenInput = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
    };

    const createEmptyState = (message) => {
        const empty = document.createElement('div');
        empty.className = 'rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500';
        empty.textContent = message;
        return empty;
    };

    const createMutationForm = (url, label, tone) => {
        if (!url || !csrf) return null;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.className = 'shrink-0';
        form.append(hiddenInput('_token', csrf), hiddenInput('_method', 'PATCH'));
        const button = document.createElement('button');
        button.type = 'submit';
        button.className = tone === 'restore'
            ? 'rounded-lg border border-emerald-200 bg-white px-2.5 py-2 text-[9px] font-black text-emerald-700'
            : 'rounded-lg border border-amber-200 bg-white px-2.5 py-2 text-[9px] font-black text-amber-700';
        button.textContent = label;
        form.append(button);
        return form;
    };

    const createRow = (person) => {
        const row = document.createElement('div');
        row.className = 'flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3';
        const details = document.createElement('div');
        details.className = 'min-w-0';
        const name = document.createElement('strong');
        name.className = 'text-xs';
        name.textContent = String(person.name ?? 'بدون نام');
        details.append(name);

        if (person.mobile) {
            const mobile = document.createElement('span');
            mobile.className = 'mt-1 block text-[11px] text-slate-500';
            mobile.dir = 'ltr';
            mobile.textContent = String(person.mobile);
            details.append(mobile);
        }
        row.append(details);

        const archiveForm = createMutationForm(person.actions?.archive_url, 'آرشیو عضویت', 'archive');
        const restoreForm = createMutationForm(person.actions?.restore_url, 'بازگردانی', 'restore');
        if (archiveForm) row.append(archiveForm);
        else if (restoreForm) row.append(restoreForm);
        return row;
    };

    roots.forEach((root) => {
        const baseUrl = root.dataset.directoryUrl;
        const role = root.dataset.directoryRole;
        const queryInput = root.querySelector('[data-directory-query]');
        const sortSelect = root.querySelector('[data-directory-sort]');
        const directionSelect = root.querySelector('[data-directory-direction]');
        const perPageSelect = root.querySelector('[data-directory-per-page]');
        const resetButton = root.querySelector('[data-directory-reset]');
        const resultBox = root.querySelector('[data-directory-results]');
        const status = root.querySelector('[data-directory-status]');
        const count = root.querySelector('[data-directory-count]');
        const pagination = root.querySelector('[data-directory-pagination]');
        const previousButton = root.querySelector('[data-directory-prev]');
        const nextButton = root.querySelector('[data-directory-next]');
        const pageInfo = root.querySelector('[data-directory-page-info]');
        const serverPagination = root.querySelector('[data-directory-server-pagination]');

        if (!baseUrl || !role || !queryInput || !sortSelect || !directionSelect || !resultBox || !status || !count) return;

        let activeRequest = null;
        let requestId = 0;
        let debounceHandle = 0;
        let currentPage = 1;
        let lastPage = 1;

        const setStatus = (message) => { status.textContent = message; };

        const makeUrl = (page) => {
            const url = new URL(baseUrl, window.location.origin);
            const params = url.searchParams;
            const query = queryInput.value.trim();
            params.set('per_page', String(perPageSelect?.value || '10'));
            params.set('page', String(page));
            params.set('sort', sortSelect.value || 'name');
            params.set('direction', directionSelect.value || 'asc');
            params.delete('cursor');
            if (query) params.set('q', query);
            else params.delete('q');
            return url.toString();
        };

        const load = async (page = 1) => {
            if (activeRequest) activeRequest.abort();
            const controller = new AbortController();
            activeRequest = controller;
            const currentRequestId = ++requestId;

            if (previousButton) previousButton.disabled = true;
            if (nextButton) nextButton.disabled = true;
            setStatus('در حال دریافت فهرست از سرور…');

            try {
                const response = await fetch(makeUrl(page), {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error('directory-request-failed');

                const payload = await response.json();
                if (currentRequestId !== requestId) return;

                const records = Array.isArray(payload.data) ? payload.data : [];
                const meta = payload.meta ?? {};
                currentPage = Number(meta.current_page ?? page);
                lastPage = Math.max(1, Number(meta.last_page ?? 1));
                const total = Number(meta.total ?? records.length);

                resultBox.replaceChildren();
                if (!records.length) resultBox.append(createEmptyState('عضوی مطابق این جست‌وجو پیدا نشد.'));
                else records.forEach((person) => resultBox.append(createRow(person)));

                count.textContent = String(total);
                if (serverPagination) serverPagination.hidden = true;
                if (pagination) pagination.hidden = lastPage <= 1;
                if (previousButton) previousButton.disabled = currentPage <= 1;
                if (nextButton) nextButton.disabled = currentPage >= lastPage;
                if (pageInfo) pageInfo.textContent = 'صفحه ' + currentPage + ' از ' + lastPage + ' · ' + total + ' عضو';

                setStatus(records.length
                    ? 'نتایج صفحه ' + currentPage + ' از ' + lastPage + ' نمایش داده شد.'
                    : 'عضوی مطابق این جست‌وجو پیدا نشد.');
            } catch (error) {
                if (error?.name === 'AbortError' || currentRequestId !== requestId) return;
                if (previousButton) previousButton.disabled = false;
                if (nextButton) nextButton.disabled = false;
                setStatus('فهرست آنلاین بارگذاری نشد. داده‌های سرور و پیوندهای صفحه‌بندی همچنان در دسترس‌اند.');
            } finally {
                if (currentRequestId === requestId) activeRequest = null;
            }
        };

        queryInput.addEventListener('input', () => {
            window.clearTimeout(debounceHandle);
            debounceHandle = window.setTimeout(() => load(1), 250);
        });
        sortSelect.addEventListener('change', () => load(1));
        directionSelect.addEventListener('change', () => load(1));
        perPageSelect?.addEventListener('change', () => load(1));
        resetButton?.addEventListener('click', () => {
            queryInput.value = '';
            sortSelect.value = 'name';
            directionSelect.value = 'asc';
            if (perPageSelect) perPageSelect.value = '10';
            load(1);
        });
        previousButton?.addEventListener('click', () => {
            if (currentPage > 1) load(currentPage - 1);
        });
        nextButton?.addEventListener('click', () => {
            if (currentPage < lastPage) load(currentPage + 1);
        });
    });
})();
