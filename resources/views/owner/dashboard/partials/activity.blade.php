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
                        <h2 id="login-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-users"/></svg></span>آخرین ورود دانش‌آموزان</h2>
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

        @if(($canViewResources || $canViewMeetings) && $canViewPeople)
            <section class="card c4 owner-learning-activity" aria-labelledby="learning-activity-title" style="--c:#0d9488">
                <div class="card-title">
                    <div>
                        <h2 id="learning-activity-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-book"/></svg></span>رفت‌وآمد آموزشی دانش‌آموزان</h2>
                        <p>باز کردن جزوه‌ها و اقدام برای ورود به کلاس‌های آنلاین.</p>
                    </div>
                    <div class="owner-learning-activity-links">
                        @if($canViewOrders && $firstAcademy)
                            <a class="table-link" href="{{ route('owner.protected-file-views.index', $firstAcademy) }}">گزارش فایل‌ها ←</a>
                        @endif
                        @if($canViewMeetings && $firstAcademy)
                            <a class="table-link" href="{{ route('owner.schedules.index', $firstAcademy) }}">برنامه کلاس‌ها ←</a>
                        @endif
                    </div>
                </div>
                @forelse($recentLearningActivities as $activity)
                    <div class="lr owner-learning-activity-row" style="--c:{{ $activity->accent }}">
                        <i class="tile"><svg class="ic" aria-hidden="true"><use href="#i-{{ $activity->icon }}"/></svg></i>
                        <span class="gx">
                            <b>{{ $activity->student_name }}</b>
                            <small>{{ $activity->detail }}</small>
                            <small class="owner-learning-activity-action">{{ $activity->action_label }}</small>
                        </span>
                        <span class="row-value">{{ \App\Support\PersianUi::dateTime($activity->occurred_at) }}</span>
                    </div>
                @empty
                    <div class="card-empty">هنوز رویداد آموزشی تازه‌ای برای نمایش ثبت نشده است.</div>
                @endforelse
                <p class="owner-learning-activity-note">درخواست ورود به کلاس ثبت می‌شود؛ حضور واقعی فقط با تأیید سرویس کلاس یا ثبت حضور و غیاب مشخص خواهد شد.</p>
            </section>
        @endif

        <section class="card c12 owner-dashboard-shortcuts" aria-labelledby="shortcut-title" style="--c:#5265bd">
            <div class="card-title">
                <div><h2 id="shortcut-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-arr"/></svg></span>دسترسی سریع به کارهای آموزشگاه</h2><p>هر بخش را از اینجا مستقیم باز کن؛ دسترسی‌ها مطابق نقش و مجوز حساب تو نمایش داده می‌شوند.</p></div>
            </div>
            <div class="owner-shortcut-groups">
                @if($canViewCourses || $canViewClassrooms || $canViewAssignments || $canViewExams || $canViewMeetings)
                    <div class="owner-shortcut-group owner-shortcut-group--learning">
                        <h3>آموزش و ارزیابی</h3>
                        <div class="act owner-shortcuts">
                            @if($canViewCourses)<a class="go sm owner-shortcut owner-shortcut--learning" href="{{ route('owner.courses.index') }}">دوره‌ها</a>@endif
                            @if($canViewClassrooms && $firstAcademy)<a class="go sm owner-shortcut owner-shortcut--learning" href="{{ route('owner.classrooms.index', $firstAcademy) }}">کلاس‌ها و گروه‌ها</a>@endif
                            @if($canViewMeetings && $firstAcademy)<a class="go sm owner-shortcut owner-shortcut--learning" href="{{ route('owner.schedules.index', $firstAcademy) }}">برنامه هفتگی</a>@endif
                            @if($canViewAssignments && $firstAcademy)<a class="go sm owner-shortcut owner-shortcut--learning" href="{{ route('owner.assignments.index', $firstAcademy) }}">تکالیف</a>@endif
                            @if($canViewExams && $firstAcademy)<a class="go sm owner-shortcut owner-shortcut--learning" href="{{ route('owner.exams.index', $firstAcademy) }}">آزمون‌ها</a>@endif
                        </div>
                    </div>
                @endif

                @if($canViewParents || $canViewMeetings)
                    <div class="owner-shortcut-group owner-shortcut-group--people">
                        <h3>ارتباط و جلسات</h3>
                        <div class="act owner-shortcuts">
                            @if($firstAcademy && ($canViewParents || $canViewMeetings))<a class="go sm owner-shortcut owner-shortcut--communication" href="{{ route('owner.communication-workspace', $firstAcademy) }}">پیام‌ها و ارتباطات</a>@endif
                            @if($canViewParents && $firstAcademy)<a class="go sm owner-shortcut owner-shortcut--communication" href="{{ route('owner.communications.index', $firstAcademy) }}">پیام اولیا</a>@endif
                            @if($canViewParents && $firstAcademy)<a class="go sm owner-shortcut owner-shortcut--communication" href="{{ route('owner.counseling.index', $firstAcademy) }}">جلسات مشاوره</a>@endif
                            @if($canViewMeetings && $firstAcademy)<a class="go sm owner-shortcut owner-shortcut--communication" href="{{ route('owner.meetings.index', $firstAcademy) }}">جلسات اولیا</a>@endif
                        </div>
                    </div>
                @endif

                @if($canViewResources || $canViewMeetings || $canViewOrders || $ownerUser?->hasPermission('courses.manage'))
                    <div class="owner-shortcut-group owner-shortcut-group--media">
                        <h3>منابع و رسانه</h3>
                        <div class="act owner-shortcuts">
                            @if($canViewResources && $firstAcademy)<a class="go sm owner-shortcut owner-shortcut--media" href="{{ route('owner.resources.index', $firstAcademy) }}">جزوه‌ها و منابع</a>@endif
                            @if($canViewMeetings && $firstAcademy)<a class="go sm owner-shortcut owner-shortcut--media" href="{{ route('owner.recordings.index', $firstAcademy) }}">فیلم کلاس‌ها</a>@endif
                            @if($canViewOrders && $firstAcademy)<a class="go sm owner-shortcut owner-shortcut--media" href="{{ route('owner.protected-file-views.index', $firstAcademy) }}">گزارش مشاهده فایل‌ها</a>@endif
                            @if($ownerUser?->hasPermission('courses.manage'))<a class="go sm owner-shortcut owner-shortcut--media" href="{{ route('owner.spotplayer.edit') }}">اتصال پخش ویدئو</a>@endif
                        </div>
                    </div>
                @endif

                @if($canViewPeople || $canViewAttendance || $canViewReports || $canViewFinance || $canViewTeacherPayments)
                    <div class="owner-shortcut-group owner-shortcut-group--management">
                        <h3>اعضا و مدیریت</h3>
                        <div class="act owner-shortcuts">
                            @if($canViewPeople && $firstAcademy)<a class="go sm owner-shortcut owner-shortcut--management" href="{{ route('owner.people.index', $firstAcademy) }}">دانش‌آموزان و مدرسان</a>@endif
                            @if($canViewAttendance && $firstAcademy)<a class="go sm owner-shortcut owner-shortcut--management" href="{{ route('owner.attendance.index', $firstAcademy) }}">حضور و غیاب</a>@endif
                            @if($canViewReports)<a class="go sm owner-shortcut owner-shortcut--management" href="{{ route('owner.reports.index') }}">گزارش‌های مدیریتی</a>@endif
                            @if($canViewFinance)<a class="go sm owner-shortcut owner-shortcut--management" href="{{ route('owner.finance.index') }}">مالی و پرداخت‌ها</a>@endif
                            @if($canViewTeacherPayments && $firstAcademy)<a class="go sm owner-shortcut owner-shortcut--management" href="{{ route('owner.teacher-payments.index', $firstAcademy) }}">پرداخت مدرس‌ها</a>@endif
                        </div>
                    </div>
                @endif
            </div>
        </section>
