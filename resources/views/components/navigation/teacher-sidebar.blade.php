<aside class="role-sidebar teacher-sidebar">
    <div class="role-sidebar-brand">
        <a href="{{ route('teacher.dashboard') }}" class="role-brand" title="داشبورد شیخان" aria-label="داشبورد شیخان">
            <span class="role-brand-mark" aria-hidden="true">ش</span>
            <span><strong>شیخان</strong><small>پنل مدرس</small></span>
        </a>
    </div>

    <nav class="role-sidebar-nav" aria-label="منوی مدرس">
        <div class="role-sidebar-label">آموزش</div>

        <a href="{{ route('teacher.dashboard') }}" title="داشبورد" aria-label="داشبورد" class="role-nav-link {{ request()->routeIs('teacher.dashboard') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.dashboard')) aria-current="page" @endif>
            <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></span><span>داشبورد</span>
        </a>
        <a href="{{ route('teacher.courses.index') }}" title="دوره‌های من" aria-label="دوره‌های من" class="role-nav-link {{ request()->routeIs('teacher.courses.*') || request()->routeIs('teacher.lessons.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.courses.*') || request()->routeIs('teacher.lessons.*')) aria-current="page" @endif>
            <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v17H6.5A2.5 2.5 0 0 0 4 22z"/><path d="M4 5.5V22M8 7h8M8 11h8"/></svg></span><span>دوره‌های من</span>
        </a>
        <a href="{{ route('teacher.classrooms.index') }}" title="کلاس‌های من" aria-label="کلاس‌های من" class="role-nav-link {{ request()->routeIs('teacher.classrooms.*') && !request()->routeIs('teacher.classrooms.attendance.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.classrooms.*') && !request()->routeIs('teacher.classrooms.attendance.*')) aria-current="page" @endif>
            <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M16 5a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 5v1"/></svg></span><span>کلاس‌های من</span>
        </a>
        <a href="{{ route('teacher.schedule.index') }}" title="برنامه هفتگی" aria-label="برنامه هفتگی" class="role-nav-link {{ request()->routeIs('teacher.schedule.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.schedule.*')) aria-current="page" @endif>
            <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4m8-4v4M3 10h18M8 14h3M8 17h3"/></svg></span><span>برنامه هفتگی</span>
        </a>
        <a href="{{ route('teacher.live-classes.index') }}" title="کلاس‌های آنلاین" aria-label="کلاس‌های آنلاین" class="role-nav-link {{ request()->routeIs('teacher.live-classes.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.live-classes.*')) aria-current="page" @endif>
            <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="12" height="12" rx="2"/><path d="m15 10 6-3v10l-6-3z"/></svg></span><span>کلاس‌های آنلاین</span>
        </a>

        <div class="role-sidebar-label mt-6">ارزیابی</div>

        <a href="{{ route('teacher.assignments.index') }}" title="تکالیف" aria-label="تکالیف" class="role-nav-link {{ request()->routeIs('teacher.assignments.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.assignments.*')) aria-current="page" @endif>
            <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 4h8l3 3v13H5V4z"/><path d="M8 4v4h8V4M8 13l2 2 4-4"/></svg></span><span>تکالیف</span>
        </a>
        <a href="{{ route('teacher.exams.index') }}" title="آزمون‌ها" aria-label="آزمون‌ها" class="role-nav-link {{ request()->routeIs('teacher.exams.*') || request()->routeIs('teacher.exam-attempts.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.exams.*') || request()->routeIs('teacher.exam-attempts.*')) aria-current="page" @endif>
            <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 3h10a2 2 0 0 1 2 2v16H5V5a2 2 0 0 1 2-2Z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></span><span>آزمون‌ها</span>
        </a>

        <div class="role-sidebar-label mt-6">پیگیری</div>

        <a href="{{ route('teacher.attendance.index') }}" title="حضور و غیاب" aria-label="حضور و غیاب" class="role-nav-link {{ request()->routeIs('teacher.attendance.*') || request()->routeIs('teacher.classrooms.attendance.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.attendance.*') || request()->routeIs('teacher.classrooms.attendance.*')) aria-current="page" @endif>
            <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4m8-4v4M3 10h18M8 15l2 2 4-4"/></svg></span><span>حضور و غیاب</span>
        </a>
        @if(auth()->user()?->hasPermission('parent_messages.view'))
            <a href="{{ route('teacher.messages.index') }}" title="پیام‌های اولیا" aria-label="پیام‌های اولیا" class="role-nav-link {{ request()->routeIs('teacher.messages.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.messages.*')) aria-current="page" @endif>
                <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v12H9l-5 4z"/><path d="M8 9h8M8 13h5"/></svg></span><span>پیام‌های اولیا</span>
            </a>
        @endif
        @if(auth()->user()?->hasPermission('student_messages.view'))
            <a href="{{ route('teacher.student-messages.index') }}" title="پیام‌های دانش‌آموزان" aria-label="پیام‌های دانش‌آموزان" class="role-nav-link {{ request()->routeIs('teacher.student-messages.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.student-messages.*')) aria-current="page" @endif>
                <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h12v10H8l-4 4z"/><path d="M8 9h5"/><path d="M16 8h4v11h-7"/></svg></span><span>پیام‌های دانش‌آموزان</span>
            </a>
        @endif
        @if(auth()->user()?->hasPermission('parent_messages.manage'))
            <a href="{{ route('teacher.direct-messages.index') }}" title="پیام مستقیم به خانواده و دانش‌آموز" aria-label="پیام مستقیم به خانواده و دانش‌آموز" class="role-nav-link {{ request()->routeIs('teacher.direct-messages.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.direct-messages.*')) aria-current="page" @endif>
                <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m21 3-7.5 18-3.5-7-7-3.5z"/><path d="M10 14 21 3"/></svg></span><span>پیام مستقیم به خانواده و دانش‌آموز</span>
            </a>
        @endif
        @if(auth()->user()?->hasPermission('parent_messages.manage'))
            <a href="{{ route('teacher.communications.index') }}" title="اطلاعیه برای خانواده‌های کلاس" aria-label="اطلاعیه برای خانواده‌های کلاس" class="role-nav-link {{ request()->routeIs('teacher.communications.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.communications.*')) aria-current="page" @endif>
                <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 10v4h4l10 5V5L7 10z"/><path d="M7 14l2 6h4l-3-4M21 9a4 4 0 0 1 0 6"/></svg></span><span>اطلاعیه برای خانواده‌های کلاس</span>
            </a>
        @endif
        <a href="{{ route('teacher.meetings.index') }}" title="جلسات با اولیا" aria-label="جلسات با اولیا" class="role-nav-link {{ request()->routeIs('teacher.meetings.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.meetings.*')) aria-current="page" @endif>
            <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4m8-4v4M3 10h18"/><circle cx="12" cy="15" r="2.5"/><path d="M12 13.5v1.5l1 1"/></svg></span><span>جلسات با اولیا</span>
        </a>
        <a href="{{ route('teacher.students.index') }}" title="دانش‌آموزان" aria-label="دانش‌آموزان" class="role-nav-link {{ request()->routeIs('teacher.students.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.students.*')) aria-current="page" @endif>
            <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20v-1a6 6 0 0 1 12 0v1M16 15a5 5 0 0 1 5 5"/></svg></span><span>دانش‌آموزان</span>
        </a>
        <a href="{{ route('teacher.dashboard') }}#td-progress-title" title="گزارش عملکرد" aria-label="گزارش عملکرد" class="role-nav-link">
            <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 21h18M6 17v-5M12 17V5M18 17v-9"/></svg></span><span>گزارش عملکرد</span>
        </a>

        <div class="role-sidebar-label mt-6">حساب</div>

        <a href="{{ route('teacher.payments.index') }}" title="حقوق و پرداخت‌ها" aria-label="حقوق و پرداخت‌ها" class="role-nav-link {{ request()->routeIs('teacher.payments.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.payments.*')) aria-current="page" @endif>
            <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 9h18M16 14h2"/></svg></span><span>حقوق و پرداخت‌ها</span>
        </a>
        <a href="{{ route('teacher.profile.edit') }}" title="پروفایل من" aria-label="پروفایل من" class="role-nav-link {{ request()->routeIs('teacher.profile.*') ? 'is-active' : '' }}" @if(request()->routeIs('teacher.profile.*')) aria-current="page" @endif>
            <span class="role-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg></span><span>پروفایل من</span>
        </a>
    </nav>

    <div class="role-sidebar-footer">
        <a href="{{ route('home') }}" class="role-footer-link" title="مشاهده سایت" aria-label="مشاهده سایت">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9"/><path d="M18 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6"/></svg><span class="role-footer-copy">مشاهده سایت</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="role-footer-link role-logout" title="خروج از حساب" aria-label="خروج از حساب">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5"/><path d="M14 8l4 4-4 4M18 12H8"/></svg><span class="role-footer-copy">خروج</span>
            </button>
        </form>
    </div>
</aside>