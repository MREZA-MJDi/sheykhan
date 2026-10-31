@extends('layouts.parent')

@section('title', 'داشبورد والد | شیخان')
@section('header-title', 'داشبورد والد')

@section('content')
    @php
        $childrenCount = $children->count();
        $progress = max(0, min(100, (float) $overallProgress));
        $childNames = $children->keyBy('id');
    @endphp

    <div class="parent-dashboard">

        <section class="parent-hero">
            <span class="parent-kicker">مرکز پیگیری خانواده</span>

            <h1>
                مسیر تحصیلی فرزندت را روشن‌تر ببین.
            </h1>

            <p>
                میانگین تکمیل درس‌های منتشرشده در دوره‌های فعال، تکلیف‌های باز، جلسات پیش‌رو و نتیجه‌های اخیر فرزندان را از یک فضای امن دنبال کن.
            </p>

            <div class="mt-5 flex flex-wrap gap-2">
                <span class="rounded-full bg-white/10 px-3 py-1.5 text-[10px] font-bold text-white/80">
                    {{ \App\Support\PersianUi::digits($childrenCount) }} فرزند
                </span>

                <span
                    class="rounded-full bg-white/10 px-3 py-1.5 text-[10px] font-bold text-white/80"
                    title="میانگین پیشرفت درس‌های منتشرشده در دوره‌های فعال فرزندان"
                >
                    میانگین پیشرفت درس‌های منتشرشده {{ \App\Support\PersianUi::digits($progress) }}٪
                </span>
            </div>

            <div class="mt-5 flex flex-wrap gap-2" aria-label="اقدام‌های سریع خانواده">
                <a href="{{ route('parent.resources.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-black text-indigo-700 shadow-sm transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-700">
                    جزوه‌ها و منابع فرزندان <span aria-hidden="true">←</span>
                </a>
                <a href="{{ route('parent.messages.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-700">
                    پیام به آموزشگاه <span aria-hidden="true">←</span>
                </a>
                <a href="{{ route('parent.results.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-700">
                    نتایج آموزشی <span aria-hidden="true">←</span>
                </a>
                <a href="{{ route('parent.counseling.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/15">درخواست مشاوره</a>
                <a href="{{ route('parent.attendance.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/15">حضور و غیاب</a>
                <a href="{{ route('courses.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/15">دوره‌های عمومی</a>
                <a href="{{ route('store.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/15">فروشگاه و کتاب‌ها</a>
                <a href="{{ route('blog.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2 text-sm font-black text-white transition hover:bg-white/15">محتوای عمومی</a>
            </div>

            <span class="parent-orb" aria-hidden="true"></span>
        </section>

        <p class="parent-progress-method" role="note">
            این درصد، میانگین پیشرفت درس‌های منتشرشده در دوره‌های فعال فرزندان است؛ درس‌های شروع‌نشده صفر حساب می‌شوند. این شاخص میزان تکمیل محتوا را نشان می‌دهد، نه نمره یا کیفیت یادگیری.
        </p>

        @if($children->isEmpty())
            <div class="parent-empty-state" role="status">
        <span class="parent-empty-state__icon" aria-hidden="true">⌕</span>
        <h3>هنوز فرزندی به حساب والد متصل نشده است</h3>
        <p>پس از اتصال حساب دانش‌آموز، وضعیت دوره‌ها، پیشرفت و جلسات در این داشبورد نمایش داده می‌شود.</p>
    </div>
        @else
            <section aria-labelledby="parent-children-title">
                <div class="mb-4">
                    <span class="parent-kicker" style="color: var(--panel-primary)">
                        فرزندان
                    </span>

                    <h2 id="parent-children-title" class="mt-1 text-xl font-black text-[var(--color-text)]">
                        وضعیت فرزندان
                    </h2>
                </div>

                <div class="parent-children">
                    @foreach($children as $child)
                        <article class="parent-child">
                            <div class="parent-child-head">
                                <div class="parent-child-avatar">
                                    {{ mb_substr(trim((string) $child->name), 0, 1) }}
                                </div>

                                <div class="min-w-0">
                                    <h3 class="parent-child-name truncate">
                                        {{ $child->name }}
                                    </h3>

                                    <span class="parent-child-meta truncate">
                                        {{ $child->studentProfile?->grade ?: 'دانش‌آموز' }}
                                    </span>
                                </div>
                            </div>

                            <div class="parent-child-stats">
                                <div class="parent-child-stat">
                                    <span>پیشرفت</span>
                                    <strong>{{ \App\Support\PersianUi::digits($child->dashboard_progress) }}٪</strong>
                                </div>

                                <div class="parent-child-stat">
                                    <span>دوره فعال</span>
                                    <strong>{{ \App\Support\PersianUi::digits($child->dashboard_courses) }}</strong>
                                </div>

                                <div class="parent-child-stat">
                                    <span>تکالیف در انتظار</span>
                                    <strong>{{ \App\Support\PersianUi::digits($child->dashboard_pending) }}</strong>
                                </div>

                                <div class="parent-child-stat">
                                    <span>وضعیت</span>
                                    <strong>{{ $child->status === 'active' ? 'فعال' : 'غیرفعال' }}</strong>
                                </div>
                            </div>

                            <div class="mt-4 border-t border-slate-100 pt-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h4 class="text-sm font-black">پیشرفت هر دوره و کلاس</h4>
                                    <span class="text-xs text-slate-500">ثبت‌نام‌های فعال و قابل دسترس</span>
                                </div>
                                <div class="mt-3 space-y-3">
                                    @forelse($child->dashboard_courses_detail ?? collect() as $courseProgress)
                                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                            <div class="flex flex-wrap items-start justify-between gap-2">
                                                <div class="min-w-0">
                                                    <strong class="text-sm">{{ $courseProgress->course_title }}</strong>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $courseProgress->classroom_title ?: 'دوره' }} · {{ \App\Support\PersianUi::digits($courseProgress->completed_lessons) }} از {{ \App\Support\PersianUi::digits($courseProgress->total_lessons) }} درس کامل</p>
                                                </div>
                                                <b class="text-sm font-black">{{ \App\Support\PersianUi::digits($courseProgress->progress_average) }}٪</b>
                                            </div>
                                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200"><span class="block h-full rounded-full bg-[var(--panel-primary)]" style="width: {{ $courseProgress->progress_average }}%"></span></div>
                                        </div>
                                    @empty
                                        <p class="rounded-xl bg-slate-50 p-3 text-xs leading-6 text-slate-500">دورهٔ فعال و قابل دسترسی برای نمایش جزئیات پیشرفت پیدا نشد.</p>
                                    @endforelse
                                </div>
                                <div class="mt-3 flex flex-wrap gap-3 text-xs text-slate-600">
                                    <span>حضور ثبت‌شده: {{ \App\Support\PersianUi::digits($child->dashboard_attendance_total ?? 0) }}</span>
                                    <span>حضور: {{ \App\Support\PersianUi::digits($child->dashboard_attendance_present ?? 0) }}</span>
                                    <span>غیبت: {{ \App\Support\PersianUi::digits($child->dashboard_attendance_absent ?? 0) }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <div class="parent-grid">

                <section class="parent-panel dashboard-panel" aria-labelledby="parent-live-title">
                    <div class="parent-panel-head">
                        <div>
                            <span class="parent-kicker" style="color: var(--panel-primary)">هفت روز آینده</span>
                            <h2 id="parent-live-title">جلسات و هماهنگی‌های پیش‌رو</h2>
                            <p>جلسه‌های آنلاین و زمان‌های هماهنگی تأییدشده برای فرزندانت.</p>
                        </div>
                    </div>

                    <div class="parent-list">
                        @forelse($upcomingLiveClasses as $item)
                            @php
                                $child = $childNames->get($item->student_id);
                            @endphp

                            <article class="parent-row">
                                <div class="parent-date">
                                    <strong>{{ \App\Support\PersianUi::time($item->scheduled_at) }}</strong>
                                    <small>{{ \App\Support\PersianUi::date($item->scheduled_at instanceof \Carbon\CarbonInterface ? $item->scheduled_at : \Carbon\Carbon::parse($item->scheduled_at)) }}</small>
                                </div>

                                <div class="min-w-0">
                                    <div class="parent-row-title">{{ $item->title }}</div>
                                    <span class="parent-row-meta">
                                        {{ $item->course_title }}
                                        @if($child)
                                            · {{ $child->name }}
                                        @endif
                                    </span>
                                </div>

                                <span class="parent-score">
                                    {{ ($item->kind ?? 'live_class') === 'meeting' ? 'هماهنگی' : 'آنلاین' }}
                                </span>
                            </article>
                        @empty
                            <div class="parent-row">
                                <div class="parent-date">—</div>
                                <div>
                                    <div class="parent-row-title">جلسه‌ای در هفت روز آینده ثبت نشده است.</div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="parent-panel dashboard-panel" aria-labelledby="parent-results-title">
                    <div class="parent-panel-head">
                        <div>
                            <span class="parent-kicker" style="color: var(--panel-primary)">نتایج</span>
                            <h2 id="parent-results-title">آخرین نتیجه‌ها</h2>
                            <p>آخرین تکالیف تصحیح‌شده فرزندان.</p>
                        </div>
                    </div>

                    <div class="parent-list">
                        @forelse($recentResults as $result)
                            <article class="parent-row">
                                <div class="parent-date">
                                    <strong>{{ $result->score === null ? '—' : \App\Support\PersianUi::digits($result->score) }}</strong>
                                    <small>{{ ($result->result_type ?? 'assignment') === 'exam' ? 'آزمون' : 'تکلیف' }}</small>
                                </div>

                                <div class="min-w-0">
                                    <div class="parent-row-title">{{ $result->title }}</div>
                                    <span class="parent-row-meta">
                                        {{ $result->student_name }} · {{ \App\Support\PersianUi::date($result->occurred_at) }} · {{ $result->status_label ?? 'ثبت‌شده' }}
                                    </span>
                                    @if(filled($result->feedback ?? null))
                                        <p class="mt-2 whitespace-pre-line text-xs leading-6 text-slate-600">بازخورد مدرس: {{ $result->feedback }}</p>
                                    @endif
                                </div>

                                <span class="parent-score">{{ $result->status_label ?? 'ثبت' }}</span>
                            </article>
                        @empty
                            <div class="parent-row">
                                <div class="parent-date">—</div>
                                <div>
                                    <div class="parent-row-title">هنوز نتیجه‌ای ثبت نشده است.</div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </section>

            </div>

            <section class="parent-panel dashboard-panel mt-5 p-5 sm:p-6" aria-labelledby="parent-tasks-title">
                <div class="parent-panel-head">
                    <div>
                        <span class="parent-kicker" style="color:var(--panel-primary)">پیگیری روزانه</span>
                        <h2 id="parent-tasks-title">تکالیف در جریان فرزندان</h2>
                        <p>تکالیف منتشرشده‌ای که هنوز برای فرزند ارسال نشده‌اند.</p>
                    </div>
                    <a href="{{ route('parent.results.index') }}" class="text-sm font-black text-[var(--panel-primary)]">نتایج و بازخوردها ←</a>
                </div>
                <div class="mt-4 space-y-2">
                    @forelse($pendingAssignmentItems ?? collect() as $task)
                        <article class="flex flex-col gap-2 rounded-xl border border-slate-200 bg-white p-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <strong class="text-sm">{{ $task->title }}</strong>
                                <p class="mt-1 text-xs text-slate-500">{{ $task->student_name }} · {{ $task->course_title }}</p>
                            </div>
                            <span class="text-xs {{ $task->due_at && $task->due_at->isPast() ? 'font-bold text-rose-700' : 'text-slate-500' }}">{{ $task->due_at ? ($task->due_at->isPast() ? 'مهلت گذشته · '.\App\Support\PersianUi::date($task->due_at) : 'موعد · '.\App\Support\PersianUi::date($task->due_at)) : 'بدون موعد' }}</span>
                        </article>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">در حال حاضر تکلیف ارسال‌نشده‌ای برای نمایش وجود ندارد.</div>
                    @endforelse
                </div>
            </section>

            <section class="parent-panel dashboard-panel mt-5 p-5 sm:p-6" aria-labelledby="parent-attendance-title">
                <div class="parent-panel-head">
                    <div>
                        <span class="parent-kicker" style="color:var(--panel-primary)">سوابق کلاس</span>
                        <h2 id="parent-attendance-title">آخرین حضور و غیاب‌ها</h2>
                        <p>رکوردهای ثبت‌شده در سامانه برای فرزندان متصل.</p>
                    </div>
                    <a href="{{ route('parent.attendance.index') }}" class="text-sm font-black text-[var(--panel-primary)]">همه سوابق ←</a>
                </div>
                <div class="mt-4 space-y-2">
                    @forelse($recentAttendance ?? collect() as $record)
                        <article class="flex flex-col gap-2 rounded-xl border border-slate-200 bg-white p-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <strong class="text-sm">{{ $record->student_name }} · {{ $record->classroom_title }}</strong>
                                <p class="mt-1 text-xs text-slate-500">{{ $record->course_title ?: 'کلاس' }} · {{ \App\Support\PersianUi::date($record->attendance_date) }}</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold">{{ match($record->status) { 'present' => 'حاضر', 'absent' => 'غایب', 'late' => 'با تأخیر', 'excused' => 'موجه', default => $record->status } }} @if($record->attended_minutes !== null) · {{ \App\Support\PersianUi::digits($record->attended_minutes) }} دقیقه @endif</span>
                        </article>
                    @empty
                        <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">هنوز رکورد حضور و غیابی برای نمایش وجود ندارد.</div>
                    @endforelse
                </div>
            </section>
        @endif

    </div>
@endsection