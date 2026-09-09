<footer
    class="border-t border-[var(--color-border)] bg-[var(--color-surface)]"
    dir="rtl"
>
    <div class="container-farzin py-14 lg:py-16">

        {{-- CTA --}}
        <div class="rounded-3xl bg-[var(--color-brand-50)] px-6 py-10 text-center sm:px-10">
            <h2 class="text-2xl font-extrabold text-[var(--color-text-primary)] sm:text-3xl">
                مسیر یادگیریت رو از همین امروز شروع کن
            </h2>

            <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-[var(--color-text-secondary)] sm:text-base">
                با دوره‌های تخصصی فرزین، برای آزمون‌های تیزهوشان و مسیر تحصیلی آینده‌ات آماده‌تر شو.
            </p>

            <div class="mt-7">
                <a
                    href="{{ route('courses.index') }}"
                    class="ui-button ui-button-primary ui-button-lg"
                >
                    مشاهده دوره‌ها

                    <svg
                        class="h-4.5 w-4.5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </a>
            </div>
        </div>

        {{-- Links --}}
        <div class="mt-14 grid grid-cols-2 gap-10 md:grid-cols-4">

            <div>
                <h3 class="text-sm font-bold text-[var(--color-text-primary)]">
                    آموزش
                </h3>

                <div class="mt-5 space-y-3.5">
                    <a href="{{ route('courses.index') }}" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        همه دوره‌ها
                    </a>

                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        تیزهوشان
                    </a>

                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        کلاس‌های تقویتی
                    </a>

                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        آزمون‌ها
                    </a>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-[var(--color-text-primary)]">
                    فرزین
                </h3>

                <div class="mt-5 space-y-3.5">
                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        درباره فرزین
                    </a>

                    <a href="{{ route('teachers.index') }}" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        اساتید
                    </a>

                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        تماس با ما
                    </a>

                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        سوالات متداول
                    </a>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-[var(--color-text-primary)]">
                    دانش‌آموز
                </h3>

                <div class="mt-5 space-y-3.5">
                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        پنل دانش‌آموز
                    </a>

                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        برنامه درسی
                    </a>

                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        آزمون‌ها
                    </a>

                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        پیشرفت تحصیلی
                    </a>
                </div>
            </div>

            <div>
                <h3 class="text-sm font-bold text-[var(--color-text-primary)]">
                    ارتباط با ما
                </h3>

                <div class="mt-5 space-y-3.5">
                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        پشتیبانی
                    </a>

                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        قوانین و مقررات
                    </a>

                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        حریم خصوصی
                    </a>

                    <a href="#" class="block text-sm text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]">
                        اینستاگرام
                    </a>
                </div>
            </div>
        </div>

        {{-- Bottom --}}
        <div class="mt-12 border-t border-[var(--color-border)] pt-7">
            <div class="flex flex-col items-center justify-between gap-5 sm:flex-row">

                <a
                    href="{{ route('home') }}"
                    class="flex items-center gap-3"
                >
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--color-brand-600)] text-sm font-black text-white">
                        ف
                    </span>

                    <div class="leading-tight">
                        <div class="text-sm font-extrabold text-[var(--color-text-primary)]">
                            فرزین
                        </div>

                        <div class="text-xs text-[var(--color-text-muted)]">
                            مرکز آموزش تخصصی
                        </div>
                    </div>
                </a>

                <p class="text-center text-xs text-[var(--color-text-muted)] sm:text-right">
                    © تمامی حقوق برای فرزین محفوظ است.
                </p>
            </div>
        </div>

    </div>
</footer>
