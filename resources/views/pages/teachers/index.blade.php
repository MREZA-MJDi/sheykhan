@extends('layouts.app')

@section('title', 'مدرس‌ها | فرزین')

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">
            <x-layout.page-header
                title="مدرس‌های فرزین"
                description="مدرس‌های متخصص و باتجربه فرزین."
            />

            <div class="mt-10 rounded-2xl border border-[var(--color-border)] bg-white p-8 text-center shadow-sm">
                <p class="text-[var(--color-text-muted)]">
                    صفحه مدرس‌ها در مرحله بعدی تکمیل می‌شود.
                </p>
            </div>
        </x-layout.container>
    </x-layout.section>
@endsection