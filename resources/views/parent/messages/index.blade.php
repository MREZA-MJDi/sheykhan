@extends('layouts.parent')

@section('title', 'پیام‌های آموزشگاه | شیخان')
@section('header-title', 'پیام‌ها و پیگیری‌ها')

@section('content')
<div class="parent-dashboard space-y-6">
    <header class="parent-panel dashboard-panel p-5 sm:p-7">
        <p class="parent-kicker" style="color:var(--panel-primary)">ارتباط با آموزشگاه</p>
        <h1 class="mt-1 text-2xl font-black">پیام‌ها، پرسش‌ها و پیشنهادها</h1>
        <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">هر پیام به فرزند و آموزشگاه مشخصی متصل می‌شود. وضعیت پیگیری و پاسخ رسمی را همین‌جا می‌بینی.</p>
    </header>

    <section class="parent-panel dashboard-panel p-5 sm:p-7">
        <h2 class="text-lg font-black">ثبت پیام جدید</h2>
        @if($contactableChildren->isEmpty())
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-7 text-amber-900">
                هنوز حساب فرزند شما به یک آموزشگاه فعال متصل نشده است. از مدیر آموزشگاه بخواهید حساب والد و دانش‌آموز را به هم متصل کند.
            </div>
        @else
            <form method="POST" action="{{ route('parent.messages.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <label class="grid gap-1 text-sm font-bold sm:col-span-2">
                    فرزند و آموزشگاه
                    <select name="target" required class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm">
                        <option value="">انتخاب فرزند و آموزشگاه</option>
                        @foreach($contactableChildren as $child)
                            @foreach($child->academies as $academy)
                                <option value="{{ $child->id }}:{{ $academy->id }}" @selected(old('target') === $child->id.':'.$academy->id)>{{ $child->name }} — {{ $academy->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('target')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1 text-sm font-bold">
                    نوع پیام
                    <select name="category" required class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm">
                        <option value="question" @selected(old('category') === 'question')>پرسش</option>
                        <option value="suggestion" @selected(old('category') === 'suggestion')>پیشنهاد</option>
                        <option value="complaint" @selected(old('category') === 'complaint')>انتقاد یا شکایت</option>
                        <option value="other" @selected(old('category') === 'other')>سایر</option>
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-bold">
                    موضوع
                    <input name="subject" value="{{ old('subject') }}" minlength="3" maxlength="180" required class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">
                    @error('subject')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1 text-sm font-bold sm:col-span-2">
                    متن پیام
                    <textarea name="body" rows="4" minlength="5" maxlength="10000" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm leading-7">{{ old('body') }}</textarea>
                    @error('body')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
                </label>
                <div class="sm:col-span-2">
                    <button class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-5 text-sm font-black text-white">ثبت پیام و شروع پیگیری</button>
                </div>
            </form>
        @endif
    </section>

    <section class="parent-panel dashboard-panel p-5 sm:p-7">
        <div>
            <h2 class="text-lg font-black">ارسال پیام مستقیم به مدرس</h2>
            <p class="mt-1 text-sm leading-7 text-slate-500">فقط مدرس‌های کلاس فعال فرزندت قابل انتخاب‌اند. پیام و پاسخ در همین بخش می‌ماند.</p>
        </div>
        @if($teacherTargets->isEmpty())
            <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-7 text-amber-900">برای این حساب، مدرس فعالی در کلاس ثبت‌نام‌شده و قابل‌دسترسی فرزند پیدا نشد.</p>
        @else
            <form method="POST" action="{{ route('parent.messages.teacher.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
                @csrf
                <label class="grid gap-1 text-sm font-bold sm:col-span-2">
                    مدرس، فرزند، دوره و کلاس
                    <select name="teacher_target" required class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm">
                        <option value="">انتخاب مدرس از کلاس فعال</option>
                        @foreach($teacherTargets as $target)
                            <option value="{{ $target->key }}" @selected(old('teacher_target') === $target->key)>{{ $target->teacher_name }} — {{ $target->student_name }} / {{ $target->course_title }} / {{ $target->classroom_title }}</option>
                        @endforeach
                    </select>
                    @error('teacher_target')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1 text-sm font-bold">
                    نوع پیام
                    <select name="teacher_category" required class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm">
                        <option value="question" @selected(old('teacher_category', 'question') === 'question')>پرسش</option>
                        <option value="suggestion" @selected(old('teacher_category') === 'suggestion')>پیشنهاد</option>
                        <option value="complaint" @selected(old('teacher_category') === 'complaint')>انتقاد یا شکایت</option>
                        <option value="other" @selected(old('teacher_category') === 'other')>سایر</option>
                    </select>
                    @error('teacher_category')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1 text-sm font-bold">
                    موضوع
                    <input name="teacher_subject" value="{{ old('teacher_subject') }}" minlength="3" maxlength="180" required class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">
                    @error('teacher_subject')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1 text-sm font-bold sm:col-span-2">
                    متن پیام
                    <textarea name="teacher_body" rows="4" minlength="5" maxlength="10000" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm leading-7">{{ old('teacher_body') }}</textarea>
                    @error('teacher_body')<span class="text-xs font-normal text-rose-600">{{ $message }}</span>@enderror
                </label>
                <div class="sm:col-span-2"><button class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-5 text-sm font-black text-white">ارسال پیام به مدرس</button></div>
            </form>
        @endif
    </section>

    <section class="space-y-4" aria-labelledby="parent-academy-notices-title">
        <div>
            <span class="text-xs font-black text-[var(--panel-accent)]">اطلاعیه‌های مهم</span>
            <h2 id="parent-academy-notices-title" class="mt-1 text-xl font-black">پیام‌های آموزشگاه و مدرس‌ها</h2>
            <p class="mt-1 text-sm leading-7 text-[var(--panel-muted)]">اطلاعیه‌های عمومی آموزشگاه، پیام‌های مخصوص کلاس فرزندت و پیام‌های مستقیم به حساب تو در اینجا نمایش داده می‌شوند.</p>
        </div>
        @forelse($academyMessages as $notice)
            @php
                $noticeLabel = match($notice->audience) {
                    'all_parents' => 'اطلاعیه عمومی',
                    'classroom_parents' => 'اطلاعیه کلاس ' . ($notice->classroom?->title ?? ''),
                    default => 'پیام خصوصی',
                };
                $noticeTone = $notice->category === 'important' ? 'rose' : ($notice->audience === 'all_parents' ? 'violet' : 'teal');
            @endphp
            <article class="parent-panel dashboard-panel overflow-hidden">
                <div class="h-1.5 {{ $noticeTone === 'rose' ? 'bg-rose-500' : ($noticeTone === 'violet' ? 'bg-violet-500' : 'bg-teal-500') }}"></div>
                <div class="p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="break-words text-lg font-black text-[var(--panel-text)]">{{ $notice->subject }}</h3>
                            <p class="mt-1 text-xs text-[var(--panel-muted)]">{{ $notice->academy?->name }} · {{ $notice->sender?->name }} · {{ \App\Support\PersianUi::date($notice->published_at ?? $notice->created_at) }}</p>
                        </div>
                        <span class="rounded-full px-3 py-1.5 text-xs font-black {{ $noticeTone === 'rose' ? 'bg-rose-50 text-rose-800' : ($noticeTone === 'violet' ? 'bg-violet-50 text-violet-800' : 'bg-teal-50 text-teal-800') }}">{{ $noticeLabel }}</span>
                    </div>
                    <p class="mt-4 whitespace-pre-line break-words rounded-xl bg-[var(--panel-bg)] p-4 text-sm leading-7 text-[var(--panel-text-secondary)]">{{ $notice->body }}</p>
                    @if($notice->student)
                        <p class="mt-3 text-xs text-[var(--panel-muted)]">دانش‌آموز مرتبط: {{ $notice->student->name }}</p>
                    @endif
                </div>
            </article>
        @empty
            <div class="parent-panel dashboard-panel p-6 text-center text-sm leading-7 text-[var(--panel-muted)]">فعلاً اطلاعیه‌ای برای خانواده‌ات منتشر نشده است.</div>
        @endforelse
        @if($academyMessages->hasPages())<div>{{ $academyMessages->onEachSide(1)->links() }}</div>@endif
    </section>

    <section class="space-y-4">
        <div><h2 class="text-xl font-black">سوابق پیام‌ها</h2><p class="mt-1 text-sm text-slate-500">پیام‌ها فقط برای حساب والد ثبت‌کننده نمایش داده می‌شوند.</p></div>
        @php($statuses = ['open' => 'در انتظار بررسی', 'in_review' => 'در حال پیگیری', 'responded' => 'پاسخ داده‌شده', 'closed' => 'بسته‌شده'])
        @php($categories = ['question' => 'پرسش', 'suggestion' => 'پیشنهاد', 'complaint' => 'انتقاد یا شکایت', 'other' => 'سایر'])
        @forelse($messages as $message)
            <article class="parent-panel dashboard-panel p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-[var(--panel-primary)]">{{ $message->student?->name }} · {{ $message->academy?->name }}</p>
                        <h3 class="mt-1 break-words text-lg font-black">{{ $message->subject }}</h3>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $statuses[$message->status] ?? $message->status }}</span>
                </div>
                <p class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-slate-700">{{ $message->body }}</p>
                <p class="mt-3 text-xs text-slate-500">{{ $categories[$message->category] ?? 'پیام' }} · {{ \App\Support\PersianUi::date($message->created_at) }}</p>
                @if($message->response)
                    <div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-xs font-black text-emerald-800">پاسخ آموزشگاه @if($message->responder) · {{ $message->responder->name }} @endif</p>
                        <p class="mt-2 whitespace-pre-line break-words text-sm leading-7 text-emerald-950">{{ $message->response }}</p>
                        @if($message->responded_at)<p class="mt-2 text-[11px] text-emerald-800">{{ \App\Support\PersianUi::date($message->responded_at) }} · {{ \App\Support\PersianUi::time($message->responded_at) }}</p>@endif
                    </div>
                @else
                    <p class="mt-3 text-xs text-slate-500">پاسخی ثبت نشده است؛ تغییر وضعیت از طرف آموزشگاه در این بخش دیده خواهد شد.</p>
                @endif
            </article>
        @empty
            <div class="parent-panel dashboard-panel p-8 text-center"><h3 class="font-black">هنوز پیامی ثبت نشده است.</h3><p class="mt-2 text-sm text-slate-500">از فرم بالا برای ارسال پرسش، پیشنهاد یا انتقاد استفاده کن.</p></div>
        @endforelse
        <div>{{ $messages->links() }}</div>
    </section>

    <section class="space-y-4">
        <div>
            <h2 class="text-xl font-black">پیام‌های ارسالی به مدرس</h2>
            <p class="mt-1 text-sm text-slate-500">پیام‌هایی که خودت آغاز کرده‌ای و پاسخ مدرس.</p>
        </div>
        @forelse($outgoingTeacherMessages as $message)
            <article class="parent-panel dashboard-panel p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="font-black">{{ $message->subject }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $message->teacher?->name }} · {{ $message->student?->name }} · {{ $message->course?->title }} / {{ $message->classroom?->title }}</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $message->status === 'responded' ? 'پاسخ داده شده' : 'در انتظار پاسخ مدرس' }}</span>
                </div>
                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $message->body }}</p>
                @if($message->response)
                    <div class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-xs font-black text-emerald-800">پاسخ مدرس @if($message->responder) · {{ $message->responder->name }} @endif</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-7">{{ $message->response }}</p>
                    </div>
                @else
                    <p class="mt-3 text-xs text-slate-500">پاسخ این پیام هنوز ثبت نشده است.</p>
                @endif
            </article>
        @empty
            <div class="parent-panel dashboard-panel p-6 text-center text-sm text-slate-500">هنوز پیام مستقیمی به مدرس ارسال نشده است.</div>
        @endforelse
        @if($outgoingTeacherMessages->hasPages())<div>{{ $outgoingTeacherMessages->links() }}</div>@endif
    </section>

    @include('components.communication.teacher-direct-messages-inbox', ['messages' => $teacherMessages, 'recipientType' => 'parent'])

</div>
@endsection
