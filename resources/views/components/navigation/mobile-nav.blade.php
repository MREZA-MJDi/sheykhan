@props([
'title' => 'فرزین',
'open' => false,
])

<div
    x-data="{ open: @js($open) }"
    x-cloak
    x-on:open-mobile-nav.window="open = true"
    x-on:close-mobile-nav.window="open = false"
    class="lg:hidden"
    dir="rtl"
>
    {{-- Overlay --}}
    <div
        x-show="open"
        x-transition:enter="transition-opacity duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 z-[var(--z-modal-backdrop)] bg-slate-950/40 backdrop-blur-sm"
        aria-hidden="true"
    ></div>

    {{-- Drawer --}}
    <aside
        x-show="open"
        x-transition:enter="transform transition duration-300 ease-[var(--ease-emphasized)]"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition duration-200 ease-[var(--ease-standard)]"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-[var(--z-modal)] flex w-[min(88vw,380px)] flex-col border-l border-[var(--color-border)] bg-white shadow-[var(--shadow-xl)]"
        role="dialog"
        aria-modal="true"
        :aria-hidden="!open"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-[var(--color-border)] px-5 py-5">

            <a
                href="{{ route('home') }}"
                @click="open = false"
                class="flex items-center gap-3"
            >
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-brand-600)] text-base font-black text-white">
                    ف
                </span>

                <div>
                    <div class="text-sm font-extrabold text-[var(--color-text-primary)]">
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
                class="flex h-10 w-10 items-center justify-center rounded-xl text-[var(--color-text-muted)] transition-colors hover:bg-[var(--color-neutral-100)] hover:text-[var(--color-text-primary)]"
                aria-label="بستن منو"
            >
                <svg
                    class="h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M6 6l12 12M18 6L6 18"></path>
                </svg>
            </button>
        </div>

        {{-- Search --}}
        <div class="border-b border-[var(--color-border)] p-5">
            <label
                for="mobile-course-search"
                class="sr-only"
            >
                جستجو
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
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m20 20-3.5-3.5"></path>
                </svg>

                <input
                    id="mobile-course-search"
                    type="search"
                    placeholder="جستجوی دوره یا مدرس..."
                    class="ui-input pr-10"
                >
            </div>
        </div>

        {{-- Links --}}
        <div class="flex-1 overflow-y-auto p-5">
            <nav class="space-y-1">

                <a
                    href="{{ route('home') }}"
                    @click="open = false"
                    class="flex items-center rounded-xl px-4 py-3.5 text-sm font-semibold
                    {{ request()->routeIs('home')
                        ? 'bg-[var(--color-brand-50)] text-[var(--color-brand-700)]'
                        : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-neutral-50)]' }}"
                >
                    خانه
                </a>

                <a
                    href="{{ route('courses.index') }}"
                    @click="open = false"
                    class="flex items-center rounded-xl px-4 py-3.5 text-sm font-semibold
                    {{ request()->routeIs('courses.*')
                        ? 'bg-[var(--color-brand-50)] text-[var(--color-brand-700)]'
                        : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-neutral-50)]' }}"
                >
                    دوره‌ها
                </a>

                <a
                    href="{{ route('teachers.index') }}"
                    @click="open = false"
                    class="flex items-center rounded-xl px-4 py-3.5 text-sm font-semibold
                    {{ request()->routeIs('teachers.*')
                        ? 'bg-[var(--color-brand-50)] text-[var(--color-brand-700)]'
                        : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-neutral-50)]' }}"
                >
                    اساتید
                </a>

                <a
                    href="#"
                    @click="open = false"
                    class="flex items-center rounded-xl px-4 py-3.5 text-sm font-semibold text-[var(--color-text-secondary)] hover:bg-[var(--color-neutral-50)]"
                >
                    درباره فرزین
                </a>

                <a
                    href="#"
                    @click="open = false"
                    class="flex items-center rounded-xl px-4 py-3.5 text-sm font-semibold text-[var(--color-text-secondary)] hover:bg-[var(--color-neutral-50)]"
                >
                    تماس با ما
                </a>
            </nav>
        </div>

        {{-- Auth --}}
        <div class="border-t border-[var(--color-border)] p-5">
            <a
                href="#"
                class="ui-button ui-button-primary w-full"
            >
                ورود / ثبت‌نام
            </a>
        </div>
    </aside>
</div>
