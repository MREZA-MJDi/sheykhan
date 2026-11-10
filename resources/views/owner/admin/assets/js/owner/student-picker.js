/* Academy-scoped student suggestions: mobile, full name and privacy-safe national ID. */
(() => {
    'use strict';
    document.querySelectorAll('[data-student-picker]').forEach((root) => {
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
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: activeRequest.signal,
                });
                if (!response.ok) throw new Error('student-suggestions-failed');
                const payload = await response.json();
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
})();
