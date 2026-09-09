@props([
'title' => 'فرزین',
'subtitle' => 'مرکز آموزش تخصصی',
'showSearch' => true,
])

@php
    $navigationItems = [
        [
            'label' => 'خانه',
            'href' => route('home'),
            'active' => request()->routeIs('home'),
        ],
        [
            'label' => 'دوره‌ها',
            'href' => route('courses.index'),
            'active' => request()->routeIs('courses.*'),
        ],
        [
            'label' => 'اساتید',
            'href' => route('teachers.index'),
            'active' => request()->routeIs('teachers.*'),
        ],
        [
            'label' => 'مجله',
            'href' => route('blog.index'),
            'active' => request()->routeIs('blog.*'),
        ],
        [
            'label' => 'درباره فرزین',
            'href' => route('about'),
            'active' => request()->routeIs('about'),
        ],
    ];
@endphp

<nav
    x-data="{
        mobileOpen: false,
        searchOpen: false,
    }"
    x-on:keydown.escape.window="
        mobileOpen = false;
        searchOpen = false;
    "
    class="sticky top-0 z-[var(--z-sticky)] border-b border-[var(--color-border)] bg-white/95 backdrop-blur"
    aria-label="ناوبری اصلی"
>
    <div class="container-farzin">
        <div class="flex min-h-[var(--header-height)] items-center justify-between gap-4">

            {{-- =====================================================
                BRAND
            ====================================================== --}}
            <a
                href="{{ route('home') }}"
                class="group inline-flex shrink-0 items-center gap-3"
                aria-label="{{ $title }}"
            >
                <span
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--color-brand-600)] text-lg font-black text-white shadow-[var(--shadow-sm)] transition-transform duration-200 group-hover:-translate-y-0.5"
                >
                    ف
                </span>

                <span class="hidden leading-tight sm:block">
                    <span class="block text-base font-extrabold text-[var(--color-text-primary)]">
                        {{ $title }}
                    </span>

                    <span class="block text-xs text-[var(--color-text-muted)]">
                        {{ $subtitle }}
                    </span>
                </span>
            </a>


            {{-- =====================================================
                DESKTOP NAVIGATION
            ====================================================== --}}
            <div class="hidden items-center gap-1 lg:flex">
                @foreach($navigationItems as $item)
                    <a
                        href="{{ $item['href'] }}"
                        @if($item['active'])
                        aria-current="page"
                        @endif
                        class="
                            rounded-xl
                            px-3.5
                            py-2.5
                            text-sm
                            font-semibold
                            transition-colors
                            duration-200
                            focus-visible:outline-none
                            focus-visible:ring-4
                            focus-visible:ring-[color-mix(in_srgb,var(--color-brand-200)_70%,transparent)]
                            {{ $item['active']
                                ? 'bg-[var(--color-brand-50)] text-[var(--color-brand-700)]'
                                : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-neutral-50)] hover:text-[var(--color-text-primary)]'
                            }}
                            "
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>


            {{-- =====================================================
                DESKTOP ACTIONS
            ====================================================== --}}
            <div class="flex items-center gap-2">

                {{-- Search --}}
                @if($showSearch)
                    <button
                        type="button"
                        @click="searchOpen = !searchOpen"
                        :aria-expanded="searchOpen.toString()"
                        aria-controls="farzin-navbar-search"
                        aria-label="جستجو"
                        class="
                            hidden
                            h-10
                            w-10
                            items-center
                            justify-center
                            rounded-xl
                            border
                            border-[var(--color-border)]
                            bg-[var(--color-surface)]
                            text-[var(--color-text-muted)]
                            transition-colors
                            duration-200
                            hover:border-[var(--color-border-strong)]
                            hover:bg-[var(--color-neutral-50)]
                            hover:text-[var(--color-text-primary)]
                            focus-visible:outline-none
                            focus-visible:ring-4
                            focus-visible:ring-[color-mix(in_srgb,var(--color-brand-200)_70%,transparent)]
                            sm:inline-flex
                        "
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <circle
                                cx="11"
                                cy="11"
                                r="7"
                            />
                            <path d="m20 20-3.5-3.5" />
                        </svg>
                    </button>
                @endif


                {{-- Login / Register --}}
                <a
                    href="#"
                    class="
                        hidden
                        min-h-10
                        items-center
                        justify-center
                        gap-2
                        rounded-xl
                        bg-[var(--color-brand-600)]
                        px-4
                        py-2
                        text-sm
                        font-bold
                        text-white
                        shadow-[var(--shadow-xs)]
                        transition-all
                        duration-200
                        hover:bg-[var(--color-brand-700)]
                        hover:shadow-[var(--shadow-sm)]
                        focus-visible:outline-none
                        focus-visible:ring-4
                        focus-visible:ring-[color-mix(in_srgb,var(--color-brand-200)_70%,transparent)]
                        sm:inline-flex
                    "
                >
                    <svg
                        class="h-4.5 w-4.5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M15.75 6a3.75 3.75 0 1 1-7.5 0" />
                        <path d="M4.5 20.25a8.25 8.25 0 0 1 15 0" />
                    </svg>

                    ورود / ثبت‌نام
                </a>


                {{-- Mobile Trigger --}}
                <button
                    type="button"
                    @click="mobileOpen = !mobileOpen"
                    :aria-expanded="mobileOpen.toString()"
                    aria-controls="farzin-navbar-mobile"
                    aria-label="منوی اصلی"
                    class="
                        inline-flex
                        h-10
                        w-10
                        items-center
                        justify-center
                        rounded-xl
                        border
                        border-[var(--color-border)]
                        bg-[var(--color-surface)]
                        text-[var(--color-text-secondary)]
                        transition-colors
                        duration-200
                        hover:bg-[var(--color-neutral-50)]
                        hover:text-[var(--color-text-primary)]
                        focus-visible:outline-none
                        focus-visible:ring-4
                        focus-visible:ring-[color-mix(in_srgb,var(--color-brand-200)_70%,transparent)]
                        lg:hidden
                    "
                >
                    <svg
                        x-show="!mobileOpen"
                        x-cloak
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M4 7h16M4 12h16M4 17h16" />
                    </svg>

                    <svg
                        x-show="mobileOpen"
                        x-cloak
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>

            </div>
        </div>
    </div>


    {{-- =========================================================
        SEARCH PANEL
    ========================================================== --}}
    @if($showSearch)
        <div
            id="farzin-navbar-search"
            x-show="searchOpen"
            x-cloak
            x-transition:enter="transition duration-150 ease-out"
            x-transition:enter-start="-translate-y-2 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition duration-100 ease-in"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="-translate-y-2 opacity-0"
            class="border-t border-[var(--color-border)] bg-[var(--color-surface)]"
        >
            <x-layout.container>
                <div class="py-4">

                    <form
                        action="#"
                        method="GET"
                        class="mx-auto max-w-2xl"
                    >
                        <label
                            for="navbar-search-input"
                            class="sr-only"
                        >
                            جستجوی سایت
                        </label>

                        <div class="relative">
                            <svg
                                class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[var(--color-text-muted)]"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <circle
                                    cx="11"
                                    cy="11"
                                    r="7"
                                />
                                <path d="m20 20-3.5-3.5" />
                            </svg>

                            <input
                                id="navbar-search-input"
                                type="search"
                                name="q"
                                placeholder="جستجوی دوره، مدرس یا مقاله..."
                                autocomplete="off"
                                class="ui-input pr-10"
                            />
                        </div>
                    </form>

                </div>
            </x-layout.container>
        </div>
    @endif


    {{-- =========================================================
        MOBILE MENU
    ========================================================== --}}
    <div
        id="farzin-navbar-mobile"
        x-show="mobileOpen"
        x-cloak
        class="lg:hidden"
    >
        {{-- Overlay --}}
        <div
            class="fixed inset-0 z-[var(--z-modal-backdrop)] bg-slate-950/40 backdrop-blur-sm"
            @click="mobileOpen = false"
            aria-hidden="true"
        ></div>


        {{-- Drawer --}}
        <aside
            x-transition:enter="transform transition duration-300 ease-out"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition duration-200 ease-in"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="
                fixed
                inset-y-0
                right-0
                z-[var(--z-modal)]
                flex
                w-[min(88vw,380px)]
                flex-col
                bg-[var(--color-surface)]
                shadow-[var(--shadow-xl)]
            "
        >

            {{-- Mobile Header --}}
            <div class="flex items-center justify-between border-b border-[var(--color-border)] px-5 py-5">

                <a
                    href="{{ route('home') }}"
                    @click="mobileOpen = false"
                    class="flex items-center gap-3"
                >
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-brand-600)] font-black text-white">
                        ف
                    </span>

                    <div>
                        <div class="text-sm font-extrabold text-[var(--color-text-primary)]">
                            {{ $title }}
                        </div>

                        <div class="text-xs text-[var(--color-text-muted)]">
                            {{ $subtitle }}
                        </div>
                    </div>
                </a>

                <button
                    type="button"
                    @click="mobileOpen = false"
                    class="
                        flex
                        h-10
                        w-10
                        items-center
                        justify-center
                        rounded-xl
                        text-[var(--color-text-muted)]
                        transition-colors
                        hover:bg-[var(--color-neutral-100)]
                        hover:text-[var(--color-text-primary)]
                        focus-visible:outline-none
                        focus-visible:ring-4
                        focus-visible:ring-[color-mix(in_srgb,var(--color-brand-200)_70%,transparent)]
                    "
                    aria-label="بستن منو"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>

            </div>


            {{-- Links --}}
            <div class="flex-1 overflow-y-auto px-5 py-5">

                <nav
                    class="space-y-1"
                    aria-label="منوی موبایل"
                >

                    @foreach($navigationItems as $item)
                        <a
                            href="{{ $item['href'] }}"
                            @click="mobileOpen = false"
                            @if($item['active'])
                            aria-current="page"
                            @endif
                            class="
                                flex
                                items-center
                                justify-between
                                rounded-xl
                                px-4
                                py-3.5
                                text-sm
                                font-semibold
                                transition-colors
                                duration-200
                                {{ $item['active']
                                    ? 'bg-[var(--color-brand-50)] text-[var(--color-brand-700)]'
                                    : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-neutral-50)] hover:text-[var(--color-text-primary)]'
                                }}
                                "
                        >
                            <span>
                                {{ $item['label'] }}
                            </span>

                            <svg
                                class="h-4 w-4 {{ $item['active'] ? 'text-[var(--color-brand-600)]' : 'text-[var(--color-text-muted)]' }}"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path d="m9 18 6-6-6-6" />
                            </svg>
                        </a>
                    @endforeach

                </nav>


                {{-- Mobile CTA --}}
                <div class="mt-6 border-t border-[var(--color-border)] pt-6">

                    <a
                        href="#"
                        @click="mobileOpen = false"
                        class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-[var(--color-brand-600)] px-4 py-3 text-sm font-bold text-white transition-colors duration-200 hover:bg-[var(--color-brand-700)]"
                    >
                        ورود / ثبت‌نام
                    </a>

                </div>

            </div>

        </aside>
    </div>

</nav>
