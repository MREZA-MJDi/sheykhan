@extends('layouts.app')

@section('title', 'دوره‌ها | فرزین')

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">
            <x-layout.page-header
                title="دوره‌های آموزشی"
                description="مسیر آموزشی مناسب خودت را پیدا کن."
            />

            <div class="mt-10 rounded-2xl border border-[var(--color-border)] bg-white p-8 text-center shadow-sm">
                <p class="text-[var(--color-text-muted)]">
                    صفحه دوره‌ها در مرحله بعدی به صورت کامل ساخته می‌شود.
                </p>
            </div>
        </x-layout.container>
    </x-layout.section>
@endsection