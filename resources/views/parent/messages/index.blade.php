@extends('layouts.parent')

@section('title', 'پیام‌ها و پیشنهادها | شیخان')
@section('header-title', 'پیام‌ها و پیشنهادها')

@section('content')
<div class="space-y-6">
    <header class="dashboard-panel p-5 sm:p-7">
        <p class="text-xs font-black text-[var(--panel-primary)]">ارتباط با آموزشگاه</p>
        <h1 class="mt-2 text-2xl font-black text-[var(--color-text)]">پیام، انتقاد یا پیشنهاد خود را ثبت کنید</h1>
        <p class="mt-3 max-w-3xl text-sm leading-7 text-[var(--color-text-muted)]">پیام شما فقط برای آموزشگاهی که انتخاب می‌کنید ثبت می‌شود. پاسخ و وضعیت پیگیری را بعداً از همین صفحه ببینید.</p>
    </header>

    <section class="dashboard-panel p-5 sm:p-6" aria-labelledby="parent-message-form-title">
        <div class="mb-5">
            <h2 id="parent-message-form-title" class="text-lg font-black">پیام جدید</h2>
            <p class="mt-1 text-sm leading-6 text-slate-500">فرزند و آموزشگاه مربوط را انتخاب کنید تا درخواست اشتباهاً برای جای دیگری ارسال نشود.</p>
        </div>

        @if($targets->isEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-7 text-amber-900">
                هنوز فرزند فعالی که به یک آموزشگاه متصل باشد برای این حساب پیدا نشد. پس از اتصال دانش‌آموز به آموزشگاه، امکان ارسال پیام فعال می‌شود.
            </div>
        @else
            <form method="POST" action="{{ route('parent.messages.store') }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                <label class="grid gap-2 text-sm font-bold">
                    <span>فرزند و آموزشگاه</span>
                    <select name="target" required class="min-h-12 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                        <option value="">انتخاب کنید</option>
                        @foreach($targets as $target)
                            <option value="{{ $target->academy_id }}:{{ $target->student_id }}" @selected(old('target') === $target->academy_id . ':' . $target->student_id)>
                                {{ $target->student_name }} — {{ $target->academy_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('target')<span class="text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-2 text-sm font-bold">
                    <span>نوع پیام</span>
                    <select name="category" required class="min-h-12 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm">
                        <option value="question" @selected(old('category') === 'question')>پرسش</option>
                        <option value="suggestion" @selected(old('category') === 'suggestion')>پیشنهاد</option>
                        <option value="complaint" @selected(old('category') === 'complaint')>انتقاد یا شکایت</option>
                        <option value="other" @selected(old('category') === 'other')>سایر موارد</option>
                    </select>
                    @error('category')<span class="text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-2 text-sm font-bold md:col-span-2">
                    <span>عنوان</span>
                    <input name="subject" value="{{ old('subject') }}" required maxlength="180" class="min-h-12 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="مثلاً: سؤال درباره برنامه کلاس">
                    @error('subject')<span class="text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-2 text-sm font-bold md:col-span-2">
                    <span>متن پیام</span>
                    <textarea name="body" required minlength="10" maxlength="5000" rows="5" class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm leading-7" placeholder="موضوع را روشن و محترمانه توضیح دهید...">{{ old('body') }}</textarea>
                    @error('body')<span class="text-xs font-semibold text-rose-600">{{ $message }}</span>@enderror
                </label>
                <div class="md:col-span-2 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs leading-6 text-slate-500">پیام‌ها در پنل آموزشگاه ثبت می‌شوند و برای پیگیری، تاریخچه‌ی آن‌ها حفظ می‌شود.</p>
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[var(--panel-primary)] px-5 py-2.5 text-sm font-black text-white transition hover:opacity-90">ثبت پیام</button>
                </div>
            </form>
        @endif
    </section>

    <section class="space-y-4" aria-labelledby="parent-message-history-title">
        <div>
            <h2 id="parent-message-history-title" class="text-lg font-black">تاریخچه پیام‌ها</h2>
            <p class="mt-1 text-sm text-slate-500">وضعیت هر پیام و پاسخ آموزشگاه در اینجا نگهداری می‌شود.</p>
        </div>
        @forelse($messages as $message)
            @php
                $statusLabels = ['open' => 'در انتظار بررسی', 'in_review' => 'در حال پیگیری', 'responded' => 'پاسخ داده‌شده', 'closed' => 'بسته‌شده'];
                $categoryLabels = ['question' => 'پرسش', 'suggestion' => 'پیشنهاد', 'complaint' => 'انتقاد یا شکایت', 'other' => 'سایر موارد'];
            @endphp
            <article class="dashboard-panel p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-[var(--panel-primary)]">{{ $message->academy?->name }} · {{ $message->student?->name }}</p>
                        <h3 class="mt-1 break-words text-base font-black">{{ $message->subject }}</h3>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700">{{ $statusLabels[$message->status] ?? 'ثبت‌شده' }}</span>
                </div>
                <p class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $message->body }}</p>
                <div class="mt-4 flex flex-wrap justify-between gap-2 border-t border-slate-100 pt-3 text-xs text-slate-500">
                    <span>{{ $categoryLabels[$message->category] ?? 'پیام' }}</span>
                    <time datetime="{{ $message->created_at?->toIso8601String() }}">{{ \App\Support\PersianUi::date($message->created_at) }}</time>
                </div>
                @if($message->response)
                    <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-xs font-black text-emerald-800">پاسخ آموزشگاه</p>
                        <p class="mt-2 whitespace-pre-line break-words text-sm leading-7 text-emerald-950">{{ $message->response }}</p>
                        @if($message->responder)<p class="mt-2 text-xs text-emerald-800">پاسخ‌دهنده: {{ $message->responder->name }}</p>@endif
                    </div>
                @endif
            </article>
        @empty
            <div class="dashboard-panel p-8 text-center">
                <h3 class="font-black">هنوز پیامی ثبت نکرده‌اید.</h3>
                <p class="mt-2 text-sm leading-7 text-slate-500">از فرم بالای صفحه برای ارتباط با آموزشگاه استفاده کنید.</p>
            </div>
        @endforelse
        <div>{{ $messages->links() }}</div>
    </section>
</div>
@endsection
