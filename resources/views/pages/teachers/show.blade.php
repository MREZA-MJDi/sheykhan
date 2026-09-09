@extends('layouts.app')

@section('title', 'مدرس | فرزین')

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">
            <x-layout.page-header
                title="پروفایل مدرس"
                description="پروفایل کامل مدرس در مرحله بعدی ساخته می‌شود."
            />

            <div class="mt-10 fz-surface p-8">
                <div class="text-sm text-[var(--color-text-muted)]">
                    Teacher
                </div>

                <div class="mt-2 text-xl font-bold">
                    {{ $teacher }}
                </div>
            </div>
        </x-layout.container>
    </x-layout.section>
@endsection