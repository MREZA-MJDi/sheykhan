@extends('layouts.owner')

@section('title', 'مرکز ارتباطات و پیگیری | شیخان')
@section('header-title', 'مرکز ارتباطات و پیگیری')

@section('content')
@php
    $baseUrl = route('owner.communication-workspace', $academy);
    $messageStatuses = ['open' => 'در انتظار بررسی', 'in_review' => 'در حال پیگیری', 'responded' => 'پاسخ‌داده‌شده', 'closed' => 'بسته‌شده'];
    $counselingStatuses = ['requested' => 'درخواست جدید', 'proposed' => 'پیشنهاد مدرس', 'scheduled' => 'زمان‌بندی‌شده', 'completed' => 'برگزارشده', 'declined' => 'پذیرفته نشد', 'cancelled' => 'لغوشده'];
    $meetingStatuses = ['proposed' => 'پیشنهاد مدرس', 'scheduled' => 'زمان‌بندی‌شده', 'completed' => 'برگزارشده', 'cancelled' => 'لغوشده', 'declined' => 'ردشده'];
@endphp

<div class="owner-workspace-page space-y-5 panel-page-enter">
    @if($tab === 'meetings')
        @include('owner.partials.learning-workspace-tabs', ['academy' => $academy, 'activeSheet' => 'meetings'])
    @endif
    <header class="dashboard-panel p-5 sm:p-7">
        <p class="text-xs font-black text-[var(--panel-primary)]">فضای کاری یکپارچه</p>
        <h1 class="mt-1 text-2xl font-black text-[var(--panel-text)]">ارتباطات و پیگیری خانواده‌ها</h1>
        <p class="mt-2 max-w-3xl text-sm leading-7 text-[var(--panel-muted)]">پیام‌ها، اطلاعیه‌ها، درخواست‌های مشاوره و جلسات را از یکجا مدیریت کن. هر بخش فقط اطلاعات مربوط به همین آموزشگاه را نمایش می‌دهد.</p>
        <p class="mt-2 text-xs text-[var(--panel-muted)]">{{ $academy->name }}</p>
    </header>

    @if(session('success'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800"><strong>اطلاعات نیاز به اصلاح دارد.</strong>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
    @endif

    @if(isset($stats))
    <section class="grid grid-cols-2 gap-3 xl:grid-cols-4" aria-label="خلاصه وضعیت ارتباطات">
        @if($canViewFamilies)
            <article class="owner-stat-card"><span>پیام‌های نیازمند پیگیری</span><strong>{{ \App\Support\PersianUi::digits($stats['unanswered']) }}</strong><small>از طرف خانواده‌ها</small></article>
            <article class="owner-stat-card"><span>درخواست‌های مشاوره</span><strong>{{ \App\Support\PersianUi::digits($stats['counseling']) }}</strong><small>جدید یا در انتظار تأیید</small></article>
            <article class="owner-stat-card"><span>اطلاعیه‌های ثبت‌شده</span><strong>{{ \App\Support\PersianUi::digits($stats['announcements']) }}</strong><small>سوابق ارسال آموزشگاه</small></article>
        @endif
        @if($canViewMeetings)
            <article class="owner-stat-card"><span>جلسات پیش‌رو</span><strong>{{ \App\Support\PersianUi::digits($stats['upcoming'] ?? 0) }}</strong><small>زمان‌بندی‌شده</small></article>
        @endif
    </section>
    @endif

    <nav class="dashboard-panel flex flex-wrap gap-2 p-3" aria-label="بخش‌های مرکز ارتباطات">
        @foreach($tabs as $key => $item)
            @if($item['allowed'])
                <a href="{{ $baseUrl }}?tab={{ $key }}" @if($tab === $key) aria-current="page" @endif
                   class="inline-flex min-h-10 items-center rounded-xl px-4 text-sm font-bold transition {{ $tab === $key ? 'bg-[var(--panel-primary)] text-white' : 'border border-[var(--panel-border)] text-[var(--panel-text)] hover:bg-[var(--panel-bg)]' }}">
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </nav>

    @if($tab === 'overview')
        <section class="grid gap-4 lg:grid-cols-2">
            @if($canViewFamilies)
                <a href="{{ $baseUrl }}?tab=messages" class="dashboard-panel p-5 transition hover:-translate-y-0.5">
                    <h2 class="font-black">پیام‌های خانواده‌ها</h2><p class="mt-2 text-sm leading-7 text-[var(--panel-muted)]">پرسش‌ها، پیشنهادها و انتقادهای دریافتی را بررسی و پاسخ بده.</p><span class="mt-3 inline-block text-xs font-black text-[var(--panel-primary)]">ورود به صندوق پیام‌ها ←</span>
                </a>
                <a href="{{ $baseUrl }}?tab=announcements" class="dashboard-panel p-5 transition hover:-translate-y-0.5">
                    <h2 class="font-black">اطلاعیه‌های آموزشگاه</h2><p class="mt-2 text-sm leading-7 text-[var(--panel-muted)]">اطلاعیه تازه منتشر کن و سوابق پیام‌های ارسال‌شده را ببین.</p><span class="mt-3 inline-block text-xs font-black text-[var(--panel-primary)]">مدیریت اطلاعیه‌ها ←</span>
                </a>
                <a href="{{ $baseUrl }}?tab=counseling" class="dashboard-panel p-5 transition hover:-translate-y-0.5">
                    <h2 class="font-black">درخواست‌های مشاوره</h2><p class="mt-2 text-sm leading-7 text-[var(--panel-muted)]">درخواست‌های والدین را بررسی و زمان جلسه را مشخص کن.</p><span class="mt-3 inline-block text-xs font-black text-[var(--panel-primary)]">پیگیری مشاوره‌ها ←</span>
                </a>
            @endif
            @if($canViewMeetings)
                <a href="{{ $baseUrl }}?tab=meetings" class="dashboard-panel p-5 transition hover:-translate-y-0.5">
                    <h2 class="font-black">جلسات هماهنگی</h2><p class="mt-2 text-sm leading-7 text-[var(--panel-muted)]">جلسات خانواده‌ها و پیشنهادهای مدرس‌ها را زمان‌بندی و پیگیری کن.</p><span class="mt-3 inline-block text-xs font-black text-[var(--panel-primary)]">مدیریت جلسات ←</span>
                </a>
            @endif
        </section>
    @elseif($tab === 'messages')
        <section class="dashboard-panel p-4 sm:p-5">
            <form method="GET" action="{{ $baseUrl }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 sm:items-end">
                <input type="hidden" name="tab" value="messages">
                <label class="grid gap-1 text-xs font-bold">جستجو<input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="عنوان، متن یا نام" class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm"></label>
                <label class="grid gap-1 text-xs font-bold">وضعیت<select name="status" class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm"><option value="">همه وضعیت‌ها</option>@foreach($messageStatuses as $value => $label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
                <label class="grid gap-1 text-xs font-bold">نوع پیام<select name="category" class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm"><option value="">همه انواع</option><option value="question">پرسش</option><option value="suggestion">پیشنهاد</option><option value="complaint">انتقاد یا شکایت</option><option value="other">سایر</option></select></label>
                <button class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">اعمال فیلتر</button>
            </form>
        </section>
        <section class="space-y-3">
            @forelse($items as $item)
                <article class="dashboard-panel p-4 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-2"><div><p class="text-xs font-bold text-[var(--panel-primary)]">{{ $item->parent?->name ?? 'ولی' }} · دانش‌آموز: {{ $item->student?->name ?? '—' }}</p><h2 class="mt-1 text-base font-black">{{ $item->subject }}</h2></div><span class="rounded-full bg-[var(--panel-bg)] px-3 py-1 text-xs font-bold">{{ $messageStatuses[$item->status] ?? $item->status }}</span></div>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7">{{ $item->body }}</p>
                    @if($item->response)<div class="mt-3 rounded-xl bg-[var(--panel-bg)] p-3 text-sm leading-7"><strong>آخرین پاسخ:</strong><p>{{ $item->response }}</p></div>@endif
                    <p class="mt-2 text-xs text-[var(--panel-muted)]">{{ \App\Support\PersianUi::dateTime($item->created_at) }} · {{ $item->parent?->mobile ?: $item->parent?->email }}</p>
                    @if($canManageFamilies)
                        <form method="POST" action="{{ route('owner.messages.reply', [$academy, $item->id]) }}" class="mt-4 grid gap-3 border-t border-[var(--panel-border)] pt-4 md:grid-cols-[1fr_190px_auto] md:items-end">
                            @csrf @method('PATCH')
                            <label class="grid gap-1 text-xs font-bold">پاسخ برای خانواده<textarea name="response" rows="2" maxlength="5000" class="rounded-xl border border-[var(--panel-border)] px-3 py-2 text-sm" placeholder="پاسخ روشن و محترمانه بنویس...">{{ $item->response }}</textarea></label>
                            <label class="grid gap-1 text-xs font-bold">وضعیت<select name="status" class="min-h-10 rounded-xl border border-[var(--panel-border)] px-3 text-sm"><option value="in_review">در حال پیگیری</option><option value="responded">ثبت پاسخ و ارسال</option><option value="closed">بستن پیام</option></select></label>
                            <button class="min-h-10 rounded-xl bg-[var(--panel-primary)] px-4 text-xs font-black text-white">ذخیره</button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="dashboard-panel p-8 text-center"><h2 class="font-black">پیامی پیدا نشد.</h2><p class="mt-2 text-sm text-[var(--panel-muted)]">فیلترها را تغییر بده یا بعداً دوباره بررسی کن.</p></div>
            @endforelse
            {{ $items->links('components.navigation.pagination') }}
        </section>
    @elseif($tab === 'announcements')
        @if($canManageFamilies)
            <section class="dashboard-panel p-5 sm:p-6">
                <h2 class="text-lg font-black">ارسال اطلاعیه تازه</h2><p class="mt-1 text-sm text-[var(--panel-muted)]">فعلاً می‌توانی اطلاعیه را برای همه خانواده‌های فعال آموزشگاه ارسال کنی.</p>
                <form method="POST" action="{{ route('owner.communications.store', $academy) }}" class="mt-4 grid gap-3 md:grid-cols-2">
                    @csrf
                    <input type="hidden" name="target" value="all_parents">
                    <label class="grid gap-1 text-xs font-bold">دسته‌بندی<select name="category" class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm">@foreach(['announcement'=>'اطلاعیه','academic'=>'آموزشی','schedule'=>'زمان‌بندی','important'=>'مهم','other'=>'سایر'] as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                    <label class="grid gap-1 text-xs font-bold">عنوان<input name="subject" maxlength="180" required class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm"></label>
                    <label class="grid gap-1 text-xs font-bold md:col-span-2">متن اطلاعیه<textarea name="body" rows="4" maxlength="10000" required class="rounded-xl border border-[var(--panel-border)] px-3 py-2 text-sm"></textarea></label>
                    <button class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white md:col-span-2">ارسال به خانواده‌های آموزشگاه</button>
                </form>
            </section>
        @endif
        <section class="space-y-3">
            @forelse($items as $item)
                <article class="dashboard-panel p-4 sm:p-5"><div class="flex flex-wrap items-center justify-between gap-2"><h2 class="font-black">{{ $item->subject }}</h2><span class="text-xs text-[var(--panel-muted)]">{{ \App\Support\PersianUi::dateTime($item->published_at ?? $item->created_at) }}</span></div><p class="mt-3 whitespace-pre-line text-sm leading-7">{{ $item->body }}</p><p class="mt-2 text-xs text-[var(--panel-muted)]">فرستنده: {{ $item->sender?->name ?? '—' }} · مخاطب: {{ $item->audience === 'all_parents' ? 'همه خانواده‌ها' : ($item->recipient?->name ?? $item->student?->name ?? 'مخاطب مشخص') }}</p></article>
            @empty
                <div class="dashboard-panel p-8 text-center text-sm">هنوز اطلاعیه‌ای ثبت نشده است.</div>
            @endforelse
            {{ $items->links('components.navigation.pagination') }}
        </section>
    @elseif($tab === 'counseling')
        <section class="dashboard-panel p-4">
            <form method="GET" action="{{ $baseUrl }}" class="grid gap-3 sm:grid-cols-[1fr_220px_auto] sm:items-end">
                <input type="hidden" name="tab" value="counseling">
                <label class="grid gap-1 text-xs font-bold">جستجو<input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="نام والد، دانش‌آموز یا موضوع" class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm"></label>
                <label class="grid gap-1 text-xs font-bold">وضعیت<select name="status" class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm"><option value="">همه وضعیت‌ها</option>@foreach($counselingStatuses as $value=>$label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
                <button class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">اعمال فیلتر</button>
            </form>
        </section>
        <section class="space-y-3">
            @forelse($items as $item)
                <article class="dashboard-panel p-4 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-2"><div><p class="text-xs font-bold text-[var(--panel-primary)]">{{ $item->parent?->name }} · {{ $item->student?->name }}</p><h2 class="mt-1 font-black">{{ $item->subject }}</h2></div><span class="rounded-full bg-[var(--panel-bg)] px-3 py-1 text-xs font-bold">{{ $counselingStatuses[$item->status] ?? $item->status }}</span></div>
                    @if($item->details)<p class="mt-3 whitespace-pre-line text-sm leading-7">{{ $item->details }}</p>@endif
                    <div class="mt-3 grid gap-2 sm:grid-cols-2"><p class="rounded-xl bg-[var(--panel-bg)] p-3 text-xs">زمان پیشنهادی والد: {{ $item->preferred_at ? \App\Support\PersianUi::dateTime($item->preferred_at) : 'ثبت نشده' }}</p><p class="rounded-xl bg-[var(--panel-bg)] p-3 text-xs">زمان نهایی: {{ $item->scheduled_at ? \App\Support\PersianUi::dateTime($item->scheduled_at) : 'تعیین نشده' }}</p></div>
                    @if($canManageFamilies)
                        <form method="POST" action="{{ route('owner.counseling.update', [$academy, $item->id]) }}" class="mt-4 grid gap-3 border-t border-[var(--panel-border)] pt-4 md:grid-cols-[180px_1fr_1fr_auto] md:items-end">
                            @csrf @method('PATCH')
                            <label class="grid gap-1 text-xs font-bold">وضعیت<select name="status" class="min-h-10 rounded-xl border border-[var(--panel-border)] px-3 text-sm">@foreach(['scheduled'=>'تعیین زمان','completed'=>'برگزارشده','declined'=>'پذیرفته نشد','cancelled'=>'لغو'] as $value=>$label)<option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>@endforeach</select></label>
                            <label class="grid gap-1 text-xs font-bold">زمان قطعی<input type="datetime-local" name="scheduled_at" value="{{ ($item->scheduled_at ?? $item->proposed_at ?? $item->preferred_at)?->format('Y-m-d\TH:i') }}" class="min-h-10 rounded-xl border border-[var(--panel-border)] px-3 text-sm"></label>
                            <label class="grid gap-1 text-xs font-bold">توضیح برای خانواده<textarea name="response_notes" rows="2" maxlength="3000" class="rounded-xl border border-[var(--panel-border)] px-3 py-2 text-sm">{{ $item->response_notes }}</textarea></label>
                            <button class="min-h-10 rounded-xl bg-[var(--panel-primary)] px-4 text-xs font-black text-white">ذخیره</button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="dashboard-panel p-8 text-center text-sm">درخواستی با این فیلتر پیدا نشد.</div>
            @endforelse
            {{ $items->links('components.navigation.pagination') }}
        </section>
    @elseif($tab === 'meetings')
        <section class="dashboard-panel p-4">
            <form method="GET" action="{{ $baseUrl }}" class="grid gap-3 sm:grid-cols-[1fr_220px_auto] sm:items-end">
                <input type="hidden" name="tab" value="meetings">
                <label class="grid gap-1 text-xs font-bold">جستجو<input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="عنوان، دانش‌آموز یا ولی" class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm"></label>
                <label class="grid gap-1 text-xs font-bold">وضعیت<select name="status" class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm"><option value="">همه وضعیت‌ها</option>@foreach($meetingStatuses as $value=>$label)<option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></label>
                <button class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">اعمال فیلتر</button>
            </form>
        </section>
        @if($canManageMeetings)
            <section class="dashboard-panel p-4 sm:p-5">
                <h2 class="text-base font-black">ثبت جلسه جدید</h2>
                <p class="mt-1 text-sm leading-7 text-[var(--panel-muted)]">برای جلوگیری از فهرست‌های طولانی، ابتدا نام دانش‌آموز، ولی یا کلاس را جست‌وجو کن؛ حداکثر ۲۰ ارتباط معتبر نمایش داده می‌شود.</p>
                <form method="GET" action="{{ $baseUrl }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                    <input type="hidden" name="tab" value="meetings">
                    <label class="grid gap-1 text-xs font-bold">جست‌وجوی مخاطب<input name="target_q" value="{{ $targetQuery ?? '' }}" minlength="2" placeholder="حداقل دو حرف از نام یا عنوان کلاس" class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm"></label>
                    <button class="min-h-11 rounded-xl border border-[var(--panel-border)] px-4 text-sm font-bold">جست‌وجوی مخاطب</button>
                </form>
                @if(($targetQuery ?? '') !== '' && mb_strlen($targetQuery) < 2)
                    <p class="mt-2 text-xs text-amber-700">برای جست‌وجو حداقل دو حرف وارد کن.</p>
                @elseif(isset($meetingTargets) && $meetingTargets->isNotEmpty())
                    <div class="mt-4">
                        <button type="button" class="owner-modal-trigger owner-modal-trigger--primary" data-owner-modal-open="owner-meeting-create-modal">＋ ثبت جلسه جدید</button>
                    </div>
                    <dialog class="owner-floating-modal" id="owner-meeting-create-modal" aria-labelledby="owner-meeting-create-title">
                        <header class="owner-floating-modal__header">
                            <div><span class="owner-record-modal-kicker">هماهنگی با خانواده</span><h2 id="owner-meeting-create-title">ثبت جلسه جدید</h2></div>
                            <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
                        </header>
                        <div class="owner-floating-modal__body">
<form method="POST" action="{{ route('owner.meetings.store', $academy) }}" class="mt-4 grid gap-3 md:grid-cols-2">
                        @csrf
                        <input type="hidden" name="form_modal" value="meeting">
                        <label class="grid gap-1 text-xs font-bold md:col-span-2">کلاس، دانش‌آموز و ولی
                            <select name="meeting_target" required class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm">
                                <option value="">انتخاب ارتباط</option>
                                @foreach($meetingTargets as $target)
                                    <option value="{{ $target->classroom_id }}:{{ $target->student_id }}:{{ $target->parent_id }}" @selected(old('meeting_target') === $target->classroom_id.':'.$target->student_id.':'.$target->parent_id)>{{ $target->classroom_title }} — {{ $target->student_name }} — ولی: {{ $target->parent_name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="grid gap-1 text-xs font-bold md:col-span-2">عنوان جلسه<input name="title" value="{{ old('title') }}" required maxlength="255" placeholder="مثلاً: بررسی روند تحصیلی و هماهنگی با خانواده" class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm"></label>
                        <label class="grid gap-1 text-xs font-bold">زمان شروع<input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" min="{{ now()->format('Y-m-d\\TH:i') }}" required class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm"></label>
                        <label class="grid gap-1 text-xs font-bold">زمان پایان (اختیاری)<input type="datetime-local" name="scheduled_end_at" value="{{ old('scheduled_end_at') }}" class="min-h-11 rounded-xl border border-[var(--panel-border)] px-3 text-sm"></label>
                        <label class="grid gap-1 text-xs font-bold md:col-span-2">پیام برای خانواده (اختیاری)<textarea name="message" rows="2" maxlength="1500" class="rounded-xl border border-[var(--panel-border)] px-3 py-2 text-sm">{{ old('message') }}</textarea></label>
                        <button class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white md:col-span-2">ثبت جلسه و اطلاع‌رسانی</button>
                    </form>
                        </div>
                    </dialog>
                    <div class="mt-3 text-sm">{{ $meetingTargets->links('components.navigation.pagination') }}</div>
                @elseif(($targetQuery ?? '') !== '')
                    <p class="mt-3 rounded-xl bg-[var(--panel-bg)] p-4 text-sm">مخاطب فعالی با این عبارت پیدا نشد. نام دیگری را امتحان کن.</p>
                @endif
            </section>
        @endif
        <section class="space-y-3">
            @forelse($items as $item)
                <article class="dashboard-panel p-4 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-2"><div><h2 class="font-black">{{ $item->title }}</h2><p class="mt-1 text-xs text-[var(--panel-muted)]">{{ $item->classroom?->title ?? 'کلاس' }} · {{ $item->course?->title ?? 'دوره' }}</p><p class="mt-1 text-xs text-[var(--panel-muted)]">دانش‌آموز: {{ $item->student?->name ?? '—' }} · ولی: {{ $item->guardian?->name ?? '—' }}</p></div><span class="rounded-full bg-[var(--panel-bg)] px-3 py-1 text-xs font-bold">{{ $meetingStatuses[$item->status] ?? $item->status }}</span></div>
                    <p class="mt-2 text-xs text-[var(--panel-muted)]">{{ $item->scheduled_at ? \App\Support\PersianUi::dateTime($item->scheduled_at) : 'زمان مشخص نشده' }}</p>
                    @if($item->message)<p class="mt-2 whitespace-pre-line text-sm leading-7">{{ $item->message }}</p>@endif
                    @if($canManageMeetings)
                        <div class="mt-4">
                            <button type="button" class="owner-modal-trigger" data-owner-modal-open="owner-meeting-status-{{ $item->id }}">تغییر وضعیت</button>
                        </div>
                        <dialog class="owner-floating-modal" id="owner-meeting-status-{{ $item->id }}" aria-labelledby="owner-meeting-status-title-{{ $item->id }}">
                            <header class="owner-floating-modal__header">
                                <div><span class="owner-record-modal-kicker">مدیریت جلسه</span><h2 id="owner-meeting-status-title-{{ $item->id }}">تغییر وضعیت · {{ $item->title }}</h2></div>
                                <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن">×</button>
                            </header>
                            <div class="owner-floating-modal__body">
<form method="POST" action="{{ route('owner.meetings.status', [$academy, $item->id]) }}" class="mt-4 flex flex-wrap items-end gap-2 border-t border-[var(--panel-border)] pt-4">
                            @csrf
                            <input type="hidden" name="form_modal" value="meeting-status-{{ $item->id }}"> @method('PATCH')
                            <label class="grid gap-1 text-xs font-bold">وضعیت<select name="status" class="min-h-10 rounded-xl border border-[var(--panel-border)] px-3 text-sm">@foreach(($item->status === 'proposed' ? ['scheduled'=>'تأیید و زمان‌بندی','declined'=>'رد پیشنهاد'] : ['scheduled'=>'برنامه‌ریزی‌شده','completed'=>'برگزارشده','cancelled'=>'لغوشده']) as $value=>$label)<option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>@endforeach</select></label>
                            @if($item->status === 'proposed')
                                <label class="grid gap-1 text-xs font-bold">زمان نهایی<input type="datetime-local" name="scheduled_at" value="{{ $item->scheduled_at?->format('Y-m-d\TH:i') }}" min="{{ now()->format('Y-m-d\TH:i') }}" required class="min-h-10 rounded-xl border border-[var(--panel-border)] px-3 text-sm"></label>
                                <label class="grid gap-1 text-xs font-bold">پایان جلسه<input type="datetime-local" name="scheduled_end_at" value="{{ $item->scheduled_end_at?->format('Y-m-d\TH:i') }}" class="min-h-10 rounded-xl border border-[var(--panel-border)] px-3 text-sm"></label>
                            @endif
                            <button class="min-h-10 rounded-xl bg-[var(--panel-primary)] px-4 text-xs font-black text-white">ذخیره وضعیت</button>
                        </form>
                            </div>
                        </dialog>
                    @endif
                </article>
            @empty
                <div class="dashboard-panel p-8 text-center text-sm">جلسه‌ای با این فیلتر پیدا نشد.</div>
            @endforelse
            {{ $items->links('components.navigation.pagination') }}
        </section>
    @endif
</div>
@endsection
