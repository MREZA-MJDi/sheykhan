@extends('layouts.student')

@section('title', ($achievement->title ?: $achievement->display_name) . ' | دستاورد | شیخان')
@section('header-title', 'جزئیات دستاورد')

@section('content')
<div class="student-workspace-page">
    <header class="student-workspace-head">
        <div class="student-workspace-head-copy">
            <span class="student-workspace-kicker">افتخار</span>
            <h1 class="student-workspace-title">{{ $achievement->title ?: $achievement->display_name }}</h1>
            <p class="student-workspace-description">
                جزئیات و مدرک ثبت‌شده این دستاورد.
            </p>
        </div>

        <a class="student-workspace-btn secondary" href="{{ route('student.achievements.index') }}">
            بازگشت به دستاوردها
        </a>
    </header>

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px]">
        <section class="student-workspace-card overflow-hidden" aria-labelledby="achievement-media-title">
            <div class="student-workspace-card-head">
                <div>
                    <h2 id="achievement-media-title">مدرک دستاورد</h2>
                    <p>نمایش فایل ثبت‌شده بدون خروج از پنل دانش‌آموز.</p>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                @if($achievement->media)
                    @php($mime = strtolower((string) $achievement->media->mime_type))

                    @if(str_starts_with($mime, 'image/'))
                        <div class="overflow-hidden rounded-2xl border border-[var(--panel-border)] bg-slate-50">
                            <img
                                src="{{ route('media.view', $achievement->media) }}"
                                alt="{{ $achievement->title ?: $achievement->display_name }}"
                                class="mx-auto max-h-[70vh] w-full object-contain"
                                loading="eager"
                            >
                        </div>
                    @elseif($mime === 'application/pdf')
                        <div class="overflow-hidden rounded-2xl border border-[var(--panel-border)] bg-slate-50">
                            <iframe
                                src="{{ route('media.view', $achievement->media) }}"
                                title="مدرک {{ $achievement->title ?: $achievement->display_name }}"
                                class="h-[70vh] w-full"
                            ></iframe>
                        </div>
                    @else
                        <div class="student-workspace-empty">
                            <strong>پیش‌نمایش مستقیم این نوع فایل در این صفحه ممکن نیست.</strong>
                            <a
                                class="student-workspace-btn primary mt-4"
                                href="{{ route('media.view', $achievement->media) }}"
                            >
                                باز کردن فایل
                            </a>
                        </div>
                    @endif
                @else
                    <div class="student-workspace-empty">
                        <strong>فایلی برای این دستاورد ثبت نشده است.</strong>
                        <span>اطلاعات متنی دستاورد همچنان در دسترس است.</span>
                    </div>
                @endif
            </div>
        </section>

        <aside class="student-workspace-card h-fit">
            <div class="student-workspace-card-head">
                <div>
                    <h2>اطلاعات دستاورد</h2>
                    <p>جزئیات ثبت‌شده برای این سابقه.</p>
                </div>
            </div>

            <div class="grid gap-3 p-4 sm:p-5">
                <div class="rounded-xl bg-slate-50 p-4">
                    <span class="block text-xs font-bold text-slate-500">عنوان</span>
                    <strong class="mt-1 block text-sm font-black text-slate-900">
                        {{ $achievement->title ?: $achievement->display_name }}
                    </strong>
                </div>

                @if($achievement->school_name)
                    <div class="rounded-xl bg-slate-50 p-4">
                        <span class="block text-xs font-bold text-slate-500">مدرسه / مؤسسه</span>
                        <strong class="mt-1 block text-sm font-black text-slate-900">{{ $achievement->school_name }}</strong>
                    </div>
                @endif

                @if($achievement->achievement_type)
                    <div class="rounded-xl bg-slate-50 p-4">
                        <span class="block text-xs font-bold text-slate-500">نوع افتخار</span>
                        <strong class="mt-1 block text-sm font-black text-slate-900">{{ $achievement->achievement_type }}</strong>
                    </div>
                @endif

                @if($achievement->grade)
                    <div class="rounded-xl bg-slate-50 p-4">
                        <span class="block text-xs font-bold text-slate-500">پایه</span>
                        <strong class="mt-1 block text-sm font-black text-slate-900">{{ $achievement->grade->title }}</strong>
                    </div>
                @endif

                @if($achievement->academicYear)
                    <div class="rounded-xl bg-slate-50 p-4">
                        <span class="block text-xs font-bold text-slate-500">سال تحصیلی</span>
                        <strong class="mt-1 block text-sm font-black text-slate-900">{{ $achievement->academicYear->title }}</strong>
                    </div>
                @endif

                @if($achievement->description)
                    <div class="rounded-xl bg-slate-50 p-4">
                        <span class="block text-xs font-bold text-slate-500">توضیحات</span>
                        <p class="mt-2 text-sm leading-7 text-slate-600">{{ $achievement->description }}</p>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection
