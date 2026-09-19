@props([
    'title' => 'شیخان',
])

@php
    $isOwner = str_contains($title, 'آموزشگاه');
    $isTeacher = str_contains($title, 'مدرس');
    $isStudent = str_contains($title, 'دانش‌آموز');
    $isParent = str_contains($title, 'والد');

    $homeRoute = $isOwner
        ? 'owner.dashboard'
        : ($isTeacher
            ? 'teacher.dashboard'
            : ($isStudent
                ? 'student.dashboard'
                : 'parent.dashboard'));

    $links = [];

    if (Route::has($homeRoute)) {
        $links[] = [
            'route' => $homeRoute,
            'label' => 'داشبورد',
            'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>',
        ];
    }

    if ($isOwner || $isTeacher) {
        $links = array_merge($links, [
            [
                'route' => $isOwner ? 'owner.courses.*' : 'teacher.courses.*',
                'label' => 'دوره‌ها',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z"/><path d="M4 18.5A2.5 2.5 0 0 1 6.5 16H20"/></svg>'],
            [
                'label' => 'کلاس‌ها',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="14" rx="3"/><path d="M8 21h8M12 18v3"/></svg>'],
            [
                'label' => 'تکلیف و آزمون',
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3h10a2 2 0 0 1 2 2v16H5V5a2 2 0 0 1 2-2Z"/><path d="m8.5 12 2 2 5-5"/></svg>'],
        ]);
    } else {
        $links = array_merge($links, [
            ['label' => $isParent ? 'فرزندان من' : 'مسیر یادگیری', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="8" cy="8" r="3"/><circle cx="16" cy="8" r="3"/><path d="M3 20c.6-3 2.2-5 5-5s4.4 2 5 5M11 20c.6-3 2.2-5 5-5s4.4 2 5 5"/></svg>'],
            ['label' => 'کلاس‌ها و جلسات', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>'],
            ['label' => 'تمرین و آزمون', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12v18H6z"/><path d="m8.5 12 2 2 5-5"/></svg>'],
        ]);
    }
@endphp

<div
    x-data="{ open: false }"
    class="w-full shrink-0 lg:w-72"
>
    <aside class="panel-sidebar h-auto border-b lg:sticky lg:top-0 lg:h-screen lg:border-b-0 lg:border-l">
        <div class="flex h-full flex-col">
            <div class="panel-sidebar-brand flex min-h-16 items-center justify-between border-b border-[var(--color-border)] px-4 sm:px-5">
                <a
                    href="{{ Route::has($homeRoute) ? route($homeRoute) : route('home') }}"
                    class="relative z-[1] flex min-w-0 items-center gap-3"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--color-slate-900)] text-sm font-black text-white shadow-sm">
                        ش
                    </span>

                    <span class="min-w-0">
                        <span class="block truncate text-sm font-black text-[var(--color-text)]">شیخان</span>
                        <span class="mt-0.5 block truncate text-[11px] font-medium text-[var(--color-text-muted)]">{{ $title }}</span>
                    </span>
                </a>

                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--color-border)] bg-white text-[var(--color-text)] lg:hidden"
                    @click="open = !open"
                    :aria-expanded="open.toString()"
                    aria-controls="role-sidebar-navigation"
                    aria-label="باز کردن منوی پنل"
                >
                    <svg x-show="!open" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                    </svg>
                    <svg x-show="open" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/>
                    </svg>
                </button>
            </div>

            <div
                id="role-sidebar-navigation"
                x-cloak
                x-show="open"
                x-transition
                class="hidden flex-1 overflow-y-auto px-3 py-4 sm:px-4 lg:!block"
            >
                <div class="mb-3 px-3 text-[10px] font-black tracking-wider text-[var(--color-text-muted)]">
                    منوی اصلی
                </div>

                <nav class="grid gap-1">
                    @foreach($links as $link)
                        @if(isset($link['route']) && Route::has($link['route']))
                            <a
                                href="{{ route($link['route']) }}"
                                class="panel-nav-link {{ request()->routeIs($link['route']) ? 'is-active' : '' }} flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-semibold {{ request()->routeIs($link['route']) ? '' : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-background-soft)] hover:text-[var(--color-text)]' }}"
                                @if(request()->routeIs($link['route'])) aria-current="page" @endif
                                @click="open = false"
                            >
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[var(--color-slate-100)] text-[var(--color-text-secondary)]">
                                    {!! $link['icon'] !!}
                                </span>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @else
                            <div class="flex min-h-11 items-center gap-3 rounded-xl px-3 text-sm font-semibold text-[var(--color-text-muted)]">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[var(--color-slate-50)] text-[var(--color-text-muted)]">
                                    {!! $link['icon'] !!}
                                </span>
                                <span class="flex-1">{{ $link['label'] }}</span>
                                <span class="rounded-full bg-[var(--color-slate-100)] px-2 py-0.5 text-[9px] font-bold text-[var(--color-text-muted)]">
                                    به‌زودی
                                </span>
                            </div>
                        @endif
                    @endforeach
                </nav>

                @if(trim($slot) !== '')
                    <div class="mt-5 border-t border-[var(--color-border)] pt-5">
                        {{ $slot }}
                    </div>
                @endif

                <div class="mt-6 border-t border-[var(--color-border)] pt-5">
                    <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-background-soft)] p-4">
                        <div class="text-xs font-bold text-[var(--color-text)]">فضای شما</div>
                        <p class="mt-1 text-xs leading-6 text-[var(--color-text-muted)]">
                            هر نقش محیط و دسترسی‌های مستقل خودش را دارد.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</div>
