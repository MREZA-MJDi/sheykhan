@extends('layouts.owner')

@section('title', $assignment->title . ' | تکالیف')
@section('header-title', 'بررسی تکلیف')

@section('content')
<div class="space-y-6 panel-page-enter">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-black text-[var(--panel-primary)]">کارتابل ادمین / منشی</p>
            <h1 class="mt-1 text-2xl font-black">{{ $assignment->title }}</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">{{ $assignment->course?->title ?? 'دوره' }} · {{ $assignment->classroom?->title ?? 'کلاس' }} · مدرس مسئول: {{ $assignment->teacher?->name ?? '—' }}</p>
        </div>
        <a href="{{ route('owner.assignments.index', $academy) }}" class="text-xs font-black text-[var(--panel-primary)]">بازگشت به فهرست ←</a>
    </header>

    <section class="dashboard-panel p-5 sm:p-7">
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">{{ $assignment->status === 'published' ? 'منتشرشده' : ($assignment->status === 'closed' ? 'بسته' : 'پیش‌نویس') }}</span>
            <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">نمره کل: {{ \App\Support\PersianUi::digits($assignment->max_score ?? 20) }}</span>
            <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">مهلت: {{ $assignment->due_at ? \App\Support\PersianUi::date($assignment->due_at).' · '.\App\Support\PersianUi::time($assignment->due_at) : 'تعیین‌نشده' }}</span>
        </div>
        @if($assignment->instructions)
            <div class="mt-4 whitespace-pre-line rounded-2xl bg-slate-50 p-4 text-sm leading-8 text-slate-700">{{ $assignment->instructions }}</div>
        @endif
        @if($assignment->media?->isNotEmpty())
            <div class="mt-4 grid gap-2">
                @foreach($assignment->media as $handout)
                    <a href="{{ route('media.view', $handout) }}" target="_blank" rel="noopener" class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-3 text-xs font-bold text-[var(--panel-primary)]">
                        <span class="min-w-0 truncate">{{ $handout->original_name ?? 'فایل راهنما' }}</span><span>مشاهده امن ↗</span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="dashboard-panel p-5 sm:p-7">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div><h2 class="text-base font-black">پاسخ‌های ارسال‌شده</h2><p class="mt-1 text-xs leading-6 text-slate-500">هر پاسخ را بررسی کن، نمره بده و بازخورد را برای دانش‌آموز ذخیره کن.</p></div>
            <span class="rounded-full bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600">{{ \App\Support\PersianUi::digits($submissions->total()) }} ارسال</span>
        </div>

        <div class="grid gap-4">
            @forelse($submissions as $submission)
                <article class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0 flex-1">
                            <strong class="block text-sm font-black">{{ $submission->student?->name ?? 'دانش‌آموز در دسترس نیست' }}</strong>
                            
                            <p class="mt-1 text-xs text-slate-500">ارسال: {{ $submission->submitted_at ? \App\Support\PersianUi::date($submission->submitted_at).' · '.\App\Support\PersianUi::time($submission->submitted_at) : '—' }}</p>
                            <div class="mt-3 whitespace-pre-line rounded-xl bg-white p-4 text-sm leading-8 text-slate-700">{{ $submission->content ?: 'پاسخ متنی ندارد؛ پیوست‌ها را بررسی کن.' }}</div>
                            @if($submission->media?->isNotEmpty())
                                <div class="mt-3 grid gap-2">
                                    @foreach($submission->media as $attachment)
                                        <a href="{{ route('media.view', $attachment) }}" target="_blank" rel="noopener" class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 text-xs font-bold text-[var(--panel-primary)]">
                                            <span class="min-w-0 truncate">{{ $attachment->original_name ?? 'پیوست دانش‌آموز' }}</span><span>مشاهده امن ↗</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                            @if($submission->graded_at)
                                <div class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50 p-3 text-xs leading-6 text-emerald-800">
                                    <strong>نمره ثبت‌شده: {{ \App\Support\PersianUi::digits($submission->score ?? 0) }}</strong>
                                    @if($submission->feedback)<p class="mt-1">{{ $submission->feedback }}</p>@endif
                                </div>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('owner.assignments.grade', [$academy, $assignment, $submission]) }}" class="grid w-full gap-3 xl:max-w-sm" @if($submission->graded_at) data-confirm="نمره این پاسخ بازنویسی شود؟" @endif>
                            @csrf @method('PATCH')
                            <label class="grid gap-1"><span class="text-xs font-bold">نمره</span><input type="number" name="score" min="0" max="{{ $assignment->max_score ?? 999999 }}" step="0.01" value="{{ old('score', $submission->score) }}" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm"><x-owner.field-error field="score"/></label>
                            <label class="grid gap-1"><span class="text-xs font-bold">بازخورد</span><textarea name="feedback" rows="4" maxlength="5000" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm">{{ old('feedback', $submission->feedback) }}</textarea><x-owner.field-error field="feedback"/></label>
                            <button type="submit" class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">{{ $submission->graded_at ? 'به‌روزرسانی تصحیح' : 'ثبت نمره و بازخورد' }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center">
                    <strong class="text-sm font-black">هنوز پاسخی ارسال نشده است.</strong>
                    <p class="mt-2 text-xs leading-6 text-slate-500">پاسخ‌هایی که دانش‌آموزان از پنل خودشان ارسال کنند، با متن و پیوست اینجا نمایش داده می‌شوند.</p>
                </div>
            @endforelse
        </div>

        @if($submissions->hasPages())
            <div class="mt-5 border-t border-slate-100 pt-4 text-sm">{{ $submissions->links('components.navigation.pagination') }}</div>
        @endif
    </section>
</div>
@endsection
