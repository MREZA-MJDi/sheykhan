<footer class="border-t border-[var(--color-border)] bg-[var(--color-surface)]">
    <x-layout.container size="2xl">
        <div class="py-12">

            <div class="grid gap-10 md:grid-cols-4">
                <div class="md:col-span-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-slate-900)] font-black text-white">ش</div>
                        <div>
                            <div class="font-black">شیخان</div>
                            <div class="text-xs text-[var(--color-text-muted)]">آموزش، رشد، آینده</div>
                        </div>
                    </a>
                    <p class="mt-4 text-sm leading-7 text-[var(--color-text-muted)]">
                        یک محیط یکپارچه برای یادگیری آنلاین، کلاس، تمرین، آزمون و پیگیری پیشرفت.
                    </p>
                </div>

                <div>
                    <h3 class="font-bold">آموزش</h3>
                    <div class="mt-4 flex flex-col gap-3 text-sm text-[var(--color-text-muted)]">
                        <a class="hover:text-[var(--color-text)]" href="{{ route('courses.index') }}">همه دوره‌ها</a>
                        <a class="hover:text-[var(--color-text)]" href="{{ route('teachers.index') }}">مدرس‌ها</a>
                        <a class="hover:text-[var(--color-text)]" href="{{ route('blog.index') }}">مقالات</a>
                    </div>
                </div>

                <div>
                    <h3 class="font-bold">شیخان</h3>
                    <div class="mt-4 flex flex-col gap-3 text-sm text-[var(--color-text-muted)]">
                        <a class="hover:text-[var(--color-text)]" href="{{ route('home') }}">خانه</a>
                        <a class="hover:text-[var(--color-text)]" href="#">درباره ما</a>
                        <a class="hover:text-[var(--color-text)]" href="#">تماس با ما</a>
                    </div>
                </div>

                <div>
                    <h3 class="font-bold">شروع کنید</h3>
                    <p class="mt-4 text-sm leading-7 text-[var(--color-text-muted)]">
                        مسیر آموزشی مناسب خودت را پیدا کن و یادگیری را شروع کن.
                    </p>
                    <a href="{{ route('courses.index') }}" class="mt-4 inline-flex rounded-xl bg-[var(--color-slate-900)] px-4 py-2.5 text-sm font-bold text-white">
                        مشاهده دوره‌ها
                    </a>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-4 border-t border-[var(--color-border)] pt-6 text-sm text-[var(--color-text-muted)] sm:flex-row sm:items-center sm:justify-between">
                <span>© تمامی حقوق برای شیخان محفوظ است.</span>
                <span>آموزش آنلاین و مدیریت یکپارچه یادگیری</span>
            </div>

        </div>
    </x-layout.container>
</footer>
