@props([
    'title' => 'پنل شیخان',
])

<div class="panel-topbar sticky top-0 z-[var(--z-sticky)] flex min-h-16 items-center justify-between gap-4 border-b px-4 sm:px-6">
    <div class="min-w-0">
        <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-[var(--color-success-500)]"></span>
            <span class="text-xs font-semibold text-[var(--color-text-muted)]">شیخان</span>
        </div>
        <h1 class="mt-1 truncate text-base font-black text-[var(--color-text)] sm:text-lg">{{ $title }}</h1>
    </div>

    <div class="flex shrink-0 items-center gap-2">
        <a
            href="{{ route('home') }}"
            class="hidden min-h-10 items-center rounded-xl border border-[var(--color-border)] bg-white px-3 text-xs font-bold text-[var(--color-text-secondary)] transition hover:bg-[var(--color-background-soft)] sm:inline-flex"
        >
            مشاهده سایت
        </a>

        <div class="hidden items-center gap-2 rounded-xl border border-[var(--color-border)] bg-white px-3 py-2 sm:flex">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-brand-50)] text-xs font-black text-[var(--color-brand-700)]">
                {{ mb_substr(auth()->user()->name ?? 'ش', 0, 1) }}
            </span>
            <div class="max-w-32">
                <div class="truncate text-xs font-black text-[var(--color-text)]">{{ auth()->user()->name ?? 'کاربر' }}</div>
                <div class="truncate text-[10px] text-[var(--color-text-muted)]">
                    @if(auth()->user()->hasRole('academy-owner'))
                        مدیر آموزشگاه
                    @elseif(auth()->user()->hasRole('teacher'))
                        مدرس
                    @elseif(auth()->user()->hasRole('student'))
                        دانش‌آموز
                    @else
                        والد
                    @endif
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--color-border)] bg-white px-3 text-xs font-bold text-[var(--color-text-secondary)] transition hover:border-[var(--color-danger-100)] hover:bg-[var(--color-danger-50)] hover:text-[var(--color-danger-600)]"
            >
                خروج
            </button>
        </form>
    </div>
</div>
