@extends('layouts.app')

@section('title', 'سیستم طراحی شیخان')

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <div class="mb-8">
                <h1 class="text-2xl font-black text-[var(--color-text)] sm:text-3xl">
                    سیستم طراحی شیخان
                </h1>

                <p class="mt-2 text-sm text-[var(--color-text-muted)]">
                    مرجع کامپوننت‌ها و الگوهای رابط کاربری شیخان
                </p>
            </div>

            <div class="grid gap-6">
                <x-ui.card>
                    <h2 class="text-lg font-bold">دکمه‌ها</h2>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <x-ui.button>اصلی</x-ui.button>
                        <x-ui.button variant="secondary">ثانویه</x-ui.button>
                        <x-ui.button variant="outline">خطی</x-ui.button>
                        <x-ui.button variant="ghost">شبح</x-ui.button>
                        <x-ui.button variant="danger">حذف</x-ui.button>
                        <x-ui.button variant="accent">تأکیدی</x-ui.button>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="text-lg font-bold">برچسب‌ها</h2>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <x-ui.badge>خنثی</x-ui.badge>
                        <x-ui.badge variant="primary">اصلی</x-ui.badge>
                        <x-ui.badge variant="success">موفق</x-ui.badge>
                        <x-ui.badge variant="warning">هشدار</x-ui.badge>
                        <x-ui.badge variant="danger">خطر</x-ui.badge>
                        <x-ui.badge variant="info">اطلاعات</x-ui.badge>
                    </div>
                </x-ui.card>

                <x-ui.card>
                    <h2 class="text-lg font-bold">فرم</h2>

                    <div class="mt-5 max-w-xl">
                        <x-ui.input
                            id="email"
                            label="ایمیل"
                            type="email"
                            placeholder="example@email.com"
                        />
                    </div>
                </x-ui.card>
            </div>
        </x-layout.container>
    </x-layout.section>
@endsection
