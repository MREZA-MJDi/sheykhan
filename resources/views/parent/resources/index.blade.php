@extends('layouts.parent')

@section('title', 'منابع آموزشی فرزندان | شیخان')
@section('header-title', 'منابع آموزشی فرزندان')

@section('content')
<div class="parent-dashboard space-y-6">
    <header class="parent-panel dashboard-panel p-5 sm:p-7">
        <p class="parent-kicker" style="color:var(--panel-primary)">کتابخانهٔ آموزشی خانواده</p>
        <h1 class="mt-1 text-2xl font-black">جزوه‌ها و منابع آموزشی فرزند</h1>
        <p class="mt-2 text-sm leading-7 text-slate-500">
            منابع فقط از دوره‌ها و کلاس‌هایی نمایش داده می‌شوند که فرزند انتخاب‌شده دسترسی فعال به آن‌ها دارد.
            خودِ فایل نیز هنگام مشاهده دوباره از سمت سرور بررسی می‌شود.
        </p>
    </header>

    @if($children->isEmpty())
        <section class="parent-panel dashboard-panel p-8 text-center">
            <h2 class="font-black">فرزند فعال و متصل‌شده‌ای برای نمایش منابع وجود ندارد.</h2>
            <p class="mt-2 text-sm leading-7 text-slate-500">از آموزشگاه بخواهید اتصال حساب والد به حساب دانش‌آموز و عضویت فعال او را بررسی کند.</p>
        </section>
    @else
        <section class="parent-panel dashboard-panel p-5 sm:p-6">
            <form method="GET" action="{{ route('parent.resources.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <label class="grid flex-1 gap-2 text-sm font-bold text-slate-700">
                    منابع کدام فرزند را ببینم؟
                    <select name="child" required class="min-h-12 rounded-xl border border-slate-300 bg-white px-3 py-2">
                        @foreach($children as $child)
                            <option value="{{ $child->id }}" @selected($selectedChild && (int) $selectedChild->id === (int) $child->id)>{{ $child->name }}</option>
                        @endforeach
                    </select>
                </label>
                <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[var(--panel-primary)] px-5 font-black text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2">نمایش منابع</button>
            </form>
        </section>

        @if($selectedChild && $resources)
            <section class="parent-panel dashboard-panel p-5 sm:p-6" aria-labelledby="parent-resources-title">
                <div class="mb-4">
                    <h2 id="parent-resources-title" class="text-lg font-black">منابع قابل دسترس برای {{ $selectedChild->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">مشاهدهٔ فایل امن است؛ لینک عمومی فایل یا دسترسی به دورهٔ خارج از ثبت‌نام ساخته نمی‌شود.</p>
                </div>

                <div class="space-y-3">
                    @forelse($resources as $resource)
                        <article class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <h3 class="break-words font-black text-slate-900">{{ $resource->title }}</h3>
                                <p class="mt-1 text-xs leading-6 text-slate-500">
                                    {{ $resource->course?->title ?? $resource->classroom?->title ?? $resource->lesson?->title ?? 'منبع آموزشگاه' }}
                                    @if($resource->description)
                                        · {{ \Illuminate\Support\Str::limit($resource->description, 120) }}
                                    @endif
                                </p>
                                @if($resource->release_at)
                                    <p class="mt-1 text-xs text-slate-500">انتشار: {{ \App\Support\PersianUi::date($resource->release_at) }}</p>
                                @endif
                            </div>
                            <a
                                class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-xl border border-[var(--panel-primary)] px-4 text-sm font-black text-[var(--panel-primary)] transition hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2"
                                href="{{ route('parent.resources.view', ['resource' => $resource, 'child' => $selectedChild->id]) }}"
                                target="_blank"
                                rel="noopener"
                            >مشاهدهٔ امن فایل</a>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center">
                            <h3 class="font-black">منبع منتشرشده‌ای برای این فرزند پیدا نشد.</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-500">منابع زمان‌بندی‌شده یا منابع دوره‌هایی که دسترسی فعال ندارند نمایش داده نمی‌شوند.</p>
                        </div>
                    @endforelse
                </div>

                @if($resources->hasPages())
                    <nav class="mt-5" aria-label="صفحه‌بندی منابع آموزشی">{{ $resources->links() }}</nav>
                @endif
            </section>
        @endif
    @endif
</div>
@endsection
