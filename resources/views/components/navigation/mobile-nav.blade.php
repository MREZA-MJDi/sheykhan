@props([
'title' => 'فرزین',
'open' => false,
])

<div
    x-data="{ open: @js($open) }"
    x-cloak
    class="lg:hidden"
>
    {{-- Trigger --}}
    <button
        type="button"
        @click="open = !open"
        :aria-expanded="open"
        aria-controls="farzin-mobile-navigation"
        aria-label="باز کردن منوی سایت"
        class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[var(--color-border)] bg-white text-[var(--color-text)] shadow-sm transition-all duration-300 hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-50)]"
    >
        {{-- Menu icon --}}
        <svg
            x-show="!open"
            x-cloak
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M4 7h16M4 12h16M4 17h16"
            />
        </svg>

        {{-- Close icon --}}
        <svg
            x-show="open"
            x-cloak
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M6 6l12 12M18 6L6 18"
            />
        </svg>
    </button>


    {{-- Overlay --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition-opacity duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 z-[var(--z-overlay)] bg-slate-950/30 backdrop-blur-sm"
        aria-hidden="true"
    ></div>


    {{-- Drawer --}}
    <aside
        id="farzin-mobile-navigation"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-[var(--ease-emphasized)] duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-[var(--ease-standard)] duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-[var(--z-modal)] flex w-[min(88vw,380px)] flex-col border-l border-[var(--color-border)] bg-white shadow-2xl"
        role="dialog"
        aria-modal="true"
        :aria-hidden="!open"
    >

        {{-- Drawer Header --}}
        <div class="flex items-center justify-between border-b border-[var(--color-border)] px-5 py-5">

            <a
                href="{{ route('home') }}"
                @click="open = false"
                class="group inline-flex items-center gap-3"
            >
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-primary-600)] text-base font-black text-white shadow-sm transition-transform duration-300 group-hover:-translate-y-0.5"
                >
                    ف
                </span>

                <div>
                    <div class="text-base font-black text-[var(--color-text)]">
                        {{ $title }}
                    </div>

                    <div class="text-xs text-[var(--color-text-muted)]">
                        یادگیری، رشد، آینده
                    </div>
                </div>
            </a>

            <button
                type="button"
                @click="open = false"
                class="flex h-10 w-10 items-center justify-center rounded-xl text-[var(--color-text-muted)] transition-all duration-300 hover:bg-[var(--color-slate-100)] hover:text-[var(--color-text)]"
                aria-label="بستن منو"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 6l12 12M18 6L6 18"
                    />
                </svg>
            </button>

        </div>


        {{-- Search --}}
        <div class="border-b border-[var(--color-border)] p-5">

            <div class="relative">
                <svg
                    class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[var(--color-text-subtle)]"
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

                <input
                    type="search"
                    placeholder="جستجوی دوره یا مدرس..."
                    class="w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-slate-50)] py-3 pl-4 pr-10 text-sm text-[var(--color-text)] outline-none transition-all duration-300 placeholder:text-[var(--color-text-subtle)] focus:border-[var(--color-primary-300)] focus:bg-white focus:ring-4 focus:ring-[var(--color-primary-100)]"
                >
            </div>

        </div>


        {{-- Navigation --}}
        <div class="flex-1 overflow-y-auto p-5">

            <div class="mb-3 px-2 text-xs font-bold tracking-wide text-[var(--color-text-subtle)]">
                دسترسی سریع
            </div>

            <nav class="space-y-1">

                {{-- Home --}}
                <a
                    href="{{ route('home') }}"
                    @click="open = false"
                    class="group flex items-center gap-3 rounded-xl px-4 py-3.5 text-sm font-semibold transition-all duration-300
                    {{ request()->routeIs('home')
                        ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]'
                        : 'text-[var(--color-text-muted)] hover:bg-[var(--color-slate-100)] hover:text-[var(--color-text)]' }}"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg
                        {{ request()->routeIs('home')
                            ? 'bg-white text-[var(--color-primary-600)] shadow-sm'
                            : 'bg-[var(--color-slate-100)] text-[var(--color-text-muted)] group-hover:bg-white' }}"
                    >
                        <svg
                            class="h-4.5 w-4.5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 10.5L12 3l9 7.5M5.5 9.5V21h13V9.5M9.5 21v-6h5v6"
                            />
                        </svg>
                    </span>

                    <span class="flex-1">
                        صفحه اصلی
                    </span>

                    @if(request()->routeIs('home'))
                        <span class="h-2 w-2 rounded-full bg-[var(--color-primary-600)]"></span>
                    @endif
                </a>


                {{-- Courses --}}
                <a
                    href="{{ route('courses.index') }}"
                    @click="open = false"
                    class="group flex items-center gap-3 rounded-xl px-4 py-3.5 text-sm font-semibold transition-all duration-300
                    {{ request()->routeIs('courses.*')
                        ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]'
                        : 'text-[var(--color-text-muted)] hover:bg-[var(--color-slate-100)] hover:text-[var(--color-text)]' }}"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg
                        {{ request()->routeIs('courses.*')
                            ? 'bg-white text-[var(--color-primary-600)] shadow-sm'
                            : 'bg-[var(--color-slate-100)] text-[var(--color-text-muted)] group-hover:bg-white' }}"
                    >
                        <svg
                            class="h-4.5 w-4.5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M4 18.5A2.5 2.5 0 0 1 6.5 16H20"
                            />
                        </svg>
                    </span>

                    <span class="flex-1">
                        دوره‌ها
                    </span>

                    @if(request()->routeIs('courses.*'))
                        <span class="h-2 w-2 rounded-full bg-[var(--color-primary-600)]"></span>
                    @endif
                </a>


                {{-- Teachers --}}
                <a
                    href="{{ route('teachers.index') }}"
                    @click="open = false"
                    class="group flex items-center gap-3 rounded-xl px-4 py-3.5 text-sm font-semibold transition-all duration-300
                    {{ request()->routeIs('teachers.*')
                        ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]'
                        : 'text-[var(--color-text-muted)] hover:bg-[var(--color-slate-100)] hover:text-[var(--color-text)]' }}"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-lg
                        {{ request()->routeIs('teachers.*')
                            ? 'bg-white text-[var(--color-primary-600)] shadow-sm'
                            : 'bg-[var(--color-slate-100)] text-[var(--color-text-muted)] group-hover:bg-white' }}"
                    >
                        <svg
                            class="h-4.5 w-4.5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                            />
                            <circle
                                cx="9"
                                cy="7"
                                r="4"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"
                            />
                        </svg>
                    </span>

                    <span class="flex-1">
                        مدرس‌ها
                    </span>

                    @if(request()->routeIs('teachers.*'))
                        <span class="h-2 w-2 rounded-full bg-[var(--color-primary-600)]"></span>
                    @endif
                </a>

            </nav>


            {{-- Auth --}}
            <div class="mt-8 border-t border-[var(--color-border)] pt-6">

                <div class="mb-3 px-2 text-xs font-bold tracking-wide text-[var(--color-text-subtle)]">
                    حساب کاربری
                </div>

                @auth

                    <a
                        href="/dashboard"
                        @click="open = false"
                        class="flex items-center gap-3 rounded-xl bg-[var(--color-primary-600)] px-4 py-3.5 text-sm font-bold text-white shadow-sm transition-all duration-300 hover:bg-[var(--color-primary-700)]"
                    >
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15">
                            <svg
                                class="h-4.5 w-4.5"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <rect
                                    x="3"
                                    y="3"
                                    width="7"
                                    height="7"
                                    rx="1"
                                />
                                <rect
                                    x="14"
                                    y="3"
                                    width="7"
                                    height="7"
                                    rx="1"
                                />
                                <rect
                                    x="3"
                                    y="14"
                                    width="7"
                                    height="7"
                                    rx="1"
                                />
                                <rect
                                    x="14"
                                    y="14"
                                    width="7"
                                    height="7"
                                    rx="1"
                                />
                            </svg>
                        </span>

                        ورود به داشبورد
                    </a>

                @else

                    <div class="grid grid-cols-2 gap-3">

                        <a
                            href="#"
                            @click="open = false"
                            class="inline-flex items-center justify-center rounded-xl border border-[var(--color-border)] px-4 py-3 text-sm font-semibold text-[var(--color-text)] transition-all duration-300 hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-50)]"
                        >
                            ورود
                        </a>

                        <a
                            href="#"
                            @click="open = false"
                            class="inline-flex items-center justify-center rounded-xl bg-[var(--color-primary-600)] px-4 py-3 text-sm font-bold text-white shadow-sm transition-all duration-300 hover:bg-[var(--color-primary-700)]"
                        >
                            ثبت‌نام
                        </a>

                    </div>

                @endauth

            </div>

        </div>


        {{-- Drawer Footer --}}
        <div class="border-t border-[var(--color-border)] bg-[var(--color-slate-50)] px-5 py-4">

            <div class="flex items-center justify-between gap-4">

                <div>
                    <p class="text-xs font-semibold text-[var(--color-text)]">
                        {{ $title }}
                    </p>

                    <p class="mt-1 text-[11px] text-[var(--color-text-muted)]">
                        پلتفرم آموزشی مدرن
                    </p>
                </div>

                <span class="rounded-full bg-[var(--color-success-500)]/10 px-2.5 py-1 text-[10px] font-bold text-[var(--color-success-600)]">
                    آنلاین
                </span>

            </div>

        </div>

    </aside>
</div>
