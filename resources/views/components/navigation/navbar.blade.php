<header class="home-glass-nav sticky top-0 z-[var(--z-sticky)] border-b border-[var(--color-border)]">
    <nav x-data="{ isOpen: false }" class="relative">
        <x-layout.container size="wide">
            <div class="flex min-h-18 items-center justify-between gap-4 py-2">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3" aria-label="شیخان">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-slate-900)] text-lg font-black text-white shadow-sm">ش</div>
                    <div class="leading-tight">
                        <span class="block text-base font-black text-[var(--color-text)] sm:text-lg">شیخان</span>
                        <span class="block text-[11px] text-[var(--color-text-muted)] sm:text-xs">آموزش، رشد، آینده</span>
                    </div>
                </a>

                <div class="hidden items-center gap-1 lg:flex">
                    <a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif class="rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('home') ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]' : 'text-[var(--color-text-muted)] hover:bg-[var(--color-background-soft)] hover:text-[var(--color-text)]' }}">خانه</a>

                    <div x-data="{ coursesOpen: false }" class="relative">
                        <button type="button" @click="coursesOpen = !coursesOpen" @click.outside="coursesOpen = false" :aria-expanded="coursesOpen.toString()" class="inline-flex items-center gap-1 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('courses.*') ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]' : 'text-[var(--color-text-muted)] hover:bg-[var(--color-background-soft)] hover:text-[var(--color-text)]' }}">
                            دوره‌ها
                            <svg class="h-3.5 w-3.5 transition" :class="coursesOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-cloak x-show="coursesOpen" x-transition class="absolute end-0 top-[calc(100%+0.5rem)] z-50 w-64 overflow-hidden rounded-2xl border border-[var(--color-border)] bg-white p-2 shadow-[var(--shadow-lg)]">
                            <a href="{{ route('courses.index') }}" class="block rounded-xl px-4 py-3 text-sm font-bold hover:bg-[var(--color-background-soft)]">همه دوره‌ها</a>
                            @foreach(($navigationGrades ?? collect()) as $grade)
                                <a href="{{ route('courses.index', ['grade' => $grade->id]) }}" class="block rounded-xl px-4 py-2.5 text-sm font-semibold text-[var(--color-text-muted)] hover:bg-[var(--color-background-soft)] hover:text-[var(--color-text)]">{{ $grade->title }}</a>
                            @endforeach
                        </div>
                    </div>

                    <a href="{{ route('teachers.index') }}" @if(request()->routeIs('teachers.*')) aria-current="page" @endif class="rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('teachers.*') ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]' : 'text-[var(--color-text-muted)] hover:bg-[var(--color-background-soft)] hover:text-[var(--color-text)]' }}">مدرس‌ها</a>
                    <a href="{{ route('blog.index') }}" @if(request()->routeIs('blog.*')) aria-current="page" @endif class="rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('blog.*') ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]' : 'text-[var(--color-text-muted)] hover:bg-[var(--color-background-soft)] hover:text-[var(--color-text)]' }}">مقالات</a>
                    <a href="{{ route('store.index') }}" @if(request()->routeIs('store.*')) aria-current="page" @endif class="rounded-xl px-3.5 py-2.5 text-sm font-semibold transition {{ request()->routeIs('store.*') ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]' : 'text-[var(--color-text-muted)] hover:bg-[var(--color-background-soft)] hover:text-[var(--color-text)]' }}">فروشگاه</a>
                </div>

                <div class="hidden items-center gap-3 lg:flex">
                    @auth
                        <div x-data="{ accountOpen: false }" class="relative">
                            <button
                                type="button"
                                @click="accountOpen = !accountOpen"
                                :aria-expanded="accountOpen.toString()"
                                class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-[var(--color-slate-900)] px-4 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:shadow-md"
                            >
                                <span class="max-w-28 truncate">{{ auth()->user()->name }}</span>
                                <svg class="h-4 w-4 transition" :class="accountOpen ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                                </svg>
                            </button>

                            <div
                                x-cloak
                                x-show="accountOpen"
                                x-transition
                                @click.outside="accountOpen = false"
                                class="absolute end-0 top-[calc(100%+0.6rem)] z-50 w-64 overflow-hidden rounded-2xl border border-[var(--color-border)] bg-white p-2 shadow-[var(--shadow-lg)]"
                            >
                                <a href="{{ route('dashboard') }}" class="block rounded-xl px-4 py-3 transition hover:bg-[var(--color-background-soft)]">
                                    <div class="text-sm font-bold text-[var(--color-text)]">داشبورد من</div>
                                    <div class="mt-1 text-xs text-[var(--color-text-muted)]">ورود به فضای نقش شما</div>
                                </a>

                                <div class="my-1 h-px bg-[var(--color-border)]"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center rounded-xl px-4 py-3 text-sm font-bold text-[var(--color-danger-600)] transition hover:bg-[var(--color-danger-50)]">
                                        خروج از حساب
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[var(--color-slate-900)] px-4 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:shadow-md">
                            ورود / ثبت‌نام
                        </a>
                    @endauth
                </div>

                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--color-border)] bg-white text-[var(--color-text)] lg:hidden"
                    @click="isOpen = !isOpen"
                    :aria-expanded="isOpen.toString()"
                    aria-controls="site-mobile-navigation"
                    aria-label="باز کردن منو"
                >
                    <svg x-show="!isOpen" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                    <svg x-show="isOpen" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <div
                id="site-mobile-navigation"
                x-cloak
                x-show="isOpen"
                x-transition
                @click.outside="isOpen = false"
                class="border-t border-[var(--color-border)] py-4 lg:hidden"
            >
                <div class="grid gap-1">
                    <a href="{{ route('home') }}" @click="isOpen = false" class="rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('home') ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]' : 'text-[var(--color-text-muted)] hover:bg-[var(--color-background-soft)] hover:text-[var(--color-text)]' }}">خانه</a>
                    <details class="rounded-xl {{ request()->routeIs('courses.*') ? 'bg-[var(--color-primary-50)]' : '' }}">
                        <summary class="cursor-pointer list-none rounded-xl px-4 py-3 text-sm font-semibold text-[var(--color-text-muted)]">دوره‌ها</summary>
                        <div class="grid gap-1 px-2 pb-2">
                            <a href="{{ route('courses.index') }}" @click="isOpen = false" class="rounded-lg px-3 py-2 text-sm font-bold hover:bg-white">همه دوره‌ها</a>
                            @foreach(($navigationGrades ?? collect()) as $grade)
                                <a href="{{ route('courses.index', ['grade' => $grade->id]) }}" @click="isOpen = false" class="rounded-lg px-3 py-2 text-sm text-[var(--color-text-muted)] hover:bg-white">{{ $grade->title }}</a>
                            @endforeach
                        </div>
                    </details>
                    <a href="{{ route('teachers.index') }}" @click="isOpen = false" class="rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('teachers.*') ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]' : 'text-[var(--color-text-muted)] hover:bg-[var(--color-background-soft)] hover:text-[var(--color-text)]' }}">مدرس‌ها</a>
                    <a href="{{ route('blog.index') }}" @click="isOpen = false" class="rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('blog.*') ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]' : 'text-[var(--color-text-muted)] hover:bg-[var(--color-background-soft)] hover:text-[var(--color-text)]' }}">مقالات</a>
                    <a href="{{ route('store.index') }}" @click="isOpen = false" class="rounded-xl px-4 py-3 text-sm font-semibold transition {{ request()->routeIs('store.*') ? 'bg-[var(--color-primary-50)] text-[var(--color-primary-700)]' : 'text-[var(--color-text-muted)] hover:bg-[var(--color-background-soft)] hover:text-[var(--color-text)]' }}">فروشگاه</a>
                </div>

                <div class="mt-3 border-t border-[var(--color-border)] pt-3">
                    @auth
                        <a href="{{ route('dashboard') }}" @click="isOpen = false" class="flex min-h-11 items-center justify-center rounded-xl bg-[var(--color-slate-900)] px-4 text-sm font-bold text-white">
                            ورود به داشبورد
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="mt-2">
                            @csrf
                            <button type="submit" @click="isOpen = false" class="flex min-h-11 w-full items-center justify-center rounded-xl border border-[var(--color-border)] bg-white px-4 text-sm font-bold text-[var(--color-danger-600)]">
                                خروج از حساب
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" @click="isOpen = false" class="flex min-h-11 items-center justify-center rounded-xl bg-[var(--color-slate-900)] px-4 text-sm font-bold text-white">
                            ورود / ثبت‌نام
                        </a>
                    @endauth
                </div>
            </div>
        </x-layout.container>
    </nav>
</header>
