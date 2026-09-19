@extends('layouts.teacher')

@section('title', 'دوره‌های من | شیخان')

@section('content')
    <div class="space-y-6 panel-page-enter">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold text-[var(--color-brand-600)]">مدرس</p>
                <h1 class="mt-2 text-2xl font-black text-[var(--color-text)]">دوره‌های من</h1>
                <p class="mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">
                    دوره‌های آموزشی، قیمت‌گذاری و فایل‌های آموزشی خودت را مدیریت کن.
                </p>
            </div>

            <a href="{{ route('teacher.courses.create') }}"
               class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[var(--color-brand-600)] px-4 text-sm font-black text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-[var(--color-brand-700)]">
                + ساخت دوره
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-[var(--color-success-100)] bg-[var(--color-success-50)] px-4 py-3 text-sm font-semibold text-[var(--color-success-700)]">
                {{ session('success') }}
            </div>
        @endif

        <section class="panel-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-right">
                    <thead class="border-b border-[var(--color-border)] bg-[var(--color-background-soft)]">
                        <tr>
                            <th class="px-5 py-4 text-xs font-black text-[var(--color-text-muted)]">دوره</th>
                            <th class="px-5 py-4 text-xs font-black text-[var(--color-text-muted)]">آموزشگاه</th>
                            <th class="px-5 py-4 text-xs font-black text-[var(--color-text-muted)]">دسترسی</th>
                            <th class="px-5 py-4 text-xs font-black text-[var(--color-text-muted)]">وضعیت</th>
                            <th class="px-5 py-4 text-xs font-black text-[var(--color-text-muted)]">عملیات</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[var(--color-border)]">
                        @forelse($courses as $course)
                            <tr class="transition hover:bg-[var(--color-background-soft)]">
                                <td class="px-5 py-4">
                                    <div class="font-bold text-[var(--color-text)]">{{ $course->title }}</div>
                                    <div class="mt-1 text-xs text-[var(--color-text-muted)]">{{ $course->sections_count }} بخش</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-[var(--color-text-secondary)]">{{ $course->academy?->name }}</td>
                                <td class="px-5 py-4">
                                    @if($course->isFree())
                                        <span class="rounded-full bg-[var(--color-success-50)] px-2.5 py-1 text-[11px] font-bold text-[var(--color-success-700)]">رایگان</span>
                                    @else
                                        <div>
                                            <span class="rounded-full bg-[var(--color-warning-50)] px-2.5 py-1 text-[11px] font-bold text-[var(--color-warning-700)]">پولی</span>
                                            <div class="mt-1 text-xs font-bold text-[var(--color-text)]">{{ number_format((float) $course->price, 0, '.', ',') }} تومان</div>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $course->status === 'published' ? 'bg-[var(--color-success-50)] text-[var(--color-success-700)]' : 'bg-[var(--color-slate-100)] text-[var(--color-text-muted)]' }}">
                                        {{ $course->status === 'published' ? 'منتشرشده' : ($course->status === 'draft' ? 'پیش‌نویس' : 'آرشیو') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <a href="{{ route('teacher.courses.edit', $course) }}" class="text-sm font-black text-[var(--color-brand-600)] hover:text-[var(--color-brand-700)]">مدیریت دوره ←</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-16 text-center">
                                    <div class="text-sm font-bold">هنوز دوره‌ای نداری.</div>
                                    <p class="mt-1 text-xs text-[var(--color-text-muted)]">اولین دوره را بساز و محتوای آن را اضافه کن.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
