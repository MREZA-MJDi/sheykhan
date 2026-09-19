@extends('layouts.app')

@section('title', 'دوره‌ها | شیخان')
@section('description', 'دوره‌های آموزشی منتشرشده در شیخان.')

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">
            <x-layout.page-header
                title="دوره‌های آموزشی"
                description="مسیر آموزشی مناسب خودت را از بین دوره‌های منتشرشده پیدا کن."
            />

            <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($courses as $course)
                    <x-education.course-card
                        :title="$course->title"
                        :description="$course->short_description ?: $course->description"
                        :category="$course->academy?->name"
                        :teacher="$course->teachers->first()?->name"
                        :lessons="$course->sections->sum(fn ($section) => $section->lessons->count())"
                        :duration="$course->duration_minutes > 0 ? floor($course->duration_minutes / 60) . ' ساعت' : 'مدت زمان متغیر'"
                        :price="$course->price > 0 ? number_format($course->price, 0, '.', ',') . ' تومان' : 'رایگان'"
                        :level="$course->level"
                        :image="$course->media->first()?->url()"
                        :href="route('courses.show', $course)"
                    />
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-[var(--color-border)] bg-white p-12 text-center text-[var(--color-text-muted)]">
                        هنوز دوره منتشرشده‌ای برای نمایش وجود ندارد.
                    </div>
                @endforelse
            </div>

            <div class="mt-10">
                <x-navigation.pagination :paginator="$courses" />
            </div>
        </x-layout.container>
    </x-layout.section>
@endsection
