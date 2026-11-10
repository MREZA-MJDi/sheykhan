/* Owner people directory: accessible server-side search and numbered page navigation. */
(() => {
    'use strict';

    const roots = Array.from(document.querySelectorAll('[data-owner-people-directory]'));
    if (!roots.length) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const previewDialog = document.querySelector('[data-owner-person-preview-dialog]');
    const previewRoleLabels = {'active-teachers':'مدرس','archived-teachers':'مدرس آرشیوشده',students:'دانش‌آموز',parents:'والد'};
    const setPreviewText = (selector, value, fallback = 'ثبت نشده') => {
        const node = previewDialog?.querySelector(selector);
        if (node) node.textContent = value || fallback;
    };
    const formatPreviewDate = (value) => {
        if (!value) return 'ثبت نشده';
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? 'ثبت نشده' : new Intl.DateTimeFormat('fa-IR', {dateStyle:'medium'}).format(date);
    };
    const closePreview = () => {
        if (!previewDialog) return;
        if (typeof previewDialog.close === 'function') previewDialog.close();
        else previewDialog.removeAttribute('open');
    };
    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-owner-person-preview-close]') && previewDialog) { closePreview(); return; }
        const trigger = event.target.closest('[data-owner-person-preview]');
        if (!trigger || !previewDialog) return;
        const account = trigger.dataset.personAccountStatus;
        const membership = trigger.dataset.personMembershipStatus;
        setPreviewText('[data-preview-name]', trigger.dataset.personName, 'بدون نام');
        setPreviewText('[data-preview-role]', previewRoleLabels[trigger.dataset.personRole] || 'عضو');
        setPreviewText('[data-preview-mobile]', trigger.dataset.personMobile);
        setPreviewText('[data-preview-account-status]', account === 'active' ? 'فعال' : account === 'inactive' ? 'غیرفعال' : 'ثبت نشده');
        setPreviewText('[data-preview-membership-status]', membership === 'active' ? 'عضو فعال' : membership === 'archived' ? 'آرشیوشده' : 'ثبت نشده');
        setPreviewText('[data-preview-joined-at]', formatPreviewDate(trigger.dataset.personJoinedAt));
        if (typeof previewDialog.showModal === 'function') previewDialog.showModal();
        else previewDialog.setAttribute('open', '');
    });
    previewDialog?.addEventListener('click', event => { if (event.target === previewDialog) closePreview(); });
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

    const createPreviewButton = (person, role) => {
        const button = document.createElement('button');
        button.type = 'button'; button.className = 'owner-person-preview-trigger'; button.textContent = 'پیش‌نمایش';
        button.setAttribute('aria-haspopup', 'dialog'); button.setAttribute('data-owner-person-preview', '');
        button.dataset.personName = String(person.name ?? '');
        button.dataset.personMobile = String(person.mobile ?? '');
        button.dataset.personRole = role;
        button.dataset.personAccountStatus = String(person.account_status ?? '');
        button.dataset.personMembershipStatus = String(person.membership?.status ?? '');
        button.dataset.personJoinedAt = String(person.membership?.joined_at ?? '');
        button.setAttribute('aria-label', 'پیش‌نمایش پرونده ' + String(person.name ?? 'عضو'));
        return button;
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

        if (person.mobile) {
            const mobile = document.createElement('span');
            mobile.className = 'mt-1 block text-[11px] text-slate-500';
            mobile.dir = 'ltr';
            mobile.textContent = String(person.mobile);
            details.append(mobile);
        }
        const actions = document.createElement('div');
        actions.className = 'flex shrink-0 items-center gap-2';
        const archiveForm = createMutationForm(person.actions?.archive_url, 'آرشیو عضویت', 'archive');
        const restoreForm = createMutationForm(person.actions?.restore_url, 'بازگردانی', 'restore');
        if (archiveForm) actions.append(archiveForm);
        else if (restoreForm) actions.append(restoreForm);
        actions.append(createPreviewButton(person, role));
        row.append(details, actions);
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
        let paginationMode = 'page';
        let nextCursor = null;
        let cursorByPage = new Map([[1, null]]);

        const setStatus = (message) => { status.textContent = message; };

        const makeUrl = (page, cursor = null) => {
            const url = new URL(baseUrl, window.location.origin);
            const params = url.searchParams;
            const query = queryInput.value.trim();
            params.set('per_page', String(perPageSelect?.value || '25'));
            params.set('sort', sortSelect.value || 'name');
            params.set('direction', directionSelect.value || 'asc');

            if (paginationMode === 'cursor') {
                params.delete('page');
                params.delete('cursor');
                if (cursor) params.set('cursor', cursor);
            } else {
                params.set('page', String(page));
                params.delete('cursor');
            }

            if (query) params.set('q', query);
            else params.delete('q');
            return url.toString();
        };

        const load = async (page = 1, requestedCursor = undefined) => {
            if (activeRequest) activeRequest.abort();

            if (page === 1) {
                cursorByPage = new Map([[1, null]]);
                nextCursor = null;
            }

            const cursor = requestedCursor !== undefined
                ? requestedCursor
                : (cursorByPage.get(page) ?? null);

            const controller = new AbortController();
            activeRequest = controller;
            const currentRequestId = ++requestId;

            if (previousButton) previousButton.disabled = true;
            if (nextButton) nextButton.disabled = true;
            setStatus('در حال دریافت فهرست از سرور…');

            try {
                const response = await fetch(makeUrl(page, cursor), {
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
                const directory = payload.directory ?? {};
                paginationMode = directory.pagination_mode === 'cursor' ? 'cursor' : 'page';
                currentPage = Number(meta.current_page ?? page);
                lastPage = Math.max(1, Number(meta.last_page ?? currentPage));
                nextCursor = typeof meta.next_cursor === 'string' && meta.next_cursor !== ''
                    ? meta.next_cursor
                    : null;

                cursorByPage.set(currentPage, cursor || null);
                if (paginationMode === 'cursor' && nextCursor) {
                    cursorByPage.set(currentPage + 1, nextCursor);
                }

                resultBox.replaceChildren();
                if (!records.length) resultBox.append(createEmptyState('عضوی مطابق این جست‌وجو پیدا نشد.'));
                else records.forEach((person) => resultBox.append(createRow(person, role)));

                if (serverPagination) serverPagination.hidden = true;

                const hasPrevious = currentPage > 1 && (
                    paginationMode !== 'cursor' || cursorByPage.has(currentPage - 1)
                );
                const hasNext = paginationMode === 'cursor'
                    ? Boolean(nextCursor)
                    : currentPage < lastPage;

                if (pagination) pagination.hidden = !hasPrevious && !hasNext;
                if (previousButton) previousButton.disabled = !hasPrevious;
                if (nextButton) nextButton.disabled = !hasNext;

                if (paginationMode === 'cursor') {
                    if (meta.total !== undefined && meta.total !== null) {
                        count.textContent = String(meta.total);
                    } else {
                        count.textContent = String(records.length) + (hasNext ? '+' : '');
                    }
                    if (pageInfo) {
                        pageInfo.textContent = 'صفحه ' + currentPage + ' · ' + records.length + ' عضو در این صفحه';
                    }
                    setStatus(records.length
                        ? 'صفحه ' + currentPage + ' از فهرست اعضا نمایش داده شد.'
                        : 'عضوی مطابق این جست‌وجو پیدا نشد.');
                } else {
                    const total = Number(meta.total ?? records.length);
                    count.textContent = String(total);
                    if (pageInfo) pageInfo.textContent = 'صفحه ' + currentPage + ' از ' + lastPage + ' · ' + total + ' عضو';
                    setStatus(records.length
                        ? 'نتایج صفحه ' + currentPage + ' از ' + lastPage + ' نمایش داده شد.'
                        : 'عضوی مطابق این جست‌وجو پیدا نشد.');
                }
            } catch (error) {
                if (error?.name === 'AbortError' || currentRequestId !== requestId) return;
                if (previousButton) previousButton.disabled = currentPage <= 1;
                if (nextButton) nextButton.disabled = paginationMode === 'cursor' ? !nextCursor : currentPage >= lastPage;
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
            if (perPageSelect) perPageSelect.value = '25';
            load(1);
        });
        previousButton?.addEventListener('click', () => {
            if (currentPage <= 1) return;
            if (paginationMode === 'cursor') {
                const previousCursor = cursorByPage.get(currentPage - 1);
                if (cursorByPage.has(currentPage - 1)) load(currentPage - 1, previousCursor ?? null);
                return;
            }
            if (currentPage > 1) load(currentPage - 1);
        });
        nextButton?.addEventListener('click', () => {
            if (paginationMode === 'cursor') {
                if (nextCursor) load(currentPage + 1, nextCursor);
                return;
            }
            if (currentPage < lastPage) load(currentPage + 1);
        });
    });
})();


/* Academy-scoped student picker: small server pages, safe rendering and keyboard access. */
(() => {
    'use strict';

    document.querySelectorAll('[data-student-picker]').forEach((root, rootIndex) => {
        if (root.dataset.studentPickerBound === 'true') return;

        const endpoint = root.dataset.optionsUrl;
        const input = root.querySelector('[data-student-search]');
        const idInput = root.querySelector('[data-student-id]');
        const labelInput = root.querySelector('[data-student-label]');
        const results = root.querySelector('[data-student-results]');
        const status = root.querySelector('[data-student-status]');
        const more = root.querySelector('[data-student-more]');
        const selection = root.querySelector('[data-student-selection]');
        const selectedName = root.querySelector('[data-student-selected-name]');
        const selectedMobile = root.querySelector('[data-student-selected-mobile]');
        const clearButton = root.querySelector('[data-student-clear]');
        const browseOnOpen = root.dataset.studentInitialBrowse === 'true';

        if (!endpoint || !input || !idInput || !labelInput || !results || !status || !more) return;

        const minimumSearchLength = 2;
        let timer = 0;
        let page = 1;
        let lastPage = 1;
        let sequence = 0;
        let activeRequest = null;

        if (!results.id) results.id = 'student-picker-results-' + (rootIndex + 1);

        const setStatus = (message) => { status.textContent = message; };

        const clearSelection = () => {
            idInput.value = '';
            labelInput.value = '';
            if (selection) selection.hidden = true;
            if (selectedName) selectedName.textContent = '';
            if (selectedMobile) selectedMobile.textContent = '';
        };

        const clearResults = () => {
            results.replaceChildren();
            results.hidden = true;
            more.hidden = true;
            more.disabled = false;
            page = 1;
            lastPage = 1;
            input.setAttribute('aria-expanded', 'false');
        };

        const chooseStudent = (student) => {
            idInput.value = String(student.id);
            labelInput.value = String(student.name || '');
            input.value = String(student.name || '');
            if (selectedName) selectedName.textContent = String(student.name || 'بدون نام');
            if (selectedMobile) {
                selectedMobile.textContent = student.mobile ? String(student.mobile) : 'شماره موبایل ثبت نشده';
                selectedMobile.dir = 'ltr';
            }
            if (selection) selection.hidden = false;
            clearResults();
            setStatus('دانش‌آموز انتخاب شد. برای تغییر، عبارت دیگری جست‌وجو کن یا «تغییر انتخاب» را بزن.');
        };

        const createEmptyState = (message) => {
            const empty = document.createElement('div');
            empty.className = 'owner-student-picker__empty';
            empty.textContent = message;
            return empty;
        };

        const moveOptionFocus = (current, direction) => {
            const options = Array.from(results.querySelectorAll('[role="option"]'));
            if (!options.length) return;
            const index = options.indexOf(current);
            const next = Math.max(0, Math.min(options.length - 1, index + direction));
            options[next]?.focus();
        };

        const renderOption = (student) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'owner-student-picker__option';
            button.setAttribute('role', 'option');
            button.setAttribute('aria-selected', String(String(student.id) === idInput.value));
            button.setAttribute('aria-label', 'انتخاب ' + String(student.name || 'دانش‌آموز'));
            button.id = results.id + '-option-' + String(student.id);

            const copy = document.createElement('span');
            copy.className = 'owner-student-picker__option-copy';

            const name = document.createElement('strong');
            name.className = 'owner-student-picker__option-name';
            name.textContent = String(student.name || 'بدون نام');
            copy.append(name);

            const meta = document.createElement('span');
            meta.className = 'owner-student-picker__option-meta';
            meta.dir = 'ltr';
            meta.textContent = String(student.mobile || 'شماره موبایل ثبت نشده');
            copy.append(meta);

            const action = document.createElement('span');
            action.className = 'owner-student-picker__option-action';
            action.textContent = 'انتخاب';
            button.append(copy, action);
            button.addEventListener('click', () => chooseStudent(student));
            button.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    moveOptionFocus(button, 1);
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    const options = Array.from(results.querySelectorAll('[role="option"]'));
                    if (options[0] === button) input.focus();
                    else moveOptionFocus(button, -1);
                } else if (event.key === 'Escape') {
                    event.preventDefault();
                    clearResults();
                    input.focus();
                }
            });
            results.append(button);
        };

        const load = async (append = false, browse = false) => {
            const term = input.value.trim();
            const canBrowse = browse || (browseOnOpen && term.length === 0);

            if (term.length < minimumSearchLength && !canBrowse) {
                clearResults();
                setStatus('برای جست‌وجو حداقل دو نویسه از نام یا شماره موبایل وارد کن.');
                return;
            }

            if (activeRequest) activeRequest.abort();

            const controller = new AbortController();
            activeRequest = controller;
            const requestSequence = ++sequence;
            const requestedPage = append ? page + 1 : 1;

            more.disabled = true;
            if (!append) results.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            setStatus(append ? 'در حال دریافت صفحه بعدی…' : 'در حال دریافت دانش‌آموزان فعال…');

            const url = new URL(endpoint, window.location.origin);
            if (term.length >= minimumSearchLength) url.searchParams.set('q', term);
            else url.searchParams.delete('q');
            url.searchParams.set('page', String(requestedPage));
            if (canBrowse) url.searchParams.set('browse', '1');

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: controller.signal,
                });

                if (!response.ok) throw new Error('student-picker-request-failed');

                const payload = await response.json();
                if (requestSequence !== sequence) return;

                const records = Array.isArray(payload.data) ? payload.data : [];
                const meta = payload.meta || {};

                if (!append) results.replaceChildren();
                if (records.length) records.forEach(renderOption);
                else if (!append) {
                    results.append(createEmptyState(term.length >= minimumSearchLength
                        ? 'دانش‌آموزی با این مشخصات در فهرست فعال این آموزشگاه پیدا نشد.'
                        : 'هنوز دانش‌آموز فعالی برای نمایش وجود ندارد.'));
                }

                page = Math.max(1, Number(meta.current_page || requestedPage));
                lastPage = Math.max(1, Number(meta.last_page || 1));
                const total = Math.max(0, Number(meta.total || 0));
                more.hidden = page >= lastPage;
                more.disabled = false;
                more.textContent = 'نمایش صفحه بعد (' + (page + 1) + ' از ' + lastPage + ')';

                const shown = results.querySelectorAll('[role="option"]').length;
                setStatus(shown
                    ? 'نمایش ' + shown + ' نفر از ' + total + ' دانش‌آموز فعال؛ برای انتخاب روی نام بزن.'
                    : 'موردی برای نمایش پیدا نشد. عبارت جست‌وجو را تغییر بده.');
            } catch (error) {
                if (error?.name === 'AbortError' || requestSequence !== sequence) return;
                more.disabled = false;
                more.hidden = true;
                setStatus('فهرست دانش‌آموزان بارگذاری نشد. اتصال را بررسی کن و دوباره تلاش کن.');
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
            setStatus(term.length < minimumSearchLength
                ? 'برای جست‌وجو حداقل دو نویسه از نام یا شماره موبایل وارد کن.'
                : 'جست‌وجو به‌زودی آغاز می‌شود…');

            if (term.length >= minimumSearchLength) {
                timer = window.setTimeout(() => load(false), 250);
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' && !results.hidden) {
                const firstOption = results.querySelector('[role="option"]');
                if (firstOption) {
                    event.preventDefault();
                    firstOption.focus();
                }
            } else if (event.key === 'Escape' && !results.hidden) {
                clearResults();
            }
        });

        more.addEventListener('click', () => {
            if (page < lastPage) {
                load(true, browseOnOpen && input.value.trim().length < minimumSearchLength);
            }
        });

        clearButton?.addEventListener('click', () => {
            clearSelection();
            input.value = '';
            input.focus();
            if (browseOnOpen) load(false, true);
            else {
                clearResults();
                setStatus('برای انتخاب دانش‌آموز، نام یا شماره موبایل را جست‌وجو کن.');
            }
        });

        clearResults();
        root.dataset.studentPickerBound = 'true';

        if (input.value.trim().length >= minimumSearchLength) load(false);
        else if (browseOnOpen) load(false, true);
    });
})();
