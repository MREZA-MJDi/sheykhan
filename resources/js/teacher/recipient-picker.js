/* Searchable, bounded recipient selectors for teacher messaging. */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-teacher-recipient-picker]').forEach((root) => {
        const endpoint = root.dataset.endpoint;
        const context = root.dataset.context || 'direct';
        const typeInput = root.querySelector('[data-recipient-type]');
        const queryInput = root.querySelector('[data-recipient-query]');
        const results = root.querySelector('[data-recipient-results]');
        const status = root.querySelector('[data-recipient-status]');
        const moreButton = root.querySelector('[data-recipient-more]');

        if (!endpoint || !typeInput || !queryInput || !results || !status || !moreButton) return;

        let timer = 0;
        let page = 1;
        let hasMore = false;
        let sequence = 0;
        let activeRequest = null;

        const clearDynamicOptions = () => {
            results.querySelectorAll('[data-dynamic-recipient]').forEach((option) => option.remove());
        };

        const setStatus = (message) => { status.textContent = message; };

        const search = async (append = false) => {
            const term = queryInput.value.trim();
            if (term.length < 2) {
                clearDynamicOptions();
                results.value = '';
                moreButton.hidden = true;
                setStatus('برای جست‌وجو، دست‌کم دو حرف از نام مخاطب را وارد کن.');
                return;
            }

            if (activeRequest) activeRequest.abort();
            activeRequest = new AbortController();
            const requestSequence = ++sequence;
            const requestedPage = append ? page + 1 : 1;
            moreButton.disabled = true;
            setStatus(append ? 'در حال دریافت ادامهٔ فهرست…' : 'در حال جست‌وجوی مخاطب‌ها…');

            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('context', context);
            url.searchParams.set('type', typeInput.value);
            url.searchParams.set('q', term);
            url.searchParams.set('page', String(requestedPage));

            try {
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: activeRequest.signal,
                });
                if (!response.ok) throw new Error('recipient-search-failed');

                const payload = await response.json();
                if (requestSequence !== sequence) return;

                if (!append) clearDynamicOptions();
                const records = Array.isArray(payload.data) ? payload.data : [];
                records.forEach((recipient) => {
                    const option = document.createElement('option');
                    option.value = recipient.value;
                    option.textContent = recipient.label;
                    option.dataset.dynamicRecipient = '1';
                    results.append(option);
                });

                page = requestedPage;
                hasMore = Boolean(payload.has_more);
                moreButton.hidden = !hasMore;
                moreButton.disabled = false;

                const shown = results.querySelectorAll('[data-dynamic-recipient]').length;
                setStatus(shown
                    ? String(shown) + ' مخاطب نمایش داده شده' + (hasMore ? ' است؛ برای دیدن نتایج بعدی، دکمهٔ زیر را بزن.' : ' است.')
                    : 'مخاطبی با این عبارت پیدا نشد. عبارت دیگری را امتحان کن.');
            } catch (error) {
                if (error?.name === 'AbortError' || requestSequence !== sequence) return;
                moreButton.disabled = false;
                moreButton.hidden = true;
                setStatus('جست‌وجوی مخاطب انجام نشد. اتصال را بررسی کن و دوباره تلاش کن.');
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
            clearDynamicOptions();
            results.value = '';
            moreButton.hidden = true;
            moreButton.disabled = false;
        };

        queryInput.addEventListener('input', () => {
            reset();
            const term = queryInput.value.trim();
            setStatus(term.length < 2
                ? 'برای جست‌وجو، دست‌کم دو حرف از نام مخاطب را وارد کن.'
                : 'جست‌وجو به‌زودی آغاز می‌شود…');
            if (term.length >= 2) timer = window.setTimeout(() => search(false), 300);
        });

        typeInput.addEventListener('change', () => {
            reset();
            queryInput.value = '';
            setStatus('نوع مخاطب را انتخاب کردی؛ حالا نام او را جست‌وجو کن.');
        });

        moreButton.addEventListener('click', () => {
            if (hasMore) search(true);
        });
    });
});
