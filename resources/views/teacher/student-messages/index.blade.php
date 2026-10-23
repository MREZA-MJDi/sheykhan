@extends('layouts.teacher')

@section('title', 'پیام‌های دانش‌آموزان | شیخان')
@section('header-title', 'پیام‌های دانش‌آموزان')

@section('content')
    @php($statusLabels = ['open' => 'در انتظار پاسخ', 'responded' => 'پاسخ داده شده', 'closed' => 'بسته شده'])

    <div class="mx-auto max-w-6xl space-y-6">
        <header class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <span class="text-xs font-black text-[var(--panel-primary)]">ارتباط آموزشی</span>
            <h1 class="mt-2 text-2xl font-black text-slate-900">مکاتبات دانش‌آموزان شما</h1>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600">
                فقط پیام‌هایی نمایش داده می‌شوند که دانش‌آموز مستقیماً برای شما و در زمینهٔ یکی از دوره‌ها یا کلاس‌های مجاز فرستاده باشد.
                پاسخ شما در کارتابل همان دانش‌آموز ذخیره می‌شود.
            </p>
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-amber-50 p-4"><span class="text-xs font-bold text-amber-800">در انتظار پاسخ</span><strong class="mt-2 block text-2xl font-black text-amber-900">{{ \App\Support\PersianUi::digits($messages->getCollection()->where('status', 'open')->count()) }}</strong><small class="text-xs text-amber-800">در این صفحه</small></div>
                <div class="rounded-2xl bg-emerald-50 p-4"><span class="text-xs font-bold text-emerald-800">پاسخ داده شده</span><strong class="mt-2 block text-2xl font-black text-emerald-900">{{ \App\Support\PersianUi::digits($messages->getCollection()->where('status', 'responded')->count()) }}</strong><small class="text-xs text-emerald-800">در این صفحه</small></div>
                <div class="rounded-2xl bg-indigo-50 p-4 col-span-2 sm:col-span-1"><span class="text-xs font-bold text-indigo-800">کل موارد این صفحه</span><strong class="mt-2 block text-2xl font-black text-indigo-900">{{ \App\Support\PersianUi::digits($messages->count()) }}</strong><small class="text-xs text-indigo-800">برای صفحه‌بندی از پایین استفاده کنید</small></div>
            </div>
        </header>

        <section class="space-y-4" aria-label="فهرست پیام‌های دانش‌آموزان">
            @forelse($messages as $message)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="break-words text-base font-black text-slate-900">{{ $message->subject }}</h2>
                            <p class="mt-2 text-xs leading-6 text-slate-500">
                                {{ $message->student?->name ?? 'دانش‌آموز' }}
                                @if($message->classroom)
                                    · کلاس {{ $message->classroom->title }}
                                @elseif($message->course)
                                    · دوره {{ $message->course->title }}
                                @endif
                                · {{ \App\Support\PersianUi::date($message->created_at) }}
                            </p>
                        </div>
                        <span class="rounded-full px-3 py-1.5 text-xs font-black {{ $message->status === 'responded' ? 'bg-emerald-50 text-emerald-700' : ($message->status === 'closed' ? 'bg-slate-100 text-slate-600' : 'bg-amber-50 text-amber-800') }}">
                            {{ $statusLabels[$message->status] ?? 'در حال پیگیری' }}
                        </span>
                    </div>

                    <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm leading-7 text-slate-700">{{ $message->body }}</div>

                    @if($message->response)
                        <div class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50/70 p-4 text-sm leading-7 text-slate-800">
                            <strong class="mb-1 block text-emerald-800">پاسخ ثبت‌شده</strong>
                            {{ $message->response }}
                        </div>
                    @endif

                    @if($message->status !== 'closed')
                        <form method="POST" action="{{ route('teacher.student-messages.reply', $message) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4">
                            @csrf
                            @method('PATCH')
                            <label class="grid gap-2 text-sm font-bold text-slate-700">
                                {{ $message->response ? 'ویرایش پاسخ' : 'پاسخ به دانش‌آموز' }}
                                <textarea name="response" rows="3" required minlength="2" maxlength="5000" class="rounded-xl border border-slate-300 px-3 py-3 leading-7" placeholder="پاسخ روشن و کاربردی بنویسید…">{{ old('response', $message->response) }}</textarea>
                            </label>
                            <div><button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[var(--panel-primary)] px-5 font-black text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2">{{ $message->response ? 'ذخیره پاسخ' : 'ارسال پاسخ' }}</button></div>
                        </form>
                        @if($message->response)
                            <form method="POST" action="{{ route('teacher.student-messages.close', $message) }}" class="mt-3 flex justify-end">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-black text-slate-600 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2">
                                    بستن مکاتبه پس از پاسخ
                                </button>
                            </form>
                        @endif
                    @else
                        <p class="mt-4 text-xs text-slate-500">این مکاتبه بسته شده و امکان پاسخ جدید ندارد.</p>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                    <h2 class="font-black text-slate-800">پیام جدیدی ندارید</h2>
                    <p class="mt-2 text-sm leading-7 text-slate-500">پیام دانش‌آموزانی که به دوره یا کلاس شما دسترسی فعال دارند در این صفحه نمایش داده می‌شود.</p>
                </div>
            @endforelse

            <div>{{ $messages->links() }}</div>
        </section>
    </div>
@endsection
