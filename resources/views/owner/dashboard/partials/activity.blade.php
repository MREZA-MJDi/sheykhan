@if($canViewAssignments)
            <section class="card c4 owner-quick-approvals" aria-labelledby="quick-approval-title" style="--c:#ff9a1f">
                <div class="card-title">
                    <div>
                        <h2 id="quick-approval-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-bell"/></svg></span>تأیید سریع</h2>
                        <p>{{ \App\Support\PersianUi::digits($pendingReviews) }} پاسخ نیازمند بررسی مدرس.</p>
                    </div>
                    <a class="table-link" href="{{ route('owner.assignments.index', $firstAcademy) }}">همه تکالیف ←</a>
                </div>
                @forelse($pendingApprovalItems as $item)
                    <a class="ckr" href="{{ route('owner.assignments.show', [$item->academy_id, $item->assignment_id]) }}" style="--c:#ff9a1f">
                        <i class="tile"><svg class="ic" aria-hidden="true"><use href="#i-doc"/></svg></i>
                        <span class="gx">
                            <b>{{ $item->student_name }}</b>
                            <small>{{ $item->course_title }} · {{ $item->assignment_title }}</small>
                        </span>
                        <small>{{ \App\Support\PersianUi::dateTime(\Illuminate\Support\Carbon::parse($item->submitted_at)) }}</small>
                    </a>
                @empty
                    <div class="card-empty">پاسخ بررسی‌نشده‌ای برای نمایش وجود ندارد.</div>
                @endforelse
            </section>
        @endif

        @if($canViewPeople)
            <section class="card c4" aria-labelledby="login-title" style="--c:#22d3ee">
                <div class="card-title">
                    <div>
                        <h2 id="login-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-users"/></svg></span>آخرین رویدادها</h2>
                        <p>سه ورود اخیر دانش‌آموزان فعال در آموزشگاه‌های شما.</p>
                    </div>
                    @if($firstAcademy)<a class="table-link" href="{{ route('owner.people.index', $firstAcademy) }}">اعضا ←</a>@endif
                </div>
                @forelse($recentStudentLogins as $login)
                    <a class="lr" href="{{ $firstAcademy ? route('owner.people.index', $firstAcademy) : route('owner.dashboard') }}" style="--c:#22d3ee">
                        <i class="tile"><svg class="ic" aria-hidden="true"><use href="#i-users"/></svg></i>
                        <span class="gx"><b>{{ $login->subject?->name ?? 'حساب حذف‌شده' }}</b><small>{{ $login->subject?->email ?? 'بدون ایمیل' }}</small></span>
                        <span class="row-value">{{ \App\Support\PersianUi::dateTime($login->created_at) }}</span>
                        <svg class="ic" aria-hidden="true"><use href="#i-arr"/></svg>
                    </a>
                @empty
                    <div class="card-empty">رویداد ورود دانش‌آموزان در این بازه ثبت نشده است.</div>
                @endforelse
            </section>
        @endif

        @if($canViewPeople)
            <section class="card c8 owner-growth-card" aria-labelledby="student-growth-title" style="--c:#ff1f6b">
                <div class="card-title">
                    <div>
                        <h2 id="student-growth-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-chart"/></svg></span>رشد ثبت‌نام دانش‌آموزان در ۱۳ هفته</h2>
                        <p>تعداد عضویت‌های فعال ایجادشده در هر هفته.</p>
                    </div>
                    @if($canViewReports)<a class="table-link" href="{{ route('owner.reports.index') }}">گزارش کامل ←</a>@endif
                </div>
                @if(count(array_filter(is_array($weeklyStudents) ? $weeklyStudents : [], 'is_numeric')) >= 2 && array_sum(array_map('floatval', is_array($weeklyStudents) ? $weeklyStudents : [])) > 0)
                    <x-owner.dashboard.line-chart
                        :segments="$weeklyStudentSegments"
                        :labels="$weeklyLabels"
                        series-class="owner-line-students"
                        chart-class="owner-growth-chart"
                        aria-label="روند هفتگی ثبت‌نام دانش‌آموزان"
                        :show-vertical-grid="false" />
                @else
                    <div class="card-empty">هنوز داده کافی برای نمایش روند ثبت‌نام در ۱۳ هفته اخیر وجود ندارد.</div>
                @endif
            </section>
        @endif

        <section class="card c4 owner-dashboard-shortcuts" aria-labelledby="shortcut-title" style="--c:#22d3ee">
            <div class="card-title">
                <div><h2 id="shortcut-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-arr"/></svg></span>میان‌بر</h2><p>دسترسی مستقیم به کارهای روزمره.</p></div>
            </div>
            <div class="act owner-shortcuts">
                @if($canViewAttendance && $firstAcademy)<a class="go sm" href="{{ route('owner.attendance.index', $firstAcademy) }}">ثبت حضور</a>@endif
                @if($canViewClassrooms && $firstAcademy)<a class="go sm" href="{{ route('owner.classrooms.index', $firstAcademy) }}">کلاس‌ها</a>@endif
                @if($canViewPeople && $firstAcademy)<a class="go sm" href="{{ route('owner.people.index', $firstAcademy) }}">مدیریت اعضا</a>@endif
                @if($canViewCourses)<a class="go sm" href="{{ route('owner.courses.index') }}">دوره‌ها</a>@endif
                @if($ownerUser?->hasPermission('courses.manage'))<a class="go sm" href="{{ route('owner.spotplayer.edit') }}">اتصال SpotPlayer</a>@endif
                @if($canViewOrders && $firstAcademy)<a class="go sm" href="{{ route('owner.protected-file-views.index', $firstAcademy) }}">گزارش مشاهده فایل‌ها</a>@endif
                @if($canViewAssignments && $firstAcademy)<a class="go sm" href="{{ route('owner.assignments.index', $firstAcademy) }}">تکالیف</a>@endif
                @if($canViewReports)<a class="go sm" href="{{ route('owner.reports.index') }}">گزارش‌ها</a>@endif
            </div>
            <small>برای پیدا کردن سایر صفحه‌ها از جستجوی منوی بالای صفحه استفاده کن.</small>
        </section>
