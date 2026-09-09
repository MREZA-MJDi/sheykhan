@extends('layouts.app')

@section('title', 'دوره | فرزین')

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">
            <x-layout.page-header
                title="جزئیات دوره"
                description="ساختار کامل دوره در مرحله Backend و Course Module پیاده‌سازی می‌شود."
            />

            <div class="mt-10 fz-surface p-8">
                <div class="text-sm text-[var(--color-text-muted)]">
                    Course Slug
                </div>

                <div class="mt-2 text-xl font-bold">
                    {{ $course }}
                </div>
            </div>
        </x-layout.container>
    </x-layout.section>
@endsection