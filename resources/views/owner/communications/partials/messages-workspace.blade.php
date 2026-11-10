@php
    $audienceLabels = [
        'all_parents' => 'همه خانواده‌های آموزشگاه',
        'classroom_parents' => 'خانواده‌های یک کلاس',
        'parent' => 'ولی مشخص',
        'student' => 'دانش‌آموز مشخص',
    ];
    $categoryLabels = [
        'announcement' => 'اطلاعیه',
        'academic' => 'آموزشی',
        'schedule' => 'زمان‌بندی',
        'important' => 'مهم',
        'other' => 'سایر',
    ];
@endphp

<div class="space-y-6 panel-page-enter owner-workspace-page owner-communications-workspace">
    <header class="dashboard-panel overflow-hidden p-5 sm:p-7">
        <span class="inline-flex rounded-full border border-[var(--panel-primary)]/20 bg-[var(--panel-primary-soft)] px-3 py-1.5 text-xs font-black text-[var(--panel-primary)]">مرکز ارتباطات</span>
        <h1 class="mt-3 text-2xl font-black leading-relaxed text-[var(--panel-text)]">{{ $pageTitle }}</h1>
        <p class="mt-2 max-w-3xl text-sm leading-7 text-[var(--panel-muted)]">{{ $pageDescription }}</p>
        <a href="{{ $backRoute }}" class="mt-4 inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-4 text-sm font-bold">بازگشت</a>
    </header>

    @include('owner.partials.communications-nav')

    @if(session('success'))
        <div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <strong class="block font-black">پیام ارسال نشد؛ این موارد را بررسی کن.</strong>
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    @if($canManage)
    <section class="dashboard-panel p-5 sm:p-7">
        <h2 class="text-xl font-black text-[var(--panel-text)]">ارسال پیام تازه</h2>
        <p class="mt-1 text-sm leading-7 text-[var(--panel-muted)]">اطلاعیه عمومی، پیام خانواده‌های یک کلاس و پیام مستقیم، گیرنده‌های جداگانه دارند.</p>
        <form method="POST" action="{{ $storeRoute }}" class="mt-5 grid gap-4 md:grid-cols-2">
            @csrf
            <div class="grid gap-3 text-sm font-bold md:col-span-2" data-recipient-picker data-endpoint="{{ route('owner.communications.recipients', $academy) }}">
                <label class="grid gap-2">نوع مخاطب
                    <select data-recipient-type class="min-h-12 w-full rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-3">
                        <option value="students">دانش‌آموز</option>
                        <option value="parents">ولی / خانواده</option>
                    </select>
                </label>
                <label class="grid gap-2">جست‌وجوی مخاطب
                    <input type="search" data-recipient-query minlength="2" maxlength="80" autocomplete="off" class="min-h-12 w-full rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-3" placeholder="حداقل دو حرف از نام را وارد کن...">
                </label>
                <p data-recipient-status role="status" aria-live="polite" class="text-xs font-normal leading-6 text-[var(--panel-muted)]">برای دیدن مخاطب‌های خصوصی، نام دانش‌آموز یا ولی را جست‌وجو کن. حداکثر ۲۰ نتیجه در هر بار نمایش داده می‌شود.</p>
                <label class="grid gap-2">انتخاب گیرنده
                    <select name="target" data-recipient-results required size="5" class="min-h-12 w-full rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-3 py-2">
                        <option value="">انتخاب مخاطب...</option>
                        <option value="all_parents" @selected(old('target') === 'all_parents')>اطلاعیه عمومی برای همه خانواده‌های آموزشگاه</option>
                        @if(old('target') && old('target') !== 'all_parents')
                            <option value="{{ old('target') }}" selected>مخاطب انتخاب‌شده از ارسال قبلی</option>
                        @endif
                    </select>
                    @error('target')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>
                <button type="button" data-recipient-more hidden class="min-h-10 justify-self-start rounded-lg border border-[var(--panel-border)] px-4 text-xs font-bold">نمایش ۲۰ مخاطب بعدی</button>
            </div>
                <label class="grid gap-2 text-sm font-bold">نوع
                    <select name="category" required class="min-h-12 rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-3">
                        @foreach($categoryLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('category', 'announcement') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="grid gap-2 text-sm font-bold">عنوان
                    <input name="subject" value="{{ old('subject') }}" minlength="3" maxlength="180" required class="min-h-12 rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-3" placeholder="مثلاً تغییر ساعت کلاس">
                    @error('subject')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-2 text-sm font-bold md:col-span-2">متن پیام
                    <textarea name="body" rows="4" minlength="5" maxlength="10000" required class="w-full rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-3 py-3 leading-7">{{ old('body') }}</textarea>
                    @error('body')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>
                <div class="flex flex-col gap-3 border-t border-[var(--panel-border)] pt-4 md:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs leading-6 text-[var(--panel-muted)]">مخاطب هنگام ارسال سمت سرور دوباره اعتبارسنجی می‌شود.</p>
                    <button class="min-h-12 rounded-xl bg-[var(--panel-primary)] px-6 font-black text-white transition hover:brightness-105">ارسال پیام</button>
                </div>
        </form>
    </section>
    @else
        <section class="dashboard-panel p-5 sm:p-7" role="status">
            <h2 class="text-xl font-black text-[var(--panel-text)]">ارسال پیام تازه</h2>
            <p class="mt-2 text-sm leading-7 text-[var(--panel-muted)]">این حساب دسترسی مشاهده دارد. برای ارسال اطلاعیه یا پیام، مجوز مدیریت پیام‌ها لازم است.</p>
        </section>
    @endif

    <section class="space-y-4">
        <div class="flex items-end justify-between gap-3">
            <div><span class="text-xs font-black text-[var(--panel-accent)]">سوابق</span><h2 class="mt-1 text-xl font-black text-[var(--panel-text)]">پیام‌های ارسال‌شده</h2></div>
            <span class="rounded-xl border border-[var(--panel-border)] px-3 py-2 text-xs font-bold">{{ \App\Support\PersianUi::digits($messages->total()) }} پیام</span>
        </div>
        @forelse($messages as $message)
            @php($tone = match($message->audience) { 'all_parents' => 'violet', 'classroom_parents' => 'teal', 'parent' => 'rose', default => 'amber' })
            <article class="dashboard-panel overflow-hidden">
                <div class="h-1.5 {{ $tone === 'violet' ? 'bg-violet-500' : ($tone === 'teal' ? 'bg-teal-500' : ($tone === 'rose' ? 'bg-rose-500' : 'bg-amber-500')) }}"></div>
                <div class="p-5 sm:p-6">
                    <h3 class="break-words text-lg font-black text-[var(--panel-text)]">{{ $message->subject }}</h3>
                    <p class="mt-1 text-xs leading-6 text-[var(--panel-muted)]">{{ $categoryLabels[$message->category] ?? 'پیام' }} · {{ $audienceLabels[$message->audience] ?? 'مخاطب مشخص' }} · {{ \App\Support\PersianUi::date($message->published_at ?? $message->created_at) }}</p>
                    <p class="mt-4 whitespace-pre-line break-words rounded-xl bg-[var(--panel-bg)] p-4 text-sm leading-7 text-[var(--panel-text-secondary)]">{{ $message->body }}</p>
                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-[var(--panel-muted)]">
                        <span>فرستنده: {{ $message->sender?->name ?? 'حساب غیرفعال' }}</span>
                        @if($message->recipient)<span>گیرنده: {{ $message->recipient->name }}</span>@endif
                        @if($message->student)<span>دانش‌آموز مرتبط: {{ $message->student->name }}</span>@endif
                        @if($message->classroom)<span>کلاس: {{ $message->classroom->title }}</span>@endif
                    </div>
                </div>
            </article>
        @empty
            <div class="dashboard-panel p-8 text-center"><h3 class="font-black">هنوز پیامی ارسال نشده</h3><p class="mt-2 text-sm text-[var(--panel-muted)]">از فرم بالا برای شروع استفاده کن.</p></div>
        @endforelse
        @if($messages->hasPages())<div>{{ $messages->onEachSide(1)->links() }}</div>@endif
    </section>
</div>

@push('scripts')
<script>
(() => {
    const picker = document.querySelector('[data-recipient-picker]');
    if (!picker) return;

    const endpoint = picker.dataset.endpoint;
    const typeInput = picker.querySelector('[data-recipient-type]');
    const queryInput = picker.querySelector('[data-recipient-query]');
    const results = picker.querySelector('[data-recipient-results]');
    const status = picker.querySelector('[data-recipient-status]');
    const moreButton = picker.querySelector('[data-recipient-more]');
    let debounceTimer = null;
    let activeRequest = null;
    let requestSequence = 0;
    let currentPage = 1;
    let hasMore = false;

    const clearResults = () => {
        results.querySelectorAll('[data-dynamic-recipient]').forEach(option => option.remove());
        currentPage = 1;
        hasMore = false;
        moreButton.hidden = true;
    };

    const search = async (append = false) => {
        const term = queryInput.value.trim();
        if (term.length < 2) {
            clearResults();
            status.textContent = 'برای جست‌وجو حداقل دو حرف وارد کن.';
            return;
        }

        if (append && !hasMore) return;
        if (activeRequest) activeRequest.abort();
        activeRequest = new AbortController();
        const sequence = ++requestSequence;
        const requestedPage = append ? currentPage + 1 : 1;
        status.textContent = append ? 'در حال دریافت ادامه فهرست...' : 'در حال جست‌وجوی مخاطب‌ها...';
        moreButton.disabled = true;

        const url = new URL(endpoint, window.location.origin);
        url.searchParams.set('type', typeInput.value);
        url.searchParams.set('q', term);
        url.searchParams.set('page', String(requestedPage));

        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: activeRequest.signal
            });
            if (!response.ok) throw new Error('recipient-search-failed');

            const payload = await response.json();
            if (sequence !== requestSequence) return;

            if (!append) clearResults();
            payload.data.forEach(recipient => {
                const option = document.createElement('option');
                option.value = recipient.value;
                option.textContent = recipient.label;
                option.dataset.dynamicRecipient = '1';
                results.appendChild(option);
            });

            currentPage = requestedPage;
            hasMore = payload.has_more;
            moreButton.hidden = !hasMore;
            moreButton.disabled = false;

            const count = results.querySelectorAll('[data-dynamic-recipient]').length;
            status.textContent = count
                ? count + ' مخاطب نمایش داده شده است.' + (hasMore ? ' برای دیدن ادامه فهرست، دکمهٔ پایین را بزن.' : '')
                : 'مخاطبی با این عبارت پیدا نشد.';
        } catch (error) {
            if (error.name === 'AbortError') return;
            moreButton.disabled = false;
            status.textContent = 'جست‌وجوی مخاطب انجام نشد؛ دوباره تلاش کن.';
        }
    };

    queryInput.addEventListener('input', () => {
        window.clearTimeout(debounceTimer);
        if (activeRequest) activeRequest.abort();
        requestSequence++;
        clearResults();
        results.value = '';
        moreButton.disabled = false;
        status.textContent = queryInput.value.trim().length < 2
            ? 'برای جست‌وجو حداقل دو حرف وارد کن.'
            : 'در حال آماده‌سازی جست‌وجو...';
        debounceTimer = window.setTimeout(() => search(false), 300);
    });

    typeInput.addEventListener('change', () => {
        window.clearTimeout(debounceTimer);
        if (activeRequest) activeRequest.abort();
        requestSequence++;
        queryInput.value = '';
        clearResults();
        results.value = '';
        moreButton.disabled = false;
        status.textContent = 'برای جست‌وجوی مخاطب‌های این گروه، حداقل دو حرف وارد کن.';
    });

    moreButton.addEventListener('click', () => search(true));
})();
</script>
@endpush
