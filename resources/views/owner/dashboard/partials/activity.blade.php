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
                @forelse($recentClassJoinRequests as $event)
                    <div class="lr owner-learning-activity-row" style="--c:#2875b9">
                        <i class="tile"><svg class="ic" aria-hidden="true"><use href="#i-video"/></svg></i>
                        <span class="gx">
                            <b>{{ $event->actor?->name ?? 'دانش‌آموز' }}</b>
                            <small>{{ $event->subject?->title ?? $event->subject?->classroom?->title ?? 'کلاس آنلاین' }}</small>
                            <small class="owner-learning-activity-action">برای ورود به اتاق اقدام کرد · حضور قطعی محسوب نمی‌شود</small>
                        </span>
                        <span class="row-value">{{ \App\Support\PersianUi::dateTime($event->created_at) }}</span>
                    </div>
                @empty
                @endforelse
                @forelse($recentLearningViews as $view)
                    <div class="lr owner-learning-activity-row" style="--c:#0d9488">
                        <i class="tile"><svg class="ic" aria-hidden="true"><use href="#i-file"/></svg></i>
                        <span class="gx">
                            <b>{{ $view->user?->name ?? 'دانش‌آموز' }}</b>
                            <small>{{ $view->resource?->title ?? $view->media?->original_name ?? 'منبع آموزشی' }}</small>
                            <small class="owner-learning-activity-action">جزوه را باز کرد</small>
                        </span>
                        <span class="row-value">{{ \App\Support\PersianUi::dateTime($view->viewed_at) }}</span>
                    </div>
                @empty
                @endforelse
                @if($recentClassJoinRequests->isEmpty() && $recentLearningViews->isEmpty())
                    <div class="card-empty">هنوز رویداد آموزشی تازه‌ای برای نمایش ثبت نشده است.</div>
                @endif
                <p class="owner-learning-activity-note">درخواست ورود به کلاس ثبت می‌شود؛ حضور واقعی فقط با تأیید سرویس کلاس یا ثبت حضور و غیاب مشخص خواهد شد.</p>
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
