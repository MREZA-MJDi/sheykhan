@extends('layouts.app')

@section('title', 'مدرس‌ها | شیخان')
@section('description', 'مدرس‌های تاییدشده شیخان و تخصص‌های آموزشی آن‌ها.')

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">
            <x-layout.page-header
                title="مدرس‌های شیخان"
                description="مدرس‌های تاییدشده و فعال پلتفرم."
            />

            <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @forelse ($teachers as $teacher)
                    <x-education.teacher-card
                        :name="$teacher->name"
                        :role="$teacher->teacherProfile?->specialization ?: 'مدرس'"
                        :avatar="$teacher->teacherProfile?->media->first()?->url()"
                        :bio="$teacher->teacherProfile?->bio"
                        :courses="$teacher->courses_count"
                        :href="route('teachers.index')"
                    />
                @empty
                    <div class="sm:col-span-2 xl:col-span-4 rounded-2xl border border-dashed border-[var(--color-border)] p-12 text-center text-[var(--color-text-muted)]">
                        هنوز مدرس تاییدشده‌ای برای نمایش وجود ندارد.
                    </div>
                @endforelse
            </div>

            <div class="mt-10">
                <x-navigation.pagination :paginator="$teachers" />
            </div>
        </x-layout.container>
    </x-layout.section>
@endsection
