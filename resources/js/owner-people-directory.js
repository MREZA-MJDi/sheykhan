/* Shared JSON request contract for the member directory and async pickers. */
const fetchJson = async (url, signal) => {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        signal,
    });
    if (!response.ok) throw new Error('request-failed');
    return response.json();
};

/* Owner people directory: accessible server-side search and numbered page navigation. */
const bootOwnerPeopleDirectory = (scope = document) => {
    'use strict';

    const roots = Array.from(scope.querySelectorAll('[data-owner-people-directory]'))
        .filter((root) => root.dataset.ownerPeopleDirectoryBound !== 'true');
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
                const payload = await fetchJson(makeUrl(page), controller.signal);
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
        root.dataset.ownerPeopleDirectoryBound = 'true';
    });
};

bootOwnerPeopleDirectory();
window.addEventListener('owner:modal:mounted', (event) => {
    const scope = event.detail?.root;
    if (scope) bootOwnerPeopleDirectory(scope);
});


/* Academy-scoped student suggestions: mobile, full name and privacy-safe national ID. */
const bootOwnerStudentPickers = (scope = document) => {
    'use strict';
    scope.querySelectorAll('[data-student-picker]').forEach((root) => {
        if (root.dataset.studentPickerBound === 'true') return;
        if (root.dataset.studentPickerBound === 'true') return;
        const endpoint = root.dataset.optionsUrl;
        const input = root.querySelector('[data-student-search]');
        const idInput = root.querySelector('[data-student-id]');
        const labelInput = root.querySelector('[data-student-label]');
        const results = root.querySelector('[data-student-results]');
        const status = root.querySelector('[data-student-status]');
        const more = root.querySelector('[data-student-more]');
        if (!endpoint || !input || !idInput || !labelInput || !results || !status || !more) return;

        let timer = 0;
        let page = 1;
        let lastPage = 1;
        let sequence = 0;
        let activeRequest = null;

        const clearSelection = () => {
            idInput.value = '';
            labelInput.value = '';
        };
        const clearResults = () => {
            results.replaceChildren();
            more.hidden = true;
            more.disabled = false;
            page = 1;
            lastPage = 1;
        };
        const renderOption = (student) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'flex w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3 text-start transition hover:border-indigo-300 hover:bg-indigo-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-indigo-500';
            button.setAttribute('role', 'option');
            button.setAttribute('aria-selected', 'false');
            const copy = document.createElement('span');
            copy.className = 'min-w-0';
            const name = document.createElement('strong');
            name.className = 'block truncate text-xs text-slate-800';
            name.textContent = String(student.name || 'بدون نام');
            copy.append(name);
            if (student.mobile) {
                const mobile = document.createElement('small');
                mobile.className = 'mt-1 block text-[11px] text-slate-500';
                mobile.dir = 'ltr';
                mobile.textContent = String(student.mobile);
                copy.append(mobile);
            }
            const choose = document.createElement('span');
            choose.className = 'shrink-0 text-[10px] font-bold text-indigo-700';
            choose.textContent = 'انتخاب';
            button.append(copy, choose);
            button.addEventListener('click', () => {
                idInput.value = String(student.id);
                labelInput.value = String(student.name || '');
                input.value = String(student.name || '');
                results.replaceChildren();
                more.hidden = true;
                status.textContent = 'دانش‌آموز انتخاب شد: ' + String(student.name || '') + (student.mobile ? ' — ' + String(student.mobile) : '');
            });
            results.append(button);
        };

        const load = async (append = false) => {
            const term = input.value.trim();
            if (term.length < 2) {
                clearResults();
                status.textContent = 'برای جست‌وجو حداقل دو نویسه از نام، موبایل یا ۱۰ رقم کد ملی وارد کن.';
                return;
            }
            if (activeRequest) activeRequest.abort();
            activeRequest = new AbortController();
            const requestSequence = ++sequence;
            const requestedPage = append ? page + 1 : 1;
            more.disabled = true;
            status.textContent = append ? 'در حال دریافت پیشنهادهای بعدی…' : 'در حال جست‌وجوی دانش‌آموزان…';
            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('q', term);
            url.searchParams.set('page', String(requestedPage));
            try {
                const payload = await fetchJson(url, activeRequest.signal);
                if (requestSequence !== sequence) return;
                const records = Array.isArray(payload.data) ? payload.data : [];
                const meta = payload.meta || {};
                if (!append) results.replaceChildren();
                records.forEach(renderOption);
                page = Number(meta.current_page || requestedPage);
                lastPage = Math.max(1, Number(meta.last_page || 1));
                more.hidden = page >= lastPage;
                more.disabled = false;
                const shown = results.querySelectorAll('[role=option]').length;
                status.textContent = shown
                    ? shown + ' پیشنهاد از ' + Number(meta.total || shown) + ' دانش‌آموز نمایش داده شد؛ برای انتخاب روی نام بزن.'
                    : 'دانش‌آموزی با این مشخصات پیدا نشد. عبارت دیگری را امتحان کن.';
            } catch (error) {
                if (error?.name === 'AbortError' || requestSequence !== sequence) return;
                more.disabled = false;
                more.hidden = true;
                status.textContent = 'جست‌وجو انجام نشد؛ اتصال را بررسی کن و دوباره تلاش کن.';
            } finally {
                if (requestSequence === sequence) activeRequest = null;
            }
        };

        input.addEventListener('input', () => {
            window.clearTimeout(timer);
            if (activeRequest) activeRequest.abort();
            sequence++;
            clearSelection();
            clearResults();
            const term = input.value.trim();
            status.textContent = term.length < 2
                ? 'برای جست‌وجو حداقل دو نویسه از نام، موبایل یا ۱۰ رقم کد ملی وارد کن.'
                : 'جست‌وجو به‌زودی آغاز می‌شود…';
            if (term.length >= 2) timer = window.setTimeout(() => load(false), 250);
        });
        more.addEventListener('click', () => {
            if (page < lastPage) load(true);
        });
        root.closest('form')?.addEventListener('submit', (event) => {
            if (!idInput.value) {
                event.preventDefault();
                input.setAttribute('aria-invalid', 'true');
                status.textContent = 'لطفاً ابتدا یک دانش‌آموز را از نتایج انتخاب کن.';
                input.focus();
            }
        });
        root.dataset.studentPickerBound = 'true';
    });
};

bootOwnerStudentPickers();
window.addEventListener('owner:modal:mounted', (event) => {
    const scope = event.detail?.root;
    if (scope) bootOwnerStudentPickers(scope);
});

/* One-time credentials: copy only on explicit user action. */
document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy-value]');
    if (!button) return;
    const value = button.getAttribute('data-copy-value') || '';
    if (!value) return;
    const original = button.textContent;
    try {
        if (navigator.clipboard?.writeText) {
            await navigator.clipboard.writeText(value);
        } else {
            const field = document.createElement('textarea');
            field.value = value;
            field.setAttribute('readonly', '');
            field.style.position = 'fixed';
            field.style.opacity = '0';
            document.body.append(field);
            field.select();
            const copied = document.execCommand('copy');
            field.remove();
            if (!copied) throw new Error('copy-failed');
        }
        button.textContent = 'کپی شد';
    } catch {
        button.textContent = 'کپی خودکار در دسترس نیست';
    }
    window.setTimeout(() => { button.textContent = original; }, 1800);
});

/* Large academy form option lists are searched and paginated on the server. */
const bootOwnerLookupPickers = (scope = document) => {
    'use strict';
    scope.querySelectorAll('[data-lookup-picker]').forEach((root) => {
        if (root.dataset.lookupPickerBound === 'true') return;
        const endpoint = root.dataset.optionsUrl;
        const type = root.dataset.lookupType;
        const input = root.querySelector('[data-lookup-search]');
        const valueInput = root.querySelector('[data-lookup-value]');
        const results = root.querySelector('[data-lookup-results]');
        const status = root.querySelector('[data-lookup-status]');
        const more = root.querySelector('[data-lookup-more]');
        const courseInputId = root.dataset.courseInput;
        const courseInput = courseInputId ? document.getElementById(courseInputId) : null;
        if (!endpoint || !type || !input || !valueInput || !results || !status || !more) return;

        let timer = 0;
        let sequence = 0;
        let page = 1;
        let lastPage = 1;
        let activeRequest = null;

        const render = (items, append) => {
            if (!append) results.replaceChildren();
            items.forEach((item) => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'flex w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3 text-start text-sm hover:border-indigo-300 hover:bg-indigo-50';
                option.setAttribute('role', 'option');
                option.textContent = String(item.label || 'بدون عنوان');
                option.addEventListener('click', () => {
                    valueInput.value = String(item.id);
                    input.value = String(item.label || '');
                    results.replaceChildren();
                    more.hidden = true;
                    status.textContent = 'انتخاب شد: ' + input.value;
                    valueInput.dispatchEvent(new Event('change', { bubbles: true }));
                });
                results.append(option);
            });
        };

        const load = async (nextPage = 1, append = false) => {
            const term = input.value.trim();
            if (term.length < 2) {
                results.replaceChildren();
                more.hidden = true;
                status.textContent = 'برای جست‌وجو حداقل دو نویسه وارد کن.';
                return;
            }
            if (courseInputId && !courseInput?.value) {
                results.replaceChildren();
                more.hidden = true;
                status.textContent = 'ابتدا دوره را جست‌وجو و انتخاب کن.';
                return;
            }
            activeRequest?.abort();
            const controller = new AbortController();
            activeRequest = controller;
            const current = ++sequence;
            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('q', term);
            url.searchParams.set('page', String(nextPage));
            if (courseInput?.value) url.searchParams.set('course_id', courseInput.value);
            status.textContent = 'در حال جست‌وجو…';
            more.disabled = true;
            try {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error('lookup-failed');
                const payload = await response.json();
                if (current !== sequence) return;
                const items = Array.isArray(payload.data) ? payload.data : [];
                page = Number(payload.meta?.current_page ?? nextPage);
                lastPage = Number(payload.meta?.last_page ?? 1);
                render(items, append);
                more.hidden = page >= lastPage || items.length === 0;
                status.textContent = items.length
                    ? (Number(payload.meta?.total ?? items.length) + ' نتیجه؛ صفحه ' + page + ' از ' + lastPage)
                    : 'نتیجه‌ای پیدا نشد.';
            } catch (error) {
                if (error?.name !== 'AbortError' && current === sequence) {
                    status.textContent = 'جست‌وجو انجام نشد؛ دوباره تلاش کن.';
                    more.hidden = true;
                }
            } finally {
                if (current === sequence) {
                    activeRequest = null;
                    more.disabled = false;
                }
            }
        };

        input.addEventListener('input', () => {
            valueInput.value = '';
            input.removeAttribute('aria-invalid');
            window.clearTimeout(timer);
            timer = window.setTimeout(() => load(1, false), 250);
        });
        more.addEventListener('click', () => {
            if (page < lastPage) load(page + 1, true);
        });
        courseInput?.addEventListener('change', () => {
            valueInput.value = '';
            input.value = '';
            results.replaceChildren();
            more.hidden = true;
            status.textContent = 'حالا کلاس مربوط به این دوره را جست‌وجو کن.';
        });

        root.closest('form')?.addEventListener('submit', (event) => {
            if (!valueInput.value) {
                event.preventDefault();
                input.setAttribute('aria-invalid', 'true');
                status.textContent = 'لطفاً ابتدا یکی از گزینه‌های فهرست را انتخاب کن.';
                input.focus();
            } else {
                input.removeAttribute('aria-invalid');
            }
        });
        root.dataset.lookupPickerBound = 'true';
    });
};

bootOwnerLookupPickers();
window.addEventListener('owner:modal:mounted', (event) => {
    const scope = event.detail?.root;
    if (scope) bootOwnerLookupPickers(scope);
});

