<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @hasSection('title')
            @yield('title') | پنل والدین فرزین
        @else
            پنل والدین | فرزین
        @endif
    </title>

    <meta
        name="description"
        content="@yield('description', 'پنل والدین فرزین')"
    >

    <link
        rel="icon"
        href="{{ asset('favicon.ico') }}"
    >

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    ])

    @stack('head')
</head>

<body class="min-h-screen bg-[var(--color-background)] text-[var(--color-text-primary)]">
<div
    x-data="{ sidebarOpen: false }"
    class="min-h-screen"
>

    {{-- Mobile overlay --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition:enter="transition-opacity duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-[var(--z-modal-backdrop)] bg-slate-950/40 backdrop-blur-sm lg:hidden"
        aria-hidden="true"
    ></div>

    {{-- Desktop Sidebar --}}
    <aside
        class="fixed inset-y-0 right-0 z-[var(--z-fixed)] hidden w-[var(--sidebar-width)] border-l border-[var(--color-border)] bg-[var(--color-surface)] lg:flex"
    >
        <x-navigation.sidebar
            title="پنل والدین"
            :items="[
                        [
                            'label' => 'داشبورد',
                            'url' => '#',
                            'route' => 'parent.dashboard',
                        ],
                        [
                            'label' => 'فرزند من',
                            'url' => '#',
                            'route' => 'parent.student',
                        ],
                        [
                            'label' => 'دوره‌های فرزند',
                            'url' => '#',
                            'route' => 'parent.courses.*',
                        ],
                        [
                            'label' => 'کلاس‌ها',
                            'url' => '#',
                            'route' => 'parent.classes.*',
                        ],
                        [
                            'label' => 'آزمون‌ها',
                            'url' => '#',
                            'route' => 'parent.exams.*',
                        ],
                        [
                            'label' => 'تکالیف',
                            'url' => '#',
                            'route' => 'parent.assignments.*',
                        ],
                        [
                            'label' => 'برنامه آموزشی',
                            'url' => '#',
                            'route' => 'parent.schedule',
                        ],
                        [
                            'label' => 'گزارش عملکرد',
                            'url' => '#',
                            'route' => 'parent.reports',
                        ],
                    ]"
        />
    </aside>

    {{-- Mobile Sidebar --}}
    <aside
        x-show="sidebarOpen"
        x-cloak
        x-transition:enter="transform transition duration-300 ease-[var(--ease-emphasized)]"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition duration-200 ease-[var(--ease-standard)]"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-[var(--z-modal)] flex w-[min(88vw,380px)] lg:hidden"
    >
        <x-navigation.sidebar
            title="پنل والدین"
            :items="[
                        [
                            'label' => 'داشبورد',
                            'url' => '#',
                            'route' => 'parent.dashboard',
                        ],
                        [
                            'label' => 'فرزند من',
                            'url' => '#',
                            'route' => 'parent.student',
                        ],
                        [
                            'label' => 'دوره‌های فرزند',
                            'url' => '#',
                            'route' => 'parent.courses.*',
                        ],
                        [
                            'label' => 'کلاس‌ها',
                            'url' => '#',
                            'route' => 'parent.classes.*',
                        ],
                        [
                            'label' => 'آزمون‌ها',
                            'url' => '#',
                            'route' => 'parent.exams.*',
                        ],
                        [
                            'label' => 'تکالیف',
                            'url' => '#',
                            'route' => 'parent.assignments.*',
                        ],
                        [
                            'label' => 'برنامه آموزشی',
                            'url' => '#',
                            'route' => 'parent.schedule',
                        ],
                        [
                            'label' => 'گزارش عملکرد',
                            'url' => '#',
                            'route' => 'parent.reports',
                        ],
                    ]"
        />
    </aside>

    {{-- Main area --}}
    <div class="min-h-screen lg:pr-[var(--sidebar-width)]">

        {{-- Topbar --}}
        <header
            class="sticky top-0 z-[var(--z-sticky)] border-b border-[var(--color-border)] bg-white/95 backdrop-blur"
        >
            <div class="container-farzin">
                <div class="flex min-h-[var(--header-height)] items-center justify-between gap-4">

                    {{-- Mobile menu --}}
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] text-[var(--color-text-secondary)] lg:hidden"
                        aria-label="باز کردن منوی پنل"
                        :aria-expanded="sidebarOpen.toString()"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                    </button>

                    {{-- Heading --}}
                    <div class="min-w-0 flex-1">
                        @hasSection('page-heading')
                            <h1 class="truncate text-base font-extrabold text-[var(--color-text-primary)] sm:text-lg">
                                @yield('page-heading')
                            </h1>
                        @else
                            <h1 class="text-base font-extrabold text-[var(--color-text-primary)] sm:text-lg">
                                پنل والدین
                            </h1>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2">

                        {{-- Notifications --}}
                        <button
                            type="button"
                            class="relative flex h-10 w-10 items-center justify-center rounded-xl text-[var(--color-text-muted)] transition-colors duration-200 hover:bg-[var(--color-neutral-100)] hover:text-[var(--color-text-primary)]"
                            aria-label="اعلان‌ها"
                        >
                            <svg
                                class="h-5 w-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true"
                            >
                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                                <path d="M10 21h4" />
                            </svg>

                            <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-[var(--color-danger-500)]"></span>
                        </button>

                        {{-- User --}}
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-xl px-2 py-1.5 transition-colors duration-200 hover:bg-[var(--color-neutral-100)]"
                        >
                            <x-ui.avatar
                                name="والد"
                                size="sm"
                            />

                            <span class="hidden text-right sm:block">
                                        <span class="block text-xs text-[var(--color-text-muted)]">
                                            خوش آمدی
                                        </span>

                                        <span class="block max-w-32 truncate text-sm font-semibold text-[var(--color-text-primary)]">
                                            والد دانش‌آموز
                                        </span>
                                    </span>

                            <svg
                                class="hidden h-4 w-4 text-[var(--color-text-muted)] sm:block"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                    </div>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main
            id="main-content"
            class="min-h-[calc(100vh-var(--header-height))]"
        >
            <x-layout.section spacing="default">
                <x-layout.container>

                    @hasSection('page-header')
                        @yield('page-header')
                    @endif

                    @yield('content')

                </x-layout.container>
            </x-layout.section>
        </main>

    </div>

    {{-- Global Toast --}}
    <div
        id="toast-container"
        class="pointer-events-none fixed inset-x-4 top-4 z-[var(--z-toast)] flex flex-col items-center gap-3 sm:inset-x-auto sm:right-4 sm:items-end"
        aria-live="polite"
        aria-atomic="true"
    >
        @if(session('success'))
            <div class="pointer-events-auto">
                <x-feedback.toast
                    type="success"
                    :message="session('success')"
                />
            </div>
        @endif

        @if(session('error'))
            <div class="pointer-events-auto">
                <x-feedback.toast
                    type="danger"
                    :message="session('error')"
                />
            </div>
        @endif

        @if(session('warning'))
            <div class="pointer-events-auto">
                <x-feedback.toast
                    type="warning"
                    :message="session('warning')"
                />
            </div>
        @endif

        @if(session('info'))
            <div class="pointer-events-auto">
                <x-feedback.toast
                    type="info"
                    :message="session('info')"
                />
            </div>
        @endif
    </div>

</div>

@stack('scripts')
</body>
</html>
