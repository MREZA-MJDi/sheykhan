<footer class="border-t border-[var(--color-border)] bg-[var(--color-surface)]">
    <x-layout.container size="wide">
        <div class="py-12 sm:py-14">

            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="sm:col-span-2 lg:col-span-1">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--color-slate-900)] font-black text-white shadow-sm">
                            ش
                        </div>

                        <div>
                            <div class="font-black text-[var(--color-text)]">شیخان</div>
                            <div class="text-xs text-[var(--color-text-muted)]">آموزش، رشد، آینده</div>
                        </div>
                    </a>

                    <p class="mt-4 max-w-sm text-sm leading-7 text-[var(--color-text-muted)]">
                        یک محیط یکپارچه برای یادگیری آنلاین، کلاس، تمرین، آزمون و پیگیری پیشرفت.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2 text-xs text-[var(--color-text-muted)]">
                        <span class="rounded-full bg-[var(--color-background-soft)] px-3 py-1.5">دانش‌آموز</span>
                        <span class="rounded-full bg-[var(--color-background-soft)] px-3 py-1.5">مدرس</span>
                        <span class="rounded-full bg-[var(--color-background-soft)] px-3 py-1.5">والد</span>
                        <span class="rounded-full bg-[var(--color-background-soft)] px-3 py-1.5">آموزشگاه</span>
                    </div>
                </div>

                <div>
                    <h3 class="font-bold text-[var(--color-text)]">آموزش</h3>
                    <div class="mt-4 flex flex-col gap-3 text-sm text-[var(--color-text-muted)]">
                        <a class="transition hover:text-[var(--color-text)]" href="{{ route('courses.index') }}">همه دوره‌ها</a>
                        <a class="transition hover:text-[var(--color-text)]" href="{{ route('teachers.index') }}">مدرس‌ها</a>
                        <a class="transition hover:text-[var(--color-text)]" href="{{ route('blog.index') }}">مقالات</a>
                    </div>
                </div>

                <div>
                    <h3 class="font-bold text-[var(--color-text)]">شیخان</h3>
                    <div class="mt-4 flex flex-col gap-3 text-sm text-[var(--color-text-muted)]">
                        <a class="transition hover:text-[var(--color-text)]" href="{{ route('home') }}">خانه</a>
                        <a class="transition hover:text-[var(--color-text)]" href="{{ route('courses.index') }}">مسیرهای یادگیری</a>
                        <a class="transition hover:text-[var(--color-text)]" href="{{ route('blog.index') }}">مرکز محتوا</a>
                    </div>
                </div>

                <div>
                    <h3 class="font-bold text-[var(--color-text)]">شروع کنید</h3>
                    <p class="mt-4 text-sm leading-7 text-[var(--color-text-muted)]">
                        مسیر آموزشی مناسب خودت را پیدا کن و یادگیری را شروع کن.
                    </p>

                    <x-ui.button href="{{ route('courses.index') }}" variant="primary" size="md" class="mt-5">
                        مشاهده دوره‌ها
                    </x-ui.button>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-3 border-t border-[var(--color-border)] pt-6 text-xs text-[var(--color-text-muted)] sm:flex-row sm:items-center sm:justify-between">
                <span>© تمامی حقوق برای شیخان محفوظ است.</span>
                <span>آموزش آنلاین و مدیریت یکپارچه یادگیری</span>
            </div>

        </div>
    </x-layout.container>
</footer>
