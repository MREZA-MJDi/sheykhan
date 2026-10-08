@extends('layouts.teacher')
@section('title','دانش‌آموزان | شیخان')
@section('header-title','دانش‌آموزان')
@section('content')
<div class="grid gap-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <p class="text-xs font-black text-[var(--panel-primary)]">دانش‌آموزان من</p>
        <h2 class="mt-1 text-2xl font-black">پیگیری یادگیری</h2>
        <p class="mt-2 text-sm text-slate-500">دانش‌آموزانی که در کلاس‌های شما هستند.</p>
    </div>
    <div class="flex flex-wrap gap-2" aria-label="مرتب‌سازی دانش‌آموزان">
        @foreach([
            'name' => 'نام',
            'progress' => 'درس‌های دیده‌شده',
            'assignments' => 'تکالیف',
            'exams' => 'آزمون‌ها',
        ] as $key => $label)
            <a
                href="{{ request()->fullUrlWithQuery(['sort' => $key, 'direction' => $studentSort === $key && $studentDirection === 'asc' ? 'desc' : 'asc', 'page' => null]) }}"
                class="rounded-lg border px-3 py-2 text-[10px] font-black {{ $studentSort === $key ? 'border-[var(--panel-primary)] text-[var(--panel-primary)]' : 'border-slate-200 text-slate-500' }}"
            >
                {{ $label }}{{ $studentSort === $key ? ($studentDirection === 'asc' ? ' ↑' : ' ↓') : '' }}
            </a>
        @endforeach
    </div>
</div>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="overflow-x-auto"><table class="min-w-[680px] w-full text-right"><thead class="bg-slate-50 text-[10px] font-black text-slate-500"><tr><th class="px-5 py-4">دانش‌آموز</th><th class="px-5 py-4">پایه</th><th class="px-5 py-4">درس‌های دیده‌شده</th><th class="px-5 py-4">تکالیف</th><th class="px-5 py-4">آزمون‌ها</th></tr></thead><tbody class="divide-y divide-slate-100">
        @forelse($students as $student)
            <tr><td class="px-5 py-4"><strong class="text-sm">{{ $student->name }}</strong><div class="mt-1 text-[10px] text-slate-500">{{ $student->email }}</div></td><td class="px-5 py-4 text-xs">{{ $student->studentProfile?->grade ?? '—' }}</td><td class="px-5 py-4 text-xs">{{ \App\Support\PersianUi::digits($student->progress_items_count) }}</td><td class="px-5 py-4 text-xs">{{ \App\Support\PersianUi::digits($student->assignment_submissions_count) }}</td><td class="px-5 py-4 text-xs">{{ \App\Support\PersianUi::digits($student->exam_attempts_count) }}</td></tr>
        @empty
            <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">دانش‌آموزی در کلاس‌های شما ثبت نشده است.</td></tr>
        @endforelse
        </tbody></table></div>
    </div>

    @if($students->hasPages())
        <div class="flex justify-center">
            {{ $students->links() }}
        </div>
    @endif
</div>
@endsection