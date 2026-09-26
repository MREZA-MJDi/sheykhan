@extends('layouts.app')

@section('title', 'مدرس‌ها | شیخان')
@section('description', 'مدرس‌های تاییدشده و قابل نمایش شیخان.')

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <x-layout.section-heading
                eyebrow="مدرس‌های شیخان"
                title="مدرس‌های تاییدشده"
                description="مدرس‌های قابل نمایش را ببین و مسیرهای آموزشی و تخصص آن‌ها را بررسی کن."
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
                    <div class="sm:col-span-2 xl:col-span-4">
                        <x-ui.empty-state
                            title="مدرس قابل نمایشی پیدا نشد"
                            description="به‌محض ثبت و تایید مدرس، اطلاعات او اینجا نمایش داده می‌شود."
                        />
                    </div>
                @endforelse
            </div>

            @if($teachers->hasPages())
                <div class="mt-10 flex justify-center">
                    <x-navigation.pagination :paginator="$teachers" />
                </div>
            @endif
        </x-layout.container>
    </x-layout.section>
@endsection
