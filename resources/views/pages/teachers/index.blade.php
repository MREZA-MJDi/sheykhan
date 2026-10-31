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
                            <x-education.teacher-card
                                :name="trim((string) $teacher->name) ?: 'مدرس شیخان'"
                                :role="$teacher->teacherProfile?->specialization ?: 'مدرس'"
                                :avatar="$teacher->teacherProfile?->media?->first()?->url()"
                                :bio="$teacher->teacherProfile?->bio"
                                :courses="$teacher->courses_count ?? 0"
                                 :href="route('teachers.show', $teacher)"
                            />
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
