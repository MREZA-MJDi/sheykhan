<header class="relative border-b border-[var(--color-border)] bg-[var(--color-surface)]">
    <nav x-data="{ isOpen: false }" class="relative">
        <x-layout.container size="2xl">
            <div class="flex min-h-20 items-center justify-between gap-6">

                <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-slate-900)] text-lg font-black text-white">
                        ش
                    </div>

                    <div class="leading-tight">
                        <span class="block text-lg font-black text-[var(--color-text)]">شیخان</span>
                        <span class="block text-xs text-[var(--color-text-muted)]">آموزش، رشد، آینده</span>
                    </div>
                </a>

                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--color-border)] text-[var(--color-text)] lg:hidden"
                    @click="isOpen = !isOpen"
                    :aria-expanded="isOpen.toString()"
                    aria-label="باز کردن منو"
                >
                    <svg x-show="!isOpen" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>

                    <svg x-show="isOpen" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </button>

                <div
                    x-cloak
                    :class="isOpen ? 'opacity-100 translate-y-0 pointer-events-auto' : 'opacity-0 -translate-y-2 pointer-events-none lg:pointer-events-auto lg:opacity-100 lg:translate-y-0'"
                    class="absolute inset-x-4 top-[calc(100%+0.5rem)] z-50 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-4 shadow-[var(--shadow-lg)] transition lg:static lg:inset-auto lg:z-auto lg:flex lg:items-center lg:gap-8 lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none"
                >
                    <div class="flex flex-col gap-2 lg:flex-row lg:items-center">
                        <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition hover:bg-[var(--color-surface-muted)]">خانه</a>
                        <a href="{{ route('courses.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition hover:bg-[var(--color-surface-muted)]">دوره‌ها</a>
                        <a href="{{ route('teachers.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition hover:bg-[var(--color-surface-muted)]">مدرس‌ها</a>
                        <a href="{{ route('blog.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium transition hover:bg-[var(--color-surface-muted)]">مقالات</a>
                    </div>

                    <div class="mt-3 border-t border-[var(--color-border)] pt-3 lg:mt-0 lg:border-0 lg:pt-0">
                        @auth
                            <a href="#" class="flex items-center justify-center rounded-xl bg-[var(--color-slate-900)] px-4 py-2.5 text-sm font-bold text-white transition hover:opacity-90">
                                حساب کاربری
                            </a>
                        @else
                            <a href="{{ Route::has('login') ? route('login') : '#' }}" class="flex items-center justify-center rounded-xl bg-[var(--color-slate-900)] px-4 py-2.5 text-sm font-bold text-white transition hover:opacity-90">
                                ورود / ثبت‌نام
                            </a>
                        @endauth
                    </div>
                </div>

            </div>
        </x-layout.container>
    </nav>
</header>
