@if($canViewReports)
            <section class="card c12" aria-labelledby="teacher-report-title" style="--c:#8b3dff">
                <div class="card-title">
                    <div><h2 id="teacher-report-title"><span class="section-icon"><svg class="ic"><use href="#i-chart"/></svg></span>عملکرد مدرس‌ها</h2><p>حداکثر ۸ مدرس بر اساس تعداد دانش‌آموزان فعال؛ میانگین پیشرفت و بررسی‌های معوق از محاسبات بک‌اند.</p></div>
                    <a class="table-link" href="{{ route('owner.reports.index') }}">گزارش کامل ←</a>
                </div>
                <div class="tsc">
                    <table>
                        <thead><tr><th>مدرس</th><th>دوره</th><th>دانش‌آموز فعال</th><th>پیشرفت میانگین</th><th>بررسی معوق</th>@if($canViewFinance)<th>دریافتی خالص (تومان)</th>@endif</tr></thead>
                        <tbody>
                        @forelse($teacherReports as $teacher)
                            <tr>
                                <td><span class="nm"><i class="av2">{{ mb_substr((string)$teacher->name,0,1) }}</i><span>{{ $teacher->name }}</span></span></td>
                                <td>{{ \App\Support\PersianUi::digits($teacher->course_count) }}</td>
                                <td>{{ \App\Support\PersianUi::digits($teacher->student_count) }}</td>
                                <td>
                                    <span class="row-value">{{ \App\Support\PersianUi::digits(round((float)$teacher->progress_average,1)) }}٪</span>
                                    <div class="trk"><span class="fill" style="--c:#8b3dff;width:{{ min(100,max(0,(float)$teacher->progress_average)) }}%"></span></div>
                                </td>
                                <td>{{ \App\Support\PersianUi::digits($teacher->pending_reviews) }}</td>
                                @if($canViewFinance)<td>{{ \App\Support\PersianUi::number($teacher->sales) }}</td>@endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canViewFinance ? 6 : 5 }}"><div class="card-empty">هنوز داده‌ای برای گزارش مدرس‌ها موجود نیست.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if($canViewMeetings)
            <section class="card c6" aria-labelledby="live-title" style="--c:#ff1f6b">
                <div class="card-title">
                    <div><h2 id="live-title"><span class="section-icon"><svg class="ic"><use href="#i-video"/></svg></span>کلاس‌های آنلاین آینده</h2><p>جلسه‌های زمان‌بندی‌شده در هفت روز آینده؛ مرتب‌شده بر اساس زمان شروع.</p></div>
                    @if($firstAcademy)<a class="table-link" href="{{ route('owner.schedules.index', $firstAcademy) }}">برنامه کامل ←</a>@endif
                </div>
                @forelse($upcomingLiveClasses as $item)
                    <a class="at" href="{{ $item->classroom?->academy_id ? route('owner.schedules.index', ['academy' => $item->classroom->academy_id]) : ($firstAcademy ? route('owner.schedules.index', $firstAcademy) : route('owner.dashboard')) }}" style="--c:{{ $loop->first ? '#ff1f6b' : '#8b3dff' }}">
                        <span class="ct">{{ \App\Support\PersianUi::digits($loop->iteration) }}</span>
                        <span class="gx"><b>{{ $item->title }}</b><small>{{ $item->course?->title ?? 'بدون دوره مرتبط' }} · {{ $item->teacher?->name ?? 'مدرس تعیین نشده' }}</small><small>{{ $item->classroom?->title ?? 'کلاس آنلاین' }}</small></span>
                        <span class="row-value">{{ \App\Support\PersianUi::dateTime($item->scheduled_at) }}</span>
                    </a>
                @empty
                    <div class="card-empty">در هفت روز آینده جلسه آنلاین زمان‌بندی‌شده‌ای ثبت نشده است.</div>
                @endforelse
            </section>
        @endif

        @if($canViewCourses)
            <section class="card c6" aria-labelledby="recent-courses-title" style="--c:#22d3ee">
                <div class="card-title">
                    <div><h2 id="recent-courses-title"><span class="section-icon"><svg class="ic"><use href="#i-book"/></svg></span>آخرین دوره‌ها</h2><p>شش دوره‌ای که به‌تازگی ثبت شده‌اند.</p></div>
                    <a class="table-link" href="{{ route('owner.courses.index') }}">همه دوره‌ها ←</a>
                </div>
                @forelse($recentCourses as $course)
                    <div class="at" style="--c:{{ $course->status === 'published' ? '#2ee6a6' : '#ff9a1f' }}">
                        <span class="tile"><svg class="ic"><use href="#i-book"/></svg></span>
                        <span class="gx"><b>{{ $course->title }}</b><small>{{ $course->academy?->name ?? 'بدون آموزشگاه' }} · {{ \App\Support\PersianUi::digits($course->active_students_count) }} دانش‌آموز فعال</small></span>
                        <span class="pl" style="--c:{{ $course->status === 'published' ? '#2ee6a6' : '#ff9a1f' }}">{{ $course->status === 'published' ? 'منتشرشده' : ($course->status === 'draft' ? 'پیش‌نویس' : $course->status) }}</span>
                        <a class="table-link" href="{{ route('owner.courses.show', $course) }}">بازبینی</a>
                    </div>
                @empty
                    <div class="card-empty">دوره‌ای ثبت نشده است.</div>
                @endforelse
            </section>
        @endif

                @if($canViewLegal)
            <section class="card c6 owner-content-rights-card" aria-labelledby="content-rights-title" style="--c:#9aa3d8">
                <div class="card-title">
                    <div>
                        <h2 id="content-rights-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-scale"/></svg></span>حقوق نشر و مجوز محتوا</h2>
                        <p>پرونده مالکیت و مجوز استفاده برای هر ویدئو، ضبط کلاس، جزوه و کتاب را با مدرک و تاریخ اعتبار ثبت و پیگیری کنید.</p>
                    </div>
                    <a class="table-link" href="{{ route('owner.content-rights.index') }}">مدیریت مجوزها ←</a>
                </div>
                <p class="text-sm leading-7">مجوزها ابتدا به‌صورت پیش‌نویس ثبت می‌شوند؛ پس از بررسی مدرک می‌توانید آن‌ها را فعال یا لغو کنید. تاریخچه نسخه‌های قبلی حفظ می‌شود.</p>
            </section>
        @endif

@if(!$canViewPeople && !$canViewClassrooms && !$canViewCourses && !$canViewAttendance && !$canViewFinance && !$canViewReports && !$canViewMeetings && !$canViewLegal)
            <section class="card c12"><div class="card-empty">برای نمایش شاخص‌ها، دسترسی مشاهده به بخش‌های مجاز این کارتابل برای این حساب فعال نیست.</div></section>
        @endif
