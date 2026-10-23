@extends('layouts.teacher')

@section('title', 'پیام مستقیم | شیخان')
@section('header-title', 'پیام مستقیم')

@section('content')
<div class="space-y-6">
    <header class="dashboard-panel p-5 sm:p-7">
        <p class="text-xs font-black text-[var(--panel-primary)]">ارتباط آموزشی</p>
        <h1 class="mt-1 text-2xl font-black">ارسال پیام به دانش‌آموز یا ولی</h1>
        <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">فقط مخاطبان فعالی را می‌بینی که به کلاس‌های تخصیص‌یافتهٔ تو مرتبط‌اند. پاسخ مخاطب در سابقه همین صفحه نمایش داده می‌شود.</p>
    </header>
    @if(session('success'))<div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm leading-7 text-rose-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

    <section class="dashboard-panel p-5 sm:p-7">
        <h2 class="text-lg font-black">پیام جدید</h2>
        @if($targets->isEmpty())
            <p class="mt-4 rounded-xl bg-amber-50 p-4 text-sm leading-7 text-amber-800">برای ارسال پیام باید در یک کلاس فعال مدرس باشی و دانش‌آموز یا ولی به همان کلاس و آموزشگاه متصل باشد.</p>
        @else
            <form method="POST" action="{{ route('teacher.direct-messages.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf
                <label class="grid gap-1 md:col-span-2"><span class="text-xs font-bold">مخاطب</span>
                    <select name="target" required class="min-h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm">
                        <option value="">انتخاب مخاطب مجاز</option>
                        @foreach($targets as $target)
                            <option value="{{ $target->key }}" @selected(old('target') === $target->key)>{{ $target->recipient_name }} — {{ $target->student_name }} / {{ $target->classroom_title }} ({{ $target->recipient_type === 'parent' ? 'ولی' : 'دانش‌آموز' }})</option>
                        @endforeach
                    </select>
                    @error('target')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror
                </label>
                <label class="grid gap-1"><span class="text-xs font-bold">نوع پیام</span>
                    <select name="category" required class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">
                        <option value="question">پرسش</option><option value="suggestion">پیشنهاد</option><option value="complaint">انتقاد یا شکایت</option><option value="other">سایر</option>
                    </select>
                </label>
                <label class="grid gap-1"><span class="text-xs font-bold">موضوع</span><input name="subject" value="{{ old('subject') }}" minlength="3" maxlength="180" required class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm" placeholder="مثلاً پیگیری تمرین هفته">@error('subject')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1 md:col-span-2"><span class="text-xs font-bold">متن پیام</span><textarea name="body" rows="4" minlength="5" maxlength="10000" required class="rounded-xl border border-slate-200 px-3 py-3 text-sm leading-7">{{ old('body') }}</textarea>@error('body')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                <button class="md:col-span-2 min-h-11 rounded-xl bg-[var(--panel-primary)] px-5 text-sm font-black text-white">ارسال پیام</button>
            </form>
        @endif
    </section>

    <section class="space-y-4">
        <h2 class="text-xl font-black">پیام‌های ارسال‌شده</h2>
        @forelse($messages as $message)
            <article class="dashboard-panel p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="font-black">{{ $message->subject }}</h3>
                        @if(($message->sender_type ?? 'teacher') === 'parent')
                            <p class="mt-1 text-xs text-slate-500">پیام دریافتی از والد: {{ $message->sender?->name }} · فرزند {{ $message->student?->name }} · {{ $message->course?->title }} / {{ $message->classroom?->title }}</p>
                        @else
                            <p class="mt-1 text-xs text-slate-500">ارسال‌شده به {{ $message->recipient_type === 'parent' ? 'ولی' : 'دانش‌آموز' }}: {{ $message->recipient?->name }} · {{ $message->student?->name }} · {{ $message->classroom?->title }}</p>
                        @endif
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $message->status === 'responded' ? 'پاسخ داده شده' : ((($message->sender_type ?? 'teacher') === 'parent') ? 'در انتظار پاسخ شما' : 'ارسال شده') }}</span>
                </div>
                <p class="mt-3 whitespace-pre-line text-sm leading-7 text-slate-700">{{ $message->body }}</p>
                @if(($message->sender_type ?? 'teacher') === 'parent' && $message->status === 'sent')
                    <form method="POST" action="{{ route('teacher.direct-messages.reply', $message->id) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4">
                        @csrf @method('PATCH')
                        <label class="grid gap-1 text-sm font-bold">پاسخ به والد<textarea name="response" rows="3" minlength="2" maxlength="5000" required class="rounded-xl border border-slate-300 px-3 py-3 text-sm leading-7"></textarea></label>
                        <button class="min-h-10 justify-self-start rounded-xl bg-[var(--panel-primary)] px-5 text-sm font-black text-white">ارسال پاسخ</button>
                    </form>
                @elseif($message->response)
                    <div class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm leading-7">
                        {{ $message->response }}
                        <p class="mt-2 text-xs text-emerald-800">{{ (($message->sender_type ?? 'teacher') === 'parent') ? 'پاسخ شما' : ('پاسخ از طرف ' . ($message->responder?->name ?? 'مخاطب')) }}</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="dashboard-panel p-8 text-center text-sm text-slate-500">هنوز پیامی از این صفحه ارسال نشده است.</div>
        @endforelse
        <div>{{ $messages->links() }}</div>
    </section>
</div>
@endsection
