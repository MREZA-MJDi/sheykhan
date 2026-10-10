@extends('layouts.app')

@section('title', 'مدرس‌ها | شیخان')
@section('description', 'مدرس‌های تاییدشده و قابل نمایش شیخان.')

@section('content')
    <section class="public-teachers-page">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">
                <header class="mb-10 sm:mb-12">
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
                    <x-ui.empty-state
                        title="هنوز مدرس قابل نمایشی وجود ندارد"
                        description="به‌محض ثبت و تایید مدرس، اطلاعات او در این بخش نمایش داده می‌شود."
                    />
                @endif

                @if($teachers->hasPages())
                    <div class="mt-10 border-t border-[var(--color-border)] pt-8 sm:mt-12">
                        <x-navigation.pagination :paginator="$teachers" />
                    </div>
                @endif
            </x-layout.container>
        </x-layout.section>
    </section>
@endsection