@extends('layouts.owner')

@section('title',$course->title.' | مدیریت دوره')
@section('header-title','بازبینی دوره')

@section('content')
@include('owner.partials.learning-workspace-tabs', ['activeSheet' => 'courses', 'academy' => $course->academy])
<div class="course-page panel-page-enter space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-xs font-black text-[var(--color-brand-600)]">بازبینی دوره</p>
                <span class="course-status-badge {{ $course->status }}">
                    <i aria-hidden="true"></i>
                    {{ match($course->status){'published'=>'منتشرشده','archived'=>'آرشیو',default=>'پیش‌نویس'} }}
                </span>
            </div>
            <h1 class="mt-2 break-words text-2xl font-black tracking-tight">{{ $course->title }}</h1>
            <p class="mt-2 max-w-3xl text-sm leading-7 text-[var(--color-text-secondary)]">
                {{ $course->short_description ?: 'برای این دوره هنوز خلاصه‌ای ثبت نشده است.' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('owner.courses.edit',$course) }}" class="course-primary-btn">ویرایش دوره</a>
            <a href="{{ route('owner.courses.index') }}" class="course-secondary-btn">بازگشت</a>
        </div>
    </div>

    <div class="course-readiness-strip {{ $course->status }}">
        <div class="course-readiness-icon">{{ $course->status === 'published' ? '✓' : ($course->status === 'archived' ? '!' : '•') }}</div>
        <div class="min-w-0">
            <strong>{{ match($course->status){'published'=>'این دوره منتشرشده است','archived'=>'این دوره آرشیو شده است',default=>'این دوره هنوز پیش‌نویس است'} }}</strong>
            <p>{{ $course->status === 'published' ? 'اطلاعات زیر وضعیت فعلی دوره را نشان می‌دهد.' : 'قبل از ارائه به دانش‌آموزها، اطلاعات و محتوای دوره را بررسی کن.' }}</p>
        </div>
        <span class="course-readiness-tag">{{ $course->isFree() ? 'رایگان' : 'پولی' }}</span>
    </div>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="خلاصه دوره">
        @foreach([
            ['دانش‌آموز فعال',$course->active_students_count,'ثبت‌نام فعال'],
            ['میانگین پیشرفت',$averageProgress.'٪','بر اساس درس‌های منتشرشده'],
            ['تکلیف',$course->assignments_count,'تکلیف ثبت‌شده'],
            ['آزمون',$course->exams_count,'آزمون ثبت‌شده'],
        ] as [$label,$value,$hint])
            <article class="course-stat">
                <span>{{ $label }}</span>
                <strong>{{ \App\Support\PersianUi::digits($value) }}</strong>
                <small>{{ $hint }}</small>
            </article>
        @endforeach
    </section>

    <section class="grid gap-5 lg:grid-cols-[minmax(0,1.25fr)_minmax(18rem,.75fr)]">
        <div class="panel-card p-5 sm:p-7">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-black">ساختار محتوا</h2>
                    <p class="mt-1 text-xs text-[var(--color-text-muted)]">بخش‌ها و درس‌های دوره.</p>
                </div>
                <span class="course-count-badge">{{ \App\Support\PersianUi::digits($course->sections->count()) }} بخش</span>
            </div>

            <div class="mt-5 grid gap-3">
                @forelse($course->sections as $section)
                    <article class="course-section-card">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="break-words text-sm font-black">{{ $section->title }}</h3>
                                @if($section->description)
                                    <p class="mt-1 text-xs leading-6 text-[var(--color-text-secondary)]">{{ $section->description }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 text-[10px] text-[var(--color-text-muted)]">{{ \App\Support\PersianUi::digits($section->lessons->count()) }} درس</span>
                        </div>
                        <div class="mt-3 grid gap-2">
                            @forelse($section->lessons as $lesson)
                                <div class="flex items-center justify-between gap-3 rounded-xl bg-white px-3 py-2.5">
                                    <span class="min-w-0 truncate text-xs font-semibold">{{ $lesson->title }}</span>
                                    <span class="shrink-0 text-[9px] {{ $lesson->status==='published'?'text-[var(--color-success-700)]':'text-[var(--color-text-muted)]' }}">{{ $lesson->status==='published'?'منتشر':'پیش‌نویس' }}</span>
                                </div>
                            @empty
                                <div class="text-xs text-[var(--color-text-muted)]">درسی در این بخش ثبت نشده است.</div>
                            @endforelse
                        </div>
                    </article>
                @empty
                    <div class="course-empty-state rounded-2xl border-dashed border p-8">
                        <div class="text-sm font-black">ساختار محتوا هنوز آماده نیست</div>
                        <p class="mt-1 text-xs leading-6 text-[var(--color-text-muted)]">این صفحه فعلاً برای بازبینی دوره است؛ محتوای ساختاریافته از بخش‌های مرتبط مدیریت می‌شود.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <aside class="space-y-5">
            <section class="panel-card p-5">
                <h2 class="text-base font-black">مدرس‌ها</h2>
                <div class="mt-4 grid gap-2">
                    @forelse($course->teachers as $teacher)
                        <div class="flex items-center justify-between rounded-xl bg-[var(--color-background-soft)] px-3 py-3">
                            <span class="min-w-0 truncate text-xs font-bold">{{ $teacher->name }}</span>
                            @if($teacher->pivot->is_primary)
                                <span class="course-mini-badge">مدرس اصلی</span>
                            @endif
                        </div>
                    @empty
                        <div class="course-empty-note">مدرسی برای این دوره اختصاص داده نشده است.</div>
                    @endforelse
                </div>
            </section>

            <section class="panel-card p-5">
                <h2 class="text-base font-black">وضعیت و فروش</h2>
                <div class="mt-4 grid gap-3 text-xs">
                    <div class="course-detail-row"><span>وضعیت</span><strong>{{ match($course->status){'published'=>'منتشرشده','archived'=>'آرشیو',default=>'پیش‌نویس'} }}</strong></div>
                    <div class="course-detail-row"><span>دسترسی</span><strong>{{ $course->isFree() ? 'رایگان' : number_format((float)$course->price,0,'.',',').' تومان' }}</strong></div>
                    <div class="course-detail-row"><span>کلاس آنلاین</span><strong>{{ number_format($course->live_classes_count) }}</strong></div>
                    <div class="course-detail-row"><span>رسانه خصوصی</span><strong>{{ number_format($course->media->count()) }} فایل</strong></div>
                </div>
            </section>
        </aside>
    </section>

    @if($canManageCourse && auth()->user()->hasPermission('lessons.manage'))
        <section class="panel-card space-y-5 p-5 sm:p-7">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-black">مدیریت فصل‌ها و ویدئوهای دوره</h2>
                    <p class="mt-1 text-xs leading-6 text-[var(--color-text-muted)]">فصل‌ها و درس‌ها را همین‌جا بساز، ویرایش یا حذف کن. ویدئوی اصلی پس از خرید از مسیر امن SpotPlayer پخش می‌شود؛ فقط درس‌هایی که «رایگان» علامت می‌زنی پیش‌نمایش عمومی دارند.</p>
                </div>
                <span class="course-mini-badge">ویرایش زندهٔ ساختار دوره</span>
            </div>

            <form method="POST" action="{{ route('owner.courses.sections.store', $course) }}" class="grid gap-3 rounded-2xl border border-[var(--color-border)] bg-[var(--color-background-soft)] p-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] md:items-end">
                @csrf
                <label class="grid gap-1 text-xs font-bold">
                    <span>عنوان فصل جدید</span>
                    <input name="title" required maxlength="255" class="course-input" placeholder="مثلاً فصل اول: مبانی و مفاهیم">
                </label>
                <label class="grid gap-1 text-xs font-bold">
                    <span>توضیح فصل</span>
                    <input name="description" maxlength="2000" class="course-input" placeholder="موضوع‌ها و هدف این فصل">
                </label>
                <button type="submit" class="course-primary-btn">افزودن فصل</button>
            </form>

            <div class="grid gap-4">
                @forelse($course->sections as $section)
                    <article class="rounded-2xl border border-[var(--color-border)] p-4 sm:p-5">
                        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-black">{{ $section->title }}</h3>
                                <p class="mt-1 text-[11px] text-[var(--color-text-muted)]">{{ \App\Support\PersianUi::digits($section->lessons->count()) }} درس · ترتیب {{ \App\Support\PersianUi::digits($section->sort_order) }}</p>
                            </div>
                            @if($section->lessons->isEmpty())
                                <form method="POST" action="{{ route('owner.courses.sections.destroy', [$course, $section]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" data-confirm="این فصل خالی حذف شود؟" class="course-action-btn danger">حذف فصل خالی</button>
                                </form>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('owner.courses.sections.update', [$course, $section]) }}" class="grid gap-3 border-b border-[var(--color-border)] pb-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_6rem_auto] md:items-end">
                            @csrf
                            @method('PATCH')
                            <label class="grid gap-1 text-[11px] font-bold"><span>نام فصل</span><input name="title" value="{{ $section->title }}" required maxlength="255" class="course-input"></label>
                            <label class="grid gap-1 text-[11px] font-bold"><span>توضیحات</span><input name="description" value="{{ $section->description }}" maxlength="2000" class="course-input"></label>
                            <label class="grid gap-1 text-[11px] font-bold"><span>ترتیب</span><input name="sort_order" type="number" min="1" max="9999" value="{{ $section->sort_order }}" required class="course-input"></label>
                            <button type="submit" class="course-action-btn primary">ذخیره فصل</button>
                        </form>

                        <form method="POST" action="{{ route('owner.courses.lessons.store', [$course, $section]) }}" class="mt-4 grid gap-3 rounded-xl bg-[var(--color-background-soft)] p-4 md:grid-cols-2">
                            @csrf
                            <div class="md:col-span-2">
                                <strong class="text-xs font-black">افزودن درس به {{ $section->title }}</strong>
                                <p class="mt-1 text-[10px] leading-5 text-[var(--color-text-muted)]">برای درس ویدئویی، ویدئوی کامل را در دورهٔ متناظر SpotPlayer قرار بده و این درس را به‌عنوان سرفصل قابل مشاهده ثبت کن.</p>
                            </div>
                            <label class="grid gap-1 text-[11px] font-bold"><span>عنوان درس</span><input name="title" required maxlength="255" class="course-input" placeholder="مثلاً جلسه اول: آشنایی با موضوع"></label>
                            <label class="grid gap-1 text-[11px] font-bold"><span>شناسهٔ درس (Slug)</span><input name="slug" required maxlength="255" pattern="[A-Za-z0-9_-]+" dir="ltr" class="course-input" placeholder="chapter-one-lesson-1"></label>
                            <label class="grid gap-1 text-[11px] font-bold"><span>نوع محتوا</span><select name="type" required class="course-input"><option value="video">ویدئوی SpotPlayer</option><option value="text">متن</option><option value="file">فایل</option><option value="quiz">آزمونک</option><option value="live">جلسه زنده</option></select></label>
                            <label class="grid gap-1 text-[11px] font-bold"><span>مدت (ثانیه)</span><input name="duration_seconds" type="number" min="0" max="86400" value="0" class="course-input"></label>
                            <label class="grid gap-1 text-[11px] font-bold md:col-span-2"><span>خلاصهٔ درس</span><input name="summary" maxlength="500" class="course-input" placeholder="توضیح کوتاه برای دانش‌آموز"></label>
                            <label class="flex items-center gap-2 text-[11px] font-bold"><input type="checkbox" name="is_free" value="1"> این درس پیش‌نمایش رایگان باشد</label>
                            <label class="grid gap-1 text-[11px] font-bold"><span>وضعیت</span><select name="status" class="course-input"><option value="draft">پیش‌نویس</option><option value="published">منتشرشده</option></select></label>
                            <label class="grid gap-1 text-[11px] font-bold md:col-span-2"><span>محتوای متنی (اختیاری)</span><textarea name="content" rows="2" maxlength="30000" class="course-input" placeholder="یادداشت یا متن کمکی درس"></textarea></label>
                            <div class="md:col-span-2 flex justify-end"><button type="submit" class="course-action-btn primary">افزودن درس</button></div>
                        </form>

                        <div class="mt-4 grid gap-2">
                            @forelse($section->lessons as $lesson)
                                <details class="rounded-xl border border-[var(--color-border)] bg-white p-3">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3">
                                        <span class="min-w-0 truncate text-xs font-bold">{{ $lesson->title }}</span>
                                        <span class="shrink-0 text-[10px] text-[var(--color-text-muted)]">{{ $lesson->is_free ? 'پیش‌نمایش رایگان' : 'قفل تا خرید' }} · {{ $lesson->status === 'published' ? 'منتشر' : ($lesson->status === 'archived' ? 'آرشیو' : 'پیش‌نویس') }}</span>
                                    </summary>
                                    <form method="POST" action="{{ route('owner.courses.lessons.update', [$course, $section, $lesson]) }}" class="mt-3 grid gap-3 border-t border-[var(--color-border)] pt-3 md:grid-cols-2">
                                        @csrf
                                        @method('PATCH')
                                        <label class="grid gap-1 text-[11px] font-bold"><span>عنوان</span><input name="title" value="{{ $lesson->title }}" required maxlength="255" class="course-input"></label>
                                        <label class="grid gap-1 text-[11px] font-bold"><span>شناسهٔ درس</span><input name="slug" value="{{ $lesson->slug }}" required maxlength="255" pattern="[A-Za-z0-9_-]+" dir="ltr" class="course-input"></label>
                                        <label class="grid gap-1 text-[11px] font-bold"><span>نوع</span><select name="type" class="course-input">@foreach(['video'=>'ویدئوی SpotPlayer','text'=>'متن','file'=>'فایل','quiz'=>'آزمونک','live'=>'جلسه زنده'] as $type=>$label)<option value="{{ $type }}" @selected($lesson->type === $type)>{{ $label }}</option>@endforeach</select></label>
                                        <label class="grid gap-1 text-[11px] font-bold"><span>مدت (ثانیه)</span><input name="duration_seconds" type="number" min="0" max="86400" value="{{ $lesson->duration_seconds }}" class="course-input"></label>
                                        <label class="grid gap-1 text-[11px] font-bold md:col-span-2"><span>خلاصه</span><input name="summary" value="{{ $lesson->summary }}" maxlength="500" class="course-input"></label>
                                        <label class="flex items-center gap-2 text-[11px] font-bold"><input type="checkbox" name="is_free" value="1" @checked($lesson->is_free)> پیش‌نمایش رایگان</label>
                                        <label class="grid gap-1 text-[11px] font-bold"><span>وضعیت</span><select name="status" class="course-input"><option value="draft" @selected($lesson->status === 'draft')>پیش‌نویس</option><option value="published" @selected($lesson->status === 'published')>منتشرشده</option><option value="archived" @selected($lesson->status === 'archived')>آرشیو</option></select></label>
                                        <label class="grid gap-1 text-[11px] font-bold md:col-span-2"><span>محتوای متنی (اختیاری)</span><textarea name="content" rows="2" maxlength="30000" class="course-input">{{ $lesson->content }}</textarea></label>
                                        <label class="grid gap-1 text-[11px] font-bold"><span>ترتیب</span><input name="sort_order" type="number" min="1" max="9999" value="{{ $lesson->sort_order }}" required class="course-input"></label>
                                        <div class="flex flex-wrap items-end justify-end gap-2 md:col-span-2">
                                            <button type="submit" class="course-action-btn primary">ذخیره درس</button>
                                        </div>
                                    </form>
                                    @if($lesson->status === 'draft')
                                        <form method="POST" action="{{ route('owner.courses.lessons.destroy', [$course, $section, $lesson]) }}" class="mt-2 flex justify-end">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" data-confirm="حذف این درس پیش‌نویس؟ حذف فقط وقتی ممکن است که سابقهٔ پیشرفت یا منبع آموزشی نداشته باشد." class="course-action-btn danger">حذف درس پیش‌نویس</button>
                                        </form>
                                    @endif
                                </details>
                            @empty
                                <p class="rounded-xl border border-dashed border-[var(--color-border)] p-4 text-xs text-[var(--color-text-muted)]">هنوز درسی در این فصل ثبت نشده است.</p>
                            @endforelse
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-[var(--color-border)] p-6 text-center text-xs text-[var(--color-text-muted)]">برای شروع، فصل اول را از فرم بالا بساز.</div>
                @endforelse
            </div>
        </section>
    @endif

    <section class="grid gap-5 lg:grid-cols-2">
        <section class="panel-card p-5 sm:p-6">
            <div>
                <h2 class="text-base font-black">آزمون‌ها</h2>
                <p class="mt-1 text-xs text-[var(--color-text-muted)]">خلاصه آزمون‌های مرتبط با دوره.</p>
            </div>
            <div class="mt-4 grid gap-2">
                @forelse($course->exams as $exam)
                    <div class="course-list-card">
                        <div class="flex items-center justify-between gap-3">
                            <strong class="min-w-0 truncate text-xs">{{ $exam->title }}</strong>
                            <span class="text-[9px] {{ $exam->status==='published'?'text-[var(--color-success-700)]':'text-[var(--color-text-muted)]' }}">{{ $exam->status==='published'?'منتشر':'پیش‌نویس' }}</span>
                        </div>
                        <div class="mt-1 truncate text-[9px] text-[var(--color-text-muted)]">{{ $exam->teacher?->name ?: 'بدون مدرس' }} · {{ \App\Support\PersianUi::digits($exam->questions_count) }} سؤال · {{ \App\Support\PersianUi::digits($exam->attempts_count) }} تلاش</div>
                    </div>
                @empty
                    <div class="course-empty-note">آزمونی برای این دوره ثبت نشده است.</div>
                @endforelse
            </div>
        </section>

        <section class="panel-card p-5 sm:p-6">
            <div>
                <h2 class="text-base font-black">تکالیف</h2>
                <p class="mt-1 text-xs text-[var(--color-text-muted)]">مواردی که نیاز به بررسی دارند.</p>
            </div>
            <div class="mt-4 grid gap-2">
                @forelse($course->assignments as $assignment)
                    <div class="course-list-card">
                        <div class="flex items-center justify-between gap-3">
                            <strong class="min-w-0 truncate text-xs">{{ $assignment->title }}</strong>
                            <span class="text-[9px] font-bold">{{ \App\Support\PersianUi::digits($assignment->pending_submissions_count) }} مورد نیازمند بررسی</span>
                        </div>
                        <div class="mt-1 truncate text-[9px] text-[var(--color-text-muted)]">{{ $assignment->teacher?->name ?: 'بدون مدرس' }} · {{ \App\Support\PersianUi::digits($assignment->submissions_count) }} ارسال</div>
                    </div>
                @empty
                    <div class="course-empty-note">تکلیفی برای این دوره ثبت نشده است.</div>
                @endforelse
            </div>
        </section>
    </section>
</div>
@endsection