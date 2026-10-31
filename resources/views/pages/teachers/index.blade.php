@extends('layouts.app')

@section('title', 'مدرس‌ها | شیخان')
@section('description', 'مدرس‌های تاییدشده و قابل نمایش شیخان.')

@section('content')
    <section class="public-teachers-page">
        <section class="relative py-16 sm:py-20 lg:py-28">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
                <header class="public-page-intro mb-10 sm:mb-12">
                    <div class="max-w-3xl">
                        <span class="ui-eyebrow mb-4">مدرس‌های شیخان</span>

                        <h1 class="text-3xl font-black leading-tight tracking-tight text-[var(--color-text)] sm:text-4xl lg:text-5xl">
                            مدرس‌های تاییدشده
                        </h1>

                        <p class="mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-secondary)] sm:text-base">
                            مدرس‌های قابل نمایش را ببین و با تخصص و مسیرهای آموزشی آن‌ها آشنا شو.
                        </p>
                    </div>

                    @if($teachers->total())
                        <div class="mt-6 flex flex-wrap items-center gap-3 text-sm">
                            <span class="inline-flex items-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-2.5 font-semibold text-[var(--color-text-muted)]">
                                <strong class="font-black text-[var(--color-text)]">
                                    {{ \App\Support\PersianUi::digits($teachers->total()) }}
                                </strong>
                                مدرس
                            </span>

                            @if($teachers->hasPages())
                                <span class="text-[var(--color-text-subtle)]">
                                    صفحه {{ \App\Support\PersianUi::digits($teachers->currentPage()) }}
                                    از {{ \App\Support\PersianUi::digits($teachers->lastPage()) }}
                                </span>
                            @endif
                        </div>
                    @endif
                </header>

                @if($teachers->count())
                    <div class="teacher-directory-grid grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3">
                        @foreach($teachers as $teacher)
                            @php
                                $teacherDisplayName = trim((string) $teacher->name) ?: 'مدرس شیخان';
                                $teacherAvatar = $teacher->teacherProfile?->media?->first()?->url();
                                $teacherRole = $teacher->teacherProfile?->specialization ?: 'مدرس';
                            @endphp
                            <article class="public-teacher-card teacher-card group h-full min-w-0">
                                <a href="{{ route('teachers.show', $teacher) }}" class="teacher-card__media" aria-label="مشاهده پروفایل {{ $teacherDisplayName }}">
                                    @if($teacherAvatar)
                                        <img src="{{ $teacherAvatar }}" alt="تصویر {{ $teacherDisplayName }}" class="teacher-card__photo" loading="lazy" decoding="async">
                                    @else
                                        <span class="teacher-card__fallback" aria-hidden="true">{{ mb_substr($teacherDisplayName,0,1) }}</span>
                                    @endif
                                    <span class="teacher-card__media-overlay" aria-hidden="true"></span>
                                    <span class="teacher-card__media-action">پروفایل مدرس <span aria-hidden="true">↗</span></span>
                                </a>
                                <div class="teacher-card__body">
                                    <div class="teacher-card__identity">
                                        <h3 class="teacher-card__name"><a href="{{ route('teachers.show', $teacher) }}">{{ $teacherDisplayName }}</a></h3>
                                        <p class="teacher-card__role">{{ $teacherRole }}</p>
                                    </div>
                                    @if(filled($teacher->teacherProfile?->bio))
                                        <p class="teacher-card__bio">{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $teacher->teacherProfile->bio)),155) }}</p>
                                    @endif
                                    <div class="teacher-card__footer">
                                        <span class="teacher-card__course-count"><span aria-hidden="true">▤</span>{{ \App\Support\PersianUi::digits((int) ($teacher->courses_count ?? 0)) }} دوره آموزشی</span>
                                        <a href="{{ route('teachers.show', $teacher) }}" class="teacher-card__action">دیدن پروفایل <span aria-hidden="true">←</span></a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="public-empty-state" role="status">
        <span class="public-empty-state__icon" aria-hidden="true">⌕</span>
        <h3>هنوز مدرس قابل نمایشی وجود ندارد</h3>
        <p>به‌محض ثبت و تایید مدرس، اطلاعات او در این بخش نمایش داده می‌شود.</p>
    </div>
                @endif

                @if($teachers->hasPages())
                    <div class="mt-10 border-t border-[var(--color-border)] pt-8 sm:mt-12">
                        {{ $teachers->links() }}
                    </div>
                @endif
            </div>
        </section>
    </section>
    <section class="public-next-step public-next-step--teachers-index" aria-label="قدم بعدی">
    <div class="mx-auto w-full max-w-[1600px] px-4 sm:px-6 lg:px-8 xl:px-10">
        <div class="public-next-step__card">
            <div class="public-next-step__copy">
                <span>قدم بعدی</span>
                <h2>مسیر یادگیریت را ادامه بده</h2>
                <p>از دوره‌ها و مدرس‌ها دیدن کن و با توجه به نیازت، قدم بعدی را انتخاب کن.</p>
            </div>
            <div class="public-next-step__actions">
                <a class="public-next-step__button public-next-step__button--primary" href="/courses">مشاهده دوره‌ها <span aria-hidden="true">←</span></a>
                <a class="public-next-step__button public-next-step__button--secondary" href="/teachers">آشنایی با مدرس‌ها <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </div>
</section>
@endsection
