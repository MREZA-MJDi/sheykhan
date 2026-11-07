/* Searchable academy-scoped selectors for large owner forms. */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-academy-option-picker]').forEach((root) => {
        const endpoint = root.dataset.endpoint;
        const type = root.dataset.type;
        const search = root.querySelector('[data-academy-option-search]');
        const select = root.querySelector('[data-academy-option-select]');
        const status = root.querySelector('[data-academy-option-status]');
        const more = root.querySelector('[data-academy-option-more]');
        const form = root.closest('form');
        const courseSelect = form?.querySelector('[name="course_id"]');

        if (!endpoint || !type || !search || !select || !status || !more) return;

        let timer = 0;
        let page = 1;
        let hasMore = false;
        let sequence = 0;
        let activeRequest = null;

        const clearDynamic = () => {
            select.querySelectorAll('[data-dynamic-academy-option]').forEach((option) => option.remove());
        };

        const load = async (append = false) => {
            const term = search.value.trim();
            if (term.length < 2) {
                status.textContent = 'برای جست‌وجو، دست‌کم دو حرف وارد کن.';
                return;
            }

            if (activeRequest) activeRequest.abort();
            activeRequest = new AbortController();
            const requestSequence = ++sequence;
            const requestedPage = append ? page + 1 : 1;
            more.disabled = true;
            status.textContent = append ? 'در حال دریافت گزینه‌های بعدی…' : 'در حال جست‌وجو…';

            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('type', type);
            url.searchParams.set('q', term);
            url.searchParams.set('page', String(requestedPage));
            if (root.dataset.dependsOnCourse === 'true' && courseSelect?.value) {
                url.searchParams.set('course_id', courseSelect.value);
            }

            try {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: activeRequest.signal,
                });
                if (!response.ok) throw new Error('academy-options-failed');
                const payload = await response.json();
                if (requestSequence !== sequence) return;

                if (!append) clearDynamic();
                const records = Array.isArray(payload.data) ? payload.data : [];
                records.forEach((record) => {
                    const option = document.createElement('option');
                    option.value = String(record.id);
                    option.textContent = String(record.label);
                    option.dataset.dynamicAcademyOption = '1';
                    select.append(option);
                });

                page = requestedPage;
                hasMore = Boolean(payload.has_more);
                more.hidden = !hasMore;
                more.disabled = false;

                const total = select.querySelectorAll('[data-dynamic-academy-option]').length;
                status.textContent = total
                    ? String(total) + ' گزینه نمایش داده شد' + (hasMore ? '؛ برای ادامه، دکمهٔ پایین را بزن.' : '.')
                    : 'نتیجه‌ای پیدا نشد. عبارت دیگری را امتحان کن.';
            } catch (error) {
                if (error?.name === 'AbortError' || requestSequence !== sequence) return;
                more.disabled = false;
                more.hidden = true;
                status.textContent = 'دریافت گزینه‌ها انجام نشد؛ دوباره تلاش کن.';
            } finally {
                if (requestSequence === sequence) activeRequest = null;
            }
        };

        const reset = () => {
            window.clearTimeout(timer);
            if (activeRequest) activeRequest.abort();
            sequence++;
            page = 1;
            hasMore = false;
            clearDynamic();
            more.hidden = true;
            more.disabled = false;
        };

        search.addEventListener('input', () => {
            reset();
            const term = search.value.trim();
            status.textContent = term.length < 2
                ? 'برای جست‌وجو، دست‌کم دو حرف وارد کن.'
                : 'جست‌وجو به‌زودی آغاز می‌شود…';
            if (term.length >= 2) timer = window.setTimeout(() => load(false), 300);
        });

        more.addEventListener('click', () => {
            if (hasMore) load(true);
        });

        if (root.dataset.dependsOnCourse === 'true') {
            courseSelect?.addEventListener('change', () => {
                search.value = '';
                reset();
                status.textContent = 'برای دیدن گزینه‌های این دوره، نام یا بخشی از عنوان را جست‌وجو کن.';
            });
        }

        if (type.endsWith('courses')) {
            select.addEventListener('change', () => {
                form?.querySelectorAll('[data-academy-option-picker][data-depends-on-course="true"]').forEach((dependent) => {
                    const dependentSearch = dependent.querySelector('[data-academy-option-search]');
                    const dependentSelect = dependent.querySelector('[data-academy-option-select]');
                    const dependentMore = dependent.querySelector('[data-academy-option-more]');
                    const dependentStatus = dependent.querySelector('[data-academy-option-status]');
                    dependentSearch.value = '';
                    dependentSelect.querySelectorAll('[data-dynamic-academy-option]').forEach((option) => option.remove());
                    const placeholder = dependentSelect.querySelector('[data-option-placeholder]');
                    dependentSelect.replaceChildren(...(placeholder ? [placeholder.cloneNode(true)] : []));
                    if (dependentMore) dependentMore.hidden = true;
                    if (dependentStatus) dependentStatus.textContent = 'دوره تغییر کرد؛ گزینهٔ مرتبط را دوباره جست‌وجو کن.';
                });
            });
        }
    });
});
