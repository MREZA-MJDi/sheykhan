@php
    $isOwnerWorkflow = str_starts_with($routePrefix, 'owner.');
    $classrooms = $course->exists ? $course->classrooms : collect();
    $sections = $course->exists ? $course->sections : collect();
    $lessonCount = $sections->sum(fn ($section) => $section->lessons->count());
    $videoLessonCount = $sections->flatMap(fn ($section) => $section->lessons)->where('type', 'video')->count();
    $classCreateUrl = $course->exists
        ? ($isOwnerWorkflow
            ? route('owner.classrooms.create', $course->academy)
            : route('teacher.classrooms.create', ['course_id' => $course->id]))
        : null;
@endphp



@if (! $course->exists)
    <section id="course-step-media" class="scroll-mt-6 rounded-2xl border border-dashed border-[var(--color-border)] bg-[var(--color-background-soft)] p-5">
        <div class="text-sm font-black">مرحله ۲ · تصویر و فایل‌های دوره</div>
        <p class="mt-2 text-xs leading-7 text-[var(--color-text-secondary)]">
            پس از ذخیره اطلاعات پایه، بخش کاور و فایل‌های خصوصی فعال می‌شود. فایل‌های آموزشی از مسیر امن و قابل ادامه آپلود خواهند شد.
        </p>
    </section>
@endif

<section id="course-step-classrooms" class="scroll-mt-6 panel-card space-y-4 p-5 sm:p-7">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="text-xs font-black text-[var(--color-brand-600)]">مرحله ۳</div>
            <h2 class="mt-1 text-base font-black">کلاس‌های متصل به این دوره</h2>
            <p class="mt-1 text-xs leading-6 text-[var(--color-text-secondary)]">
                دوره محتوای آموزشی است؛ کلاس یک گروه اجرایی از دانش‌آموزان همان دوره است. برنامه، ظرفیت و مدرس کلاس در فرم واقعی کلاس مدیریت می‌شود.
            </p>
        </div>
        @if($course->exists)
            <a href="{{ $classCreateUrl }}" class="course-primary-btn">ساخت کلاس برای این دوره</a>
        @endif
    </div>

    @if($course->exists)
        <div class="grid gap-2 sm:grid-cols-2">
            @forelse($classrooms as $classroom)
                <a href="{{ $isOwnerWorkflow ? route('owner.classrooms.edit', [$course->academy, $classroom]) : route('teacher.classrooms.edit', $classroom) }}"
                   class="flex min-w-0 items-center justify-between gap-3 rounded-xl border border-[var(--color-border)] bg-[var(--color-background-soft)] p-3 transition hover:border-[var(--color-brand-600)]">
                    <span class="min-w-0">
                        <strong class="block truncate text-xs font-black">{{ $classroom->title }}</strong>
                        <small class="mt-1 block text-[10px] text-[var(--color-text-muted)]">
                            {{ $classroom->code }} · {{ $classroom->status === 'archived' ? 'آرشیو' : 'فعال' }}
                        </small>
                    </span>
                    <span class="shrink-0 text-[10px] font-bold text-[var(--color-text-secondary)]">
                        {{ \App\Support\PersianUi::digits((int) ($classroom->active_students_count ?? 0)) }} دانش‌آموز
                    </span>
                </a>
            @empty
                <div class="rounded-xl border border-dashed border-[var(--color-border)] p-4 text-xs text-[var(--color-text-muted)] sm:col-span-2">
                    هنوز کلاسی برای این دوره ساخته نشده است. با ساخت کلاس، ثبت‌نام و برنامه اجرایی از خود دوره جدا و به همین دوره متصل می‌ماند.
                </div>
            @endforelse
        </div>
    @else
        <p class="rounded-xl border border-dashed border-[var(--color-border)] p-4 text-xs leading-7 text-[var(--color-text-muted)]">
            ابتدا دوره را ذخیره کن؛ بعد می‌توانی کلاس‌های مرتبط را از همین صفحه بسازی.
        </p>
    @endif
</section>

<section id="course-step-lessons" class="scroll-mt-6 panel-card space-y-4 p-5 sm:p-7">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="text-xs font-black text-[var(--color-brand-600)]">مرحله ۴</div>
            <h2 class="mt-1 text-base font-black">سرفصل، درس و ویدئوی آموزشی</h2>
            <p class="mt-1 text-xs leading-6 text-[var(--color-text-secondary)]">
                درس‌ها به سرفصل‌های واقعی دوره متصل می‌شوند؛ فایل‌های جزوه و تمرین با مجوز رسانه خصوصی نگهداری می‌شوند.
            </p>
        </div>
        @if($course->exists && ! $isOwnerWorkflow)
            <a href="{{ route('teacher.courses.content', $course) }}" class="course-primary-btn">مدیریت درس‌ها و منابع</a>
        @elseif($course->exists)
            <a href="{{ route('owner.courses.show', $course) }}" class="course-secondary-btn">بازبینی ساختار دوره</a>
        @endif
    </div>

    @if($course->exists)
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl bg-[var(--color-background-soft)] p-3">
                <span class="block text-[10px] text-[var(--color-text-muted)]">سرفصل‌ها</span>
                <strong class="mt-1 block text-lg font-black">{{ \App\Support\PersianUi::digits($sections->count()) }}</strong>
            </div>
            <div class="rounded-xl bg-[var(--color-background-soft)] p-3">
                <span class="block text-[10px] text-[var(--color-text-muted)]">درس‌ها</span>
                <strong class="mt-1 block text-lg font-black">{{ \App\Support\PersianUi::digits($lessonCount) }}</strong>
            </div>
            <div class="rounded-xl bg-[var(--color-background-soft)] p-3">
                <span class="block text-[10px] text-[var(--color-text-muted)]">درس ویدئویی</span>
                <strong class="mt-1 block text-lg font-black">{{ \App\Support\PersianUi::digits($videoLessonCount) }}</strong>
            </div>
        </div>
        @if($isOwnerWorkflow)
            <p class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs leading-7 text-slate-700">
                بازبینی محتوای درس‌ها از صفحه مدیریت دوره انجام می‌شود. مدرس ساختار درس را در فضای خودش تنظیم می‌کند؛ مالک وضعیت انتشار دوره را کنترل می‌کند.
            </p>
        @else
            <p class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-7 text-amber-900">
                فایل ویدئوی اصلی از پنل SpotPlayer بارگذاری و با شناسه همان دوره متصل می‌شود. این فرم فایل ویدئویی خام را به مسیر عمومی یا دانلود عادی تبدیل نمی‌کند.
                @if(filled($course->spotplayer_course_id))
                    شناسه SpotPlayer ثبت شده است.
                @else
                    هنوز شناسه SpotPlayer برای دوره ثبت نشده است.
                @endif
            </p>
        @endif
    @else
        <p class="rounded-xl border border-dashed border-[var(--color-border)] p-4 text-xs leading-7 text-[var(--color-text-muted)]">
            پس از ساخت دوره، بخش سرفصل‌ها و درس‌ها در دسترس خواهد بود.
        </p>
    @endif
</section>
