@php
    $weekdays = [6 => 'شنبه', 0 => 'یکشنبه', 1 => 'دوشنبه', 2 => 'سه‌شنبه', 3 => 'چهارشنبه', 4 => 'پنجشنبه', 5 => 'جمعه'];
    $calendarClassrooms = method_exists($classrooms, 'getCollection') ? $classrooms->getCollection() : collect($classrooms);
    $calendarRows = $calendarClassrooms->flatMap(function ($classroom) {
        return $classroom->schedules->map(function ($schedule) use ($classroom) {
            return ['classroom' => $classroom, 'schedule' => $schedule];
        });
    });
    $slotsByDay = $calendarRows->groupBy(fn ($row) => (int) $row['schedule']->weekday);
    $todayTehran = now('Asia/Tehran');
    $jalaliDate = \App\Support\PersianUi::calendar($todayTehran);
    $season = match (true) {
        $jalaliDate['month'] <= 3 => 'بهار',
        $jalaliDate['month'] <= 6 => 'تابستان',
        $jalaliDate['month'] <= 9 => 'پاییز',
        default => 'زمستان',
    };
@endphp

<section class="weekly-calendar" aria-labelledby="weekly-calendar-heading" data-weekly-calendar>
    <header class="weekly-calendar-header">
        <div class="weekly-calendar-title">
            <span class="weekly-calendar-kicker">تقویم هفتگی · برنامه‌های تکرارشونده</span>
            <h2 id="weekly-calendar-heading">برنامهٔ کلاس‌ها در یک نگاه</h2>
            <p>هر کارت یک زمان ثابت در هفته است. سبز یعنی لینک ورود آماده است؛ قرمز یعنی لینک این بازه باید بررسی یا تکمیل شود. این صفحه برنامهٔ هفتگی را نشان می‌دهد، نه جلسهٔ تاریخ‌دار.</p>
        </div>
        <div class="weekly-calendar-date-chip" aria-label="تاریخ امروز در تقویم شمسی">
            <strong>{{ $jalaliDate['today_label'] }}</strong>
            <span>{{ $jalaliDate['month_label'] }} · {{ $season }}</span>
            <small>امروز: {{ $weekdays[$todayTehran->dayOfWeek] }} · ساعت تهران (Asia/Tehran)</small>
        </div>
    </header>

    <div class="weekly-calendar-legend" aria-label="راهنمای وضعیت لینک‌ها">
        <span class="weekly-calendar-legend-item"><i class="weekly-calendar-dot"></i> لینک آماده</span>
        <span class="weekly-calendar-legend-item"><i class="weekly-calendar-dot is-needs-link"></i> نیازمند بررسی لینک</span>
        <span class="weekly-calendar-legend-item">زمان‌ها به وقت ایران</span>
    </div>

    <div class="weekly-calendar-grid">
        @foreach($weekdays as $weekday => $dayName)
            @php
                $daySlots = collect($slotsByDay->get($weekday, []))
                    ->sortBy(fn ($row) => (string) $row['schedule']->start_time)
                    ->values();
            @endphp
            <article class="weekly-calendar-day {{ $weekday === $todayTehran->dayOfWeek ? 'is-today' : '' }}" aria-label="برنامهٔ روز {{ $dayName }}">
                <header class="weekly-calendar-day-head">
                    <div class="weekly-calendar-day-heading">
                        <span class="weekly-calendar-day-number">{{ \App\Support\PersianUi::digits($loop->iteration) }}</span>
                        <h3>{{ $dayName }}</h3>
                    </div>
                    <span class="weekly-calendar-count">{{ \App\Support\PersianUi::digits($daySlots->count()) }} جلسه</span>
                </header>

                <div class="weekly-calendar-events">
                    @forelse($daySlots as $entry)
                        @php
                            $schedule = $entry['schedule'];
                            $classroom = $entry['classroom'];
                            $hasLink = filled($schedule->meeting_url);
                            $startTime = substr((string) $schedule->start_time, 0, 5);
                            $endTime = substr((string) $schedule->end_time, 0, 5);
                        @endphp
                        <div class="weekly-calendar-event {{ $hasLink ? '' : 'needs-link' }}">
                            <div class="weekly-calendar-event-time" dir="rtl">
                                <span>{{ \App\Support\PersianUi::digits($startTime) }}</span>
                                <span>تا {{ \App\Support\PersianUi::digits($endTime) }}</span>
                            </div>
                            <strong class="weekly-calendar-event-title">{{ $classroom->title }}</strong>
                            <span class="weekly-calendar-event-course">{{ $classroom->course?->title ?? 'دوره ثبت نشده' }}</span>
                            <div class="weekly-calendar-event-meta">
                                <span class="weekly-calendar-event-provider">{{ $schedule->provider ?: 'سرویس نامشخص' }}</span>
                                @if($schedule->room)
                                    <span class="weekly-calendar-event-provider">{{ $schedule->room }}</span>
                                @endif
                            </div>
                            <span class="weekly-calendar-event-status">{{ $hasLink ? 'لینک آماده' : 'لینک نیازمند بررسی' }}</span>
                            @if($hasLink)
                                <a class="weekly-calendar-event-link" href="{{ $schedule->meeting_url }}" target="_blank" rel="noopener noreferrer">
                                    باز کردن لینک ↗
                                </a>
                            @else
                                <span class="weekly-calendar-event-course">از بخش «افزودن زمان» سرویس و لینک معتبر را ثبت کن.</span>
                            @endif
                        </div>
                    @empty
                        <div class="weekly-calendar-empty">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M7 3v4M17 3v4M3.5 10h17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                            <span>برای این روز، زمانی ثبت نشده است.</span>
                        </div>
                    @endforelse
                </div>
            </article>
        @endforeach
    </div>

    <div class="weekly-calendar-guide" aria-label="راهنمای ثبت برنامه">
        <article class="weekly-calendar-guide-card">
            <span class="weekly-calendar-guide-art" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none"><rect x="5" y="8" width="38" height="34" rx="6" stroke="currentColor" stroke-width="2.5"/><path d="M15 4v9M33 4v9M6 18h36" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><rect x="12" y="24" width="9" height="7" rx="2" fill="#dc354b"/><rect x="26" y="24" width="10" height="7" rx="2" fill="#16865b"/><path d="M14 36h20" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            </span>
            <div class="weekly-calendar-guide-copy">
                <strong>۱. روز و ساعت را مشخص کن</strong>
                <p>کلاس و روز هفته را انتخاب کن، سپس شروع و پایان را به وقت تهران بزن. از روی تقویم می‌توانی تداخل‌ها را سریع ببینی.</p>
            </div>
        </article>
        <article class="weekly-calendar-guide-card">
            <span class="weekly-calendar-guide-art" aria-hidden="true">
                <svg viewBox="0 0 48 48" fill="none"><rect x="4" y="10" width="28" height="27" rx="5" stroke="currentColor" stroke-width="2.5"/><path d="M32 18l12-6v23l-12-6z" fill="#16865b" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 23h12M18 17v12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>
            </span>
            <div class="weekly-calendar-guide-copy">
                <strong>۲. سرویس و لینک ورود را وصل کن</strong>
                <p><b>Jitsi</b> لینک را خودکار می‌سازد. برای <b>Google Meet</b> یا <b>Zoom</b> جلسه را در همان سرویس بساز و لینک واقعی شرکت‌کننده را اینجا وارد کن.</p>
            </div>
        </article>
    </div>
</section>