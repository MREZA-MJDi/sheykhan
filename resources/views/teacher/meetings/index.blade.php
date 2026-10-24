@extends('layouts.teacher')

@section('title', 'جلسات با اولیا | شیخان')
@section('header-title', 'جلسات با اولیا')

@section('content')
<div class="space-y-6">
    <header><p class="text-xs font-black text-[var(--panel-primary)]">هماهنگی آموزشگاه</p><h1 class="mt-1 text-2xl font-black">جلسات برنامه‌ریزی‌شده با اولیا</h1><p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">جلسه‌های مربوط به کلاس‌های خودت را پیگیری کن، برای هماهنگی زمان پیشنهادی به آموزشگاه بفرست و پس از تأیید جلسه، نتیجهٔ برگزاری را ثبت کن.</p></header>
    <section class="dashboard-panel p-5 sm:p-6">
        <h2 class="text-lg font-black">پیشنهاد زمان به ادمین / منشی</h2>
        <p class="mt-1 text-sm leading-7 text-slate-500">زمان پیشنهادی را همراه با دانش‌آموز و ولیِ مرتبط بفرست. تا زمان تأیید آموزشگاه، جلسه برای خانواده قطعی نمایش داده نمی‌شود.</p>
        @if($targets->isEmpty())
            <p class="mt-4 rounded-xl bg-amber-50 p-4 text-sm leading-7 text-amber-800">برای پیشنهاد جلسه، کلاس فعال با دانش‌آموز و ولی متصل که در حوزه تدریس تو باشد لازم است.</p>
        @else
            <form method="POST" action="{{ route('teacher.meetings.proposals.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
                @csrf
                <label class="grid gap-1 md:col-span-2"><span class="text-xs font-bold">کلاس، دانش‌آموز و ولی</span><select name="meeting_target" required class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm"><option value="">انتخاب مخاطب</option>@foreach($targets as $target)<option value="{{ $target->classroom_id }}:{{ $target->student_id }}:{{ $target->parent_id }}" @selected(old('meeting_target') === $target->classroom_id.':'.$target->student_id.':'.$target->parent_id)>{{ $target->classroom_title }} — {{ $target->student_name }} — ولی: {{ $target->parent_name }}</option>@endforeach</select>@error('meeting_target')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1 md:col-span-2"><span class="text-xs font-bold">موضوع جلسه</span><input name="title" value="{{ old('title') }}" required minlength="3" maxlength="255" placeholder="مثلاً بررسی روند پیشرفت ریاضی" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">@error('title')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1"><span class="text-xs font-bold">زمان پیشنهادی شروع</span><input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" min="{{ now()->format('Y-m-d\\TH:i') }}" required class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">@error('scheduled_at')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1"><span class="text-xs font-bold">زمان پیشنهادی پایان (اختیاری)</span><input type="datetime-local" name="scheduled_end_at" value="{{ old('scheduled_end_at') }}" min="{{ now()->format('Y-m-d\\TH:i') }}" class="min-h-11 rounded-xl border border-slate-200 px-3 text-sm">@error('scheduled_end_at')<span class="text-xs text-rose-600">{{ $message }}</span>@enderror</label>
                <label class="grid gap-1 md:col-span-2"><span class="text-xs font-bold">توضیح برای آموزشگاه و خانواده (اختیاری)</span><textarea name="message" rows="3" maxlength="1500" class="rounded-xl border border-slate-200 px-3 py-3 text-sm leading-7">{{ old('message') }}</textarea></label>
                @if($errors->any())<div class="md:col-span-2 rounded-xl bg-rose-50 p-3 text-xs leading-6 text-rose-700" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
                <button type="submit" class="md:col-span-2 min-h-11 rounded-xl bg-[var(--panel-primary)] px-5 text-sm font-black text-white">ارسال پیشنهاد به آموزشگاه</button>
            </form>
        @endif
    </section>
    <section class="space-y-4">
        @php($labels = ['proposed' => 'در انتظار تأیید آموزشگاه', 'scheduled' => 'برنامه‌ریزی‌شده', 'completed' => 'برگزارشده', 'cancelled' => 'لغوشده', 'declined' => 'ردشده'])
        @forelse($meetings as $meeting)
            <article class="dashboard-panel p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0"><p class="text-xs font-bold text-[var(--panel-primary)]">{{ $meeting->academy?->name }} · {{ $meeting->classroom?->title ?: $meeting->course?->title }}</p><h2 class="mt-1 break-words text-lg font-black">{{ $meeting->title }}</h2></div>
                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $labels[$meeting->status] ?? $meeting->status }}</span>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">دانش‌آموز</p><p class="mt-1 text-sm font-bold">{{ $meeting->student?->name ?: '—' }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">ولی / سرپرست</p><p class="mt-1 text-sm font-bold">{{ $meeting->guardian?->name ?: '—' }}</p></div>
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs text-slate-500">زمان</p><p class="mt-1 text-sm font-bold">{{ \App\Support\PersianUi::date($meeting->scheduled_at) }} · {{ \App\Support\PersianUi::time($meeting->scheduled_at) }}</p></div>
                </div>
                @if($meeting->message)<p class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-600">{{ $meeting->message }}</p>@endif
                @if($meeting->status === 'scheduled')
                    <form method="POST" action="{{ route('teacher.meetings.status', $meeting->id) }}" class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                        @csrf @method('PATCH')
                        <button name="status" value="completed" class="min-h-10 rounded-xl bg-emerald-600 px-4 text-sm font-black text-white">ثبت برگزاری جلسه</button>
                        <button name="status" value="cancelled" class="min-h-10 rounded-xl border border-rose-200 bg-white px-4 text-sm font-bold text-rose-700">ثبت لغو جلسه</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="dashboard-panel p-10 text-center"><h2 class="font-black">جلسه‌ای برای کلاس‌های شما ثبت نشده است.</h2><p class="mt-2 text-sm leading-7 text-slate-500">جلسه‌هایی که توسط ادمین/منشی برای دانش‌آموزان کلاس شما ثبت شوند اینجا نمایش داده می‌شوند.</p></div>
        @endforelse
        <div>{{ $meetings->links() }}</div>
    </section>
</div>
@endsection
