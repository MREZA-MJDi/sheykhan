/* Owner people directory: progressive enhancement over the server-rendered roster. */
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

    const createRow = (person, role) => {
        const row = document.createElement('div');
        row.className = 'flex items-center justify-between gap-3 rounded-xl bg-slate-50 p-3';

        const details = document.createElement('div');
        details.className = 'min-w-0';

        const name = document.createElement('strong');
        name.className = 'text-xs';
        name.textContent = String(person.name ?? 'بدون نام');
        details.append(name);

        if (role !== 'students' && person.email) {
            const email = document.createElement('span');
            email.className = 'mt-1 block break-all text-[9px] text-slate-500';
            email.textContent = String(person.email);
            details.append(email);
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
        const resetButton = root.querySelector('[data-directory-reset]');
        const resultBox = root.querySelector('[data-directory-results]');
        const status = root.querySelector('[data-directory-status]');
        const count = root.querySelector('[data-directory-count]');
        const moreButton = root.querySelector('[data-directory-more]');
        const serverPagination = root.querySelector('[data-directory-server-pagination]');

        if (!baseUrl || !role || !queryInput || !sortSelect || !directionSelect || !resultBox || !status || !count) {
            return;
        }

        let activeRequest = null;
        let requestId = 0;
        let debounceHandle = 0;
        let nextCursor = null;
        let currentPage = 1;
        let hasMore = false;
        let paginationMode = 'cursor';
        let loadedCount = 0;

        const setStatus = (message) => {
            status.textContent = message;
        };

        const makeUrl = (append) => {
            const url = new URL(baseUrl, window.location.origin);
            const params = url.searchParams;
            const query = queryInput.value.trim();
            const sort = sortSelect.value || 'name';
            const direction = directionSelect.value || 'asc';

            params.set('per_page', '25');
            params.set('sort', sort);
            params.set('direction', direction);
            if (query) params.set('q', query);
            else params.delete('q');

            if (append && paginationMode === 'cursor' && nextCursor) {
                params.set('cursor', nextCursor);
                params.delete('page');
            } else if (append && paginationMode === 'page') {
                params.set('page', String(currentPage + 1));
                params.delete('cursor');
            } else {
                params.delete('cursor');
                params.delete('page');
            }

            return url.toString();
        };

        const load = async ({ append = false } = {}) => {
            if (activeRequest) activeRequest.abort();
            const controller = new AbortController();
            activeRequest = controller;
            const currentRequestId = ++requestId;

            if (!append) {
                nextCursor = null;
                currentPage = 1;
                loadedCount = 0;
            }

            if (moreButton) moreButton.disabled = true;
            setStatus('در حال دریافت فهرست از سرور…');

            try {
                const response = await fetch(makeUrl(append), {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error('directory-request-failed');
                }

                const payload = await response.json();
                if (currentRequestId !== requestId) return;

                const records = Array.isArray(payload.data) ? payload.data : [];
                const meta = payload.meta ?? {};
                const directory = payload.directory ?? {};

                paginationMode = directory.pagination_mode === 'page' ? 'page' : 'cursor';
                nextCursor = meta.next_cursor ?? null;
                currentPage = Number(meta.current_page ?? currentPage);
                hasMore = paginationMode === 'cursor'
                    ? Boolean(nextCursor)
                    : currentPage < Number(meta.last_page ?? currentPage);

                if (!append) resultBox.replaceChildren();

                if (!records.length && !append) {
                    resultBox.append(createEmptyState('عضوی مطابق این جست‌وجو پیدا نشد.'));
                } else {
                    records.forEach((person) => resultBox.append(createRow(person, role)));
                }

                loadedCount += records.length;
                count.textContent = hasMore ? String(loadedCount) + '+' : String(loadedCount);

                if (serverPagination) serverPagination.hidden = true;
                if (moreButton) {
                    moreButton.hidden = !hasMore;
                    moreButton.disabled = false;
                }

                setStatus(
                    loadedCount
                        ? (hasMore
                            ? 'حداقل ' + loadedCount + ' نتیجه نمایش داده شده؛ برای دیدن ادامه از دکمه پایین استفاده کن.'
                            : loadedCount + ' نتیجه نمایش داده شد.')
                        : 'عضوی مطابق این جست‌وجو پیدا نشد.'
                );
            } catch (error) {
                if (error?.name === 'AbortError' || currentRequestId !== requestId) return;
                if (moreButton) moreButton.disabled = false;
                setStatus('فهرست آنلاین بارگذاری نشد. فهرست سرور و پیوندهای صفحه‌بندی در دسترس می‌مانند.');
            } finally {
                if (currentRequestId === requestId) activeRequest = null;
            }
        };

        queryInput.addEventListener('input', () => {
            window.clearTimeout(debounceHandle);
            debounceHandle = window.setTimeout(() => load(), 250);
        });

        sortSelect.addEventListener('change', () => load());
        directionSelect.addEventListener('change', () => load());

        resetButton?.addEventListener('click', () => {
            queryInput.value = '';
            sortSelect.value = 'name';
            directionSelect.value = 'asc';
            load();
        });

        moreButton?.addEventListener('click', () => {
            if (!hasMore) return;
            load({ append: true });
        });
    });
})();
