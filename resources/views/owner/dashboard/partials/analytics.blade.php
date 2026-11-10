<section class="card c8 owner-trend-card" aria-labelledby="weekly-trend-title" style="--c:#ff1f6b">
            <div class="card-title">
                <div>
                    <h2 id="weekly-trend-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-chart"/></svg></span>روند حضور در ۱۳ هفته اخیر</h2>
                    <p>روند هفتگی بر اساس حضورهای ثبت‌شده؛ هفته‌های بدون داده نمایش داده نمی‌شوند.</p>
                </div>
                @if($canViewReports)<a class="table-link" href="{{ route('owner.reports.index') }}">گزارش کامل ←</a>@endif
            </div>
            @if(count(array_filter(is_array($weeklyAttendance) ? $weeklyAttendance : [], 'is_numeric')) >= 2)
                <div class="owner-chart-legend" aria-label="راهنمای نمودار">
                    <span><i style="--legend:#22d3ee"></i>میانگین نرخ حضور</span>
                </div>
                <x-owner.dashboard.line-chart
                    :segments="$weeklyAttendanceSegments"
                    :labels="$weeklyLabels"
                    series-class="owner-line-attendance"
                    aria-label="روند نرخ حضور هفتگی در ۱۳ هفته اخیر"
                    :show-vertical-grid="true" />
            @else
                <div class="card-empty">هنوز داده کافی برای نمایش روند حضور در ۱۳ هفته اخیر ثبت نشده است.</div>
            @endif
        </section>

        @if($canViewCourses || $canViewClassrooms || $canViewAttendance)
            <section class="card c4" style="--c:#8b3dff" aria-labelledby="course-publish-title">
                <div class="card-title">
                    <div>
                        <h2 id="course-publish-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-chart"/></svg></span>وضعیت کلی آموزشگاه</h2>
                        <p>شاخص‌های واقعی انتشار دوره، ظرفیت و حضور.</p>
                    </div>
                </div>
                @if($canViewCourses)
                    @if($courseCount > 0)
                        <div class="dn">
                        <svg viewBox="0 0 120 120" role="img" aria-label="سهم دوره‌های منتشرشده">
                            <circle cx="60" cy="60" r="45" fill="none" stroke="#0a0e27" stroke-width="13"/>
                            @if($publishedRate !== null)
                                <circle cx="60" cy="60" r="45" fill="none" stroke="#8b3dff" stroke-width="13" stroke-linecap="round" stroke-dasharray="{{ 2 * pi() * 45 * $publishedRate / 100 }} {{ 2 * pi() * 45 * (1 - $publishedRate / 100) }}" transform="rotate(-90 60 60)"/>
                            @endif
                            <text x="60" y="57" text-anchor="middle" fill="#e8ecff" font-size="18" font-weight="800">{{ $publishedRate === null ? '—' : \App\Support\PersianUi::digits($publishedRate) . '٪' }}</text>
                            <text x="60" y="75" text-anchor="middle" fill="#8d96c9" font-size="9">انتشار</text>
                        </svg>
                        <div class="gx">
                            <b>{{ \App\Support\PersianUi::digits($publishedCourseCount) }} دوره منتشرشده</b>
                            <small>از {{ \App\Support\PersianUi::digits($courseCount) }} دوره</small>
                            <div class="bl">منتشرنشده: {{ \App\Support\PersianUi::digits(max(0, $courseCount - $publishedCourseCount)) }}</div>
                        </div>
                        </div>
                    @else
                        <div class="card-empty">هنوز دوره‌ای ثبت نشده است.</div>
                    @endif
                @endif
                @if($canViewClassrooms)
                    @if($capacityRate !== null)
                        <div class="metric-bars">
                        <div>
                            <div class="bar-label"><span id="capacity-title">ظرفیت پرشدهٔ کلاس‌ها</span><strong>{{ $capacityRate === null ? '—' : \App\Support\PersianUi::digits($capacityRate) . '٪' }}</strong></div>
                            <div class="trk"><span class="fill" style="--c:#22d3ee;width:{{ $capacityRate ?? 0 }}%"></span></div>
                        </div>
                        </div>
                    @else
                        <div class="card-empty">هنوز ظرفیت کلاس‌ها مشخص نشده است.</div>
                    @endif
                @endif
                @if($canViewAttendance)
                    @if($attendanceRate !== null)
                        <div class="metric-bars">
                        <div>
                            <div class="bar-label"><span>نرخ حضور در بازه انتخابی</span><strong>{{ $attendanceRate === null ? '—' : \App\Support\PersianUi::digits($attendanceRate) . '٪' }}</strong></div>
                            <div class="trk"><span class="fill" style="--c:#2ee6a6;width:{{ $attendanceRate === null ? 0 : min(100, max(0, (float) $attendanceRate)) }}%"></span></div>
                        </div>
                        </div>
                    @else
                        <div class="card-empty">هنوز حضور و غیابی برای این بازه ثبت نشده است.</div>
                    @endif
                @endif
                @if($canViewClassrooms && $capacityTotal === 0)
                    <div class="card-empty">برای محاسبه ظرفیت، ابتدا ظرفیت یک کلاس فعال را در سامانه ثبت کن.</div>
                @endif
                @if($canViewCourses && $courseCount === 0)
                    <div class="card-empty">هنوز دوره‌ای ثبت نشده است.</div>
                @endif
                <div class="metric-foot">
                    @if($firstAcademy && $canViewClassrooms)<a class="table-link" href="{{ route('owner.classrooms.index', $firstAcademy) }}">کلاس‌ها ←</a>@endif
                    @if($canViewCourses)<a class="table-link" href="{{ route('owner.courses.index') }}">دوره‌ها ←</a>@endif
                </div>
            </section>
        @endif

        @if($canViewExams)
            <section class="card c4 owner-subject-averages" aria-labelledby="subject-averages-title" style="--c:#8b3dff">
                <div class="card-title">
                    <div>
                        <h2 id="subject-averages-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-book"/></svg></span>میانگین نمرهٔ دوره‌ها</h2>
                        <p>میانگین آخرین تلاش‌های نمره‌دار در دوره‌های دارای داده.</p>
                    </div>
                    <a class="table-link" href="{{ route('owner.exams.index', $firstAcademy) }}">آزمون‌ها ←</a>
                </div>
                @php
                    $subjectMax = max(1, (float) ($subjectAverages->max('average') ?? 1));
                @endphp
                @if($subjectAverages->isNotEmpty())
                    <div class="cys" role="img" aria-label="مقایسه میانگین نمره دوره‌ها">
                        @foreach($subjectAverages as $subject)
                            <div class="cyc">
                                <b>{{ \App\Support\PersianUi::number($subject['average']) }}</b>
                                <div class="cy" style="--h:{{ max(8, (int) round(abs((float) $subject['average']) / $subjectMax * 138)) }}px;--c:{{ $loop->even ? '#22d3ee' : '#8b3dff' }}"></div>
                                <small>{{ \Illuminate\Support\Str::limit($subject['title'], 18) }}</small>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="card-empty">هنوز برای دوره‌های این آموزشگاه نمرهٔ معتبر ثبت نشده است.</div>
                @endif
            </section>
        @endif
