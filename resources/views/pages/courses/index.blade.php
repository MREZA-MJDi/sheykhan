@extends('layouts.app')

@section('title', 'دوره‌ها | شیخان')
@section('description', 'دوره‌های آموزشی منتشرشده در شیخان.')

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <x-layout.section-heading
                eyebrow="کاتالوگ آموزش"
                title="دوره‌های آموزشی"
                description="دوره‌های منتشرشده را بر اساس مسیر، سطح و موضوع انتخاب کن."
            />

            <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
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
                    <div class="sm:col-span-2 xl:col-span-3">
                        <x-ui.empty-state
                            title="دوره‌ای برای نمایش وجود ندارد"
                            description="هنوز دوره‌ای با وضعیت انتشار فعال پیدا نشد."
                        />
                    </div>
                @endforelse
            </div>

            @if($courses->hasPages())
                <div class="mt-10 flex justify-center">
                    <x-navigation.pagination :paginator="$courses" />
                </div>
            @endif
        </x-layout.container>
    </x-layout.section>
@endsection
