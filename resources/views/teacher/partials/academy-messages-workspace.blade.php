@php
    $audienceLabels = [
        'all_parents' => 'همه خانواده‌های آموزشگاه',
        'teacher_class_parents' => 'همه خانواده‌های کلاس‌های من',
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

<div class="teacher-workspace-page teacher-communications-workspace">
    <header class="dashboard-panel overflow-hidden p-5 sm:p-7">
        <span class="inline-flex rounded-full border border-[var(--panel-primary)]/20 bg-[var(--panel-primary-soft)] px-3 py-1.5 text-xs font-black text-[var(--panel-primary)]">مرکز ارتباطات</span>
        <h1 class="mt-3 text-2xl font-black leading-relaxed text-[var(--panel-text)]">{{ $pageTitle }}</h1>
        <p class="mt-2 max-w-3xl text-sm leading-7 text-[var(--panel-muted)]">{{ $pageDescription }}</p>
        <a href="{{ $backRoute }}" class="mt-4 inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-4 text-sm font-bold">بازگشت</a>
    </header>

    <nav class="teacher-communications-hub" aria-label="بخش‌های مرکز ارتباطات">
        @if(auth()->user()?->hasPermission('parent_messages.view'))
            <a href="{{ route('teacher.messages.index') }}">
                <strong>صندوق خانواده‌ها</strong>
                <span>مشاهده و پاسخ به پیام‌های والدین</span>
            </a>
        @endif
        @if(auth()->user()?->hasPermission('student_messages.view'))
            <a href="{{ route('teacher.student-messages.index') }}">
                <strong>پیام‌های دانش‌آموزان</strong>
                <span>گفت‌وگو و پیگیری پیام‌های دانش‌آموزی</span>
            </a>
        @endif
        @if(auth()->user()?->hasPermission('parent_messages.manage'))
            <a href="{{ route('teacher.direct-messages.index') }}">
                <strong>ارسال پیام مستقیم</strong>
                <span>ارسال به خانواده یا مخاطب مجاز</span>
            </a>
        @endif
        <a href="{{ route('teacher.communications.index') }}" aria-current="page">
            <strong>اطلاعیه‌ها و پیام گروهی</strong>
            <span>ارسال و مشاهده سوابق اطلاع‌رسانی</span>
        </a>
        @if(auth()->user()?->hasPermission('live_classes.view'))
            <a href="{{ route('teacher.counseling-requests.index') }}">
                <strong>درخواست‌های مشاوره</strong>
                <span>پیشنهاد زمان و هماهنگی جلسه با خانواده</span>
            </a>
        @endif
    </nav>

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
            <div class="grid gap-3 text-sm font-bold md:col-span-2" data-teacher-recipient-picker data-context="announcement" data-endpoint="{{ route('teacher.recipient-options') }}">
                <label class="grid gap-2">گروه مخاطب
                    <select data-recipient-type class="min-h-12 w-full rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-3">
                        <option value="students">دانش‌آموز مشخص</option>
                        <option value="parents">ولی مشخص</option>
                        <option value="classrooms">خانواده‌های یک کلاس</option>
                    </select>
                </label>
                <label class="grid gap-2">جست‌وجوی مخاطب
                    <input type="search" data-recipient-query minlength="2" maxlength="80" autocomplete="off" class="min-h-12 w-full rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-3" placeholder="حداقل دو حرف از نام دانش‌آموز، ولی یا کلاس">
                </label>
                <p data-recipient-status role="status" aria-live="polite" class="text-xs font-normal leading-6 text-[var(--panel-muted)]">برای جلوگیری از بارگذاری فهرست‌های بزرگ، مخاطب را جست‌وجو کن. هر بار حداکثر ۲۰ نتیجه نمایش داده می‌شود.</p>
                <label class="grid gap-2">انتخاب مخاطب
                    <select name="target" data-recipient-results required size="5" class="min-h-12 w-full rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-3 py-2">
                        <option value="" data-static>ابتدا مخاطب را جست‌وجو کن…</option>
                        @foreach($targetOptions as $target)
                            <option value="{{ $target['value'] }}" data-static @selected(old('target') === $target['value'])>{{ $target['label'] }}</option>
                        @endforeach
                        @if(old('target') && !collect($targetOptions)->contains('value', old('target')) && old('target') !== 'all_parents')
                            <option value="{{ old('target') }}" data-static selected>مخاطب انتخاب‌شده از ارسال قبلی</option>
                        @endif
                    </select>
                    @error('target')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>
                <button type="button" data-recipient-more hidden class="min-h-10 justify-self-start rounded-lg border border-[var(--panel-border)] px-4 text-xs font-bold">نمایش ۲۰ مخاطب بعدی</button>
            </div>
            <label class="grid gap-2 text-sm font-bold">نوع پیام
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
                <p class="text-xs leading-6 text-[var(--panel-muted)]">هنگام ارسال، ارتباط مخاطب با کلاس و آموزشگاه دوباره بررسی می‌شود.</p>
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

    <form method="GET" class="dashboard-panel grid gap-3 p-4 sm:grid-cols-[minmax(0,1fr)_190px_190px_auto_auto] sm:items-end" role="search" aria-label="فیلتر اطلاعیه‌های مدرس">
        <label class="grid gap-1 text-xs font-bold">جست‌وجو
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" class="min-h-10 rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-3 text-sm" placeholder="عنوان، متن، گیرنده، فرزند یا کلاس">
        </label>
        <label class="grid gap-1 text-xs font-bold">نوع پیام
            <select name="category" class="min-h-10 rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-3 text-sm">
                <option value="">همه انواع پیام</option>
                <option value="announcement" @selected(($filters['category'] ?? '') === 'announcement')>اطلاعیه</option>
                <option value="academic" @selected(($filters['category'] ?? '') === 'academic')>آموزشی</option>
                <option value="schedule" @selected(($filters['category'] ?? '') === 'schedule')>زمان‌بندی</option>
                <option value="important" @selected(($filters['category'] ?? '') === 'important')>مهم</option>
                <option value="other" @selected(($filters['category'] ?? '') === 'other')>سایر</option>
            </select>
        </label>
        <label class="grid gap-1 text-xs font-bold">مخاطب
            <select name="audience" class="min-h-10 rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-3 text-sm">
                <option value="">همه مخاطب‌ها</option>
                <option value="teacher_class_parents" @selected(($filters['audience'] ?? '') === 'teacher_class_parents')>همه خانواده‌های کلاس‌های من</option>
                <option value="classroom_parents" @selected(($filters['audience'] ?? '') === 'classroom_parents')>خانواده‌های یک کلاس</option>
                <option value="parent" @selected(($filters['audience'] ?? '') === 'parent')>ولی مشخص</option>
                <option value="student" @selected(($filters['audience'] ?? '') === 'student')>دانش‌آموز مشخص</option>
            </select>
        </label>
        <button class="min-h-10 rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">اعمال فیلتر</button>
        <a href="{{ route('teacher.communications.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--panel-border)] bg-[var(--panel-surface)] px-4 text-sm font-bold">پاک‌کردن</a>
    </form>
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
