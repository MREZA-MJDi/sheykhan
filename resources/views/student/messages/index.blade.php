@extends('layouts.student')

@section('title', 'مکاتبات با مدرس‌ها | شیخان')
@section('header-title', 'مکاتبات با مدرس‌ها')

@section('content')
    @php
        $statusLabels = ['open' => 'در انتظار پاسخ', 'responded' => 'پاسخ داده شده', 'closed' => 'بسته شده'];
        $categoryLabels = ['question' => 'سؤال آموزشی', 'suggestion' => 'پیشنهاد', 'complaint' => 'انتقاد', 'other' => 'سایر'];
    @endphp

    <div class="communication-page mx-auto max-w-6xl space-y-6">
        <header class="communication-page__hero rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <span class="text-xs font-black text-[var(--panel-primary)]">ارتباط آموزشی</span>
            <h1 class="mt-2 text-2xl font-black text-slate-900">پیام به مدرس دوره یا کلاس</h1>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600">
                فقط مدرس‌هایی را می‌توانی انتخاب کنی که به دورهٔ ثبت‌نام‌شده یا کلاس فعال خودت متصل‌اند.
                پیام و پاسخ آن در همین صفحه می‌ماند.
            </p>
        </header>

        @if($contactableTargets->isEmpty())
            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm leading-7 text-amber-900">
                هنوز مدرس فعالی برای دوره‌های ثبت‌نام‌شده یا کلاس‌های فعال تو پیدا نشد؛ بنابراین امکان ارسال پیام به مدرس نامرتبط وجود ندارد.
                اگر باید به کلاسی دسترسی داشته باشی، از آموزشگاه بخواه ثبت‌نام یا عضویت کلاس را بررسی کند.
            </section>
        @else
            <section class="communication-page__card rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="student-message-compose-title">
                <div class="mb-5">
                    <h2 id="student-message-compose-title" class="text-lg font-black text-slate-900">پیام جدید</h2>
                    <p class="mt-1 text-sm text-slate-500">موضوع و متن را روشن بنویس تا مدرس سریع‌تر پیگیری کند.</p>
                </div>

                <form method="POST" action="{{ route('student.messages.store') }}" class="communication-page__form communication-page__form--two-column grid gap-4 md:grid-cols-2">
                    @csrf
                    <label class="grid gap-2 text-sm font-bold text-slate-700 md:col-span-2">
                        مدرس و زمینهٔ پیام
                        <select name="target" required class="min-h-12 rounded-xl border border-slate-300 bg-white px-3 py-2">
                            <option value="">انتخاب مدرس از دوره یا کلاس فعال</option>
                            @foreach($contactableTargets as $target)
                                <option value="{{ $target->key }}" @selected(old('target') === $target->key)>
                                    {{ $target->teacher_name }} — {{ $target->context_title }} ({{ $target->target_type === 'course' ? 'دوره' : 'کلاس' }})
                                </option>
                            @endforeach
                        </select>
                        @error('target') <span class="text-xs font-medium text-rose-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="grid gap-2 text-sm font-bold text-slate-700">
                        نوع پیام
                        <select name="category" required class="min-h-12 rounded-xl border border-slate-300 bg-white px-3 py-2">
                            @foreach($categoryLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('category', 'question') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category') <span class="text-xs font-medium text-rose-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="grid gap-2 text-sm font-bold text-slate-700">
                        موضوع
                        <input name="subject" value="{{ old('subject') }}" minlength="3" maxlength="180" required class="min-h-12 rounded-xl border border-slate-300 px-3 py-2" placeholder="مثلاً پرسش درباره تمرین جلسه">
                        @error('subject') <span class="text-xs font-medium text-rose-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="grid gap-2 text-sm font-bold text-slate-700 md:col-span-2">
                        متن پیام
                        <textarea name="body" rows="4" minlength="5" maxlength="10000" required class="rounded-xl border border-slate-300 px-3 py-3 leading-7" placeholder="سؤال یا توضیحت را بنویس…">{{ old('body') }}</textarea>
                        @error('body') <span class="text-xs font-medium text-rose-600">{{ $message }}</span> @enderror
                    </label>

                    <div class="flex flex-wrap items-center justify-between gap-3 md:col-span-2">
                        <p class="text-xs leading-6 text-slate-500">اطلاعات پیام فقط برای دانش‌آموز، مدرس انتخاب‌شده و نقش‌های دارای مجوز در مسیر فعلی قابل دسترسی است.</p>
                        <button type="submit" class="communication-page__button inline-flex min-h-12 items-center justify-center rounded-xl bg-[var(--panel-primary)] px-6 font-black text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2">
                            ارسال پیام
                        </button>
                    </div>
                </form>
            </section>
        @endif

        <section class="communication-page__message-list space-y-4" aria-labelledby="student-message-history-title">
            <div>
                <h2 id="student-message-history-title" class="text-xl font-black text-slate-900">پیام‌های من</h2>
                <p class="mt-1 text-sm text-slate-500">پیام‌های قبلی و پاسخ‌های ثبت‌شده را از جدیدترین مورد دنبال کن.</p>
            </div>

            @forelse($messages as $message)
                <article class="communication-page__message rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="break-words text-base font-black text-slate-900">{{ $message->subject }}</h3>
                            <p class="mt-2 text-xs leading-6 text-slate-500">
                                مدرس: {{ $message->teacher?->name ?? 'مدرس ثبت‌شده' }}
                                @if($message->classroom)
                                    · کلاس {{ $message->classroom->title }}
                                @elseif($message->course)
                                    · دوره {{ $message->course->title }}
                                @endif
                                · {{ \App\Support\PersianUi::date($message->created_at) }}
                            </p>
                        </div>
                        <span class="communication-page__status rounded-full px-3 py-1.5 text-xs font-black {{ $message->status === 'responded' ? 'bg-emerald-50 text-emerald-700' : ($message->status === 'closed' ? 'bg-slate-100 text-slate-600' : 'bg-amber-50 text-amber-800') }}">
                            {{ $statusLabels[$message->status] ?? 'در حال پیگیری' }}
                        </span>
                    </div>
                    <div class="communication-page__body mt-4 rounded-xl bg-slate-50 p-4 text-sm leading-7 text-slate-700">
                        <span class="mb-1 block text-xs font-black text-slate-500">{{ $categoryLabels[$message->category] ?? 'پیام' }}</span>
                        {{ $message->body }}
                    </div>
                    @if($message->response)
                        <div class="communication-page__response mt-3 rounded-xl border border-emerald-100 bg-emerald-50/70 p-4 text-sm leading-7 text-slate-800">
                            <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                                <strong class="text-emerald-800">پاسخ مدرس · {{ $message->responder?->name ?? $message->teacher?->name ?? 'مدرس' }}</strong>
                                @if($message->responded_at)
                                    <span class="text-xs text-slate-500">{{ \App\Support\PersianUi::date($message->responded_at) }}</span>
                                @endif
                            </div>
                            {{ $message->response }}
                        </div>
                    @else
                        <p class="mt-3 text-xs text-slate-500">هنوز پاسخی برای این پیام ثبت نشده است.</p>
                    @endif
                </article>
            @empty
                <div class="communication-page__empty rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                    <h3 class="font-black text-slate-800">هنوز پیامی ثبت نکرده‌ای</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-500">از فرم بالا می‌توانی با مدرس یکی از دوره‌ها یا کلاس‌های فعال خودت مکاتبه کنی.</p>
                </div>
            @endforelse

            <div>{{ $messages->links() }}</div>
        </section>

        @include('components.communication.teacher-direct-messages-inbox', ['messages' => $teacherMessages, 'recipientType' => 'student'])

    </div>
@endsection
