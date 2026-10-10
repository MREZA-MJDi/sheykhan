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

            <span class="parent-orb" aria-hidden="true"></span>
        </section>

        <p class="parent-progress-method" role="note">
            این درصد، میانگین پیشرفت درس‌های منتشرشده در دوره‌های فعال فرزندان است؛ درس‌های شروع‌نشده صفر حساب می‌شوند. این شاخص میزان تکمیل محتوا را نشان می‌دهد، نه نمره یا کیفیت یادگیری.
        </p>

        @if($children->isEmpty())
            <x-ui.empty-state
                title="هنوز فرزندی به حساب والد متصل نشده است"
                description="پس از اتصال حساب دانش‌آموز، وضعیت دوره‌ها، پیشرفت و جلسات در این داشبورد نمایش داده می‌شود."
            />
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
                        </article>
                    @endforeach
                </div>
            </section>

            <div class="parent-grid">

                <section class="parent-panel dashboard-panel" aria-labelledby="parent-live-title">
                    <div class="parent-panel-head">
                        <div>
                            <span class="parent-kicker" style="color: var(--panel-primary)">هفت روز آینده</span>
                            <h2 id="parent-live-title">جلسات آنلاین پیش‌رو</h2>
                            <p>جلسه‌های نزدیک هر فرزند در یک لیست زمانی.</p>
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
                                    آنلاین
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
                                    <strong>{{ \App\Support\PersianUi::digits($result->score ?? 0) }}</strong>
                                    <small>نمره</small>
                                </div>

                                <div class="min-w-0">
                                    <div class="parent-row-title">{{ $result->title }}</div>
                                    <span class="parent-row-meta">
                                        {{ $result->student_name }}
                                        ·
                                        {{ \App\Support\PersianUi::date($result->graded_at instanceof \Carbon\CarbonInterface ? $result->graded_at : \Carbon\Carbon::parse($result->graded_at)) }}
                                    </span>
                                </div>

                                <span class="parent-score">ثبت</span>
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
        @endif

    </div>
@endsection