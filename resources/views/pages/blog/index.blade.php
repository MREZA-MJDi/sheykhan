@extends('layouts.app')

@section('title', 'مقالات | شیخان')
@section('description', 'مقالات آموزشی منتشرشده در شیخان.')

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <x-layout.section-heading
                eyebrow="مرکز محتوا"
                title="مقالات شیخان"
                description="مطالب آموزشی و کاربردی برای ساختن عادت‌ها و مسیر یادگیری بهتر."
            />

            <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($posts as $post)
                    <x-education.post-card
                        :title="$post->title"
                        :excerpt="$post->excerpt"
                        :category="$post->category?->name"
                        :date="$post->published_at?->format('Y/m/d')"
                        :image="$post->media->first()?->url()"
                        :href="route('blog.show', $post->slug)"
                    />
                @empty
                    <div class="md:col-span-2 xl:col-span-3">
                        <x-ui.empty-state
                            title="هنوز مقاله‌ای منتشر نشده"
                            description="مقالات جدید شیخان بعد از انتشار در این بخش قرار می‌گیرند."
                        />
                    </div>
                @endforelse
            </div>

            @if($posts->hasPages())
                <div class="mt-10 flex justify-center">
                    <x-navigation.pagination :paginator="$posts" />
                </div>
            @endif
        </x-layout.container>
    </x-layout.section>
@endsection
