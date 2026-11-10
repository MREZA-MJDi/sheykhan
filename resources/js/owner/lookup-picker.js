/* Large academy form option lists are searched and paginated on the server. */
(() => {
    'use strict';
    document.querySelectorAll('[data-lookup-picker]').forEach((root) => {
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
    });
})();
