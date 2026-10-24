@props(['role'])

@php
    $user = auth()->user();
    $seoOnly = $role === 'owner'
        && ($user?->hasRole('seo-specialist') ?? false)
        && ! ($user?->hasRole('academy-owner') ?? false);

    $items = match ($role) {
        'student' => [
            ['route' => 'student.dashboard', 'label' => 'خانه', 'permission' => 'dashboard.view', 'icon' => '⌂', 'primary' => true],
            ['route' => 'student.courses.index', 'label' => 'دوره‌های من', 'permission' => 'courses.view', 'icon' => '▣', 'primary' => true],
            ['route' => 'student.assignments.index', 'label' => 'تکالیف', 'permission' => 'assignments.view', 'icon' => '✓', 'primary' => true],
            ['route' => 'student.live-classes.index', 'label' => 'کلاس‌ها', 'permission' => 'live_classes.view', 'icon' => '◉', 'primary' => false],
            ['route' => 'student.exams.index', 'label' => 'آزمون‌ها', 'permission' => 'exams.view', 'icon' => '▤', 'primary' => false],
            ['route' => 'student.resources.index', 'label' => 'جزوه‌ها و منابع', 'permission' => 'resources.view', 'icon' => '▤', 'primary' => false],
            ['route' => 'student.messages.index', 'label' => 'پیام به مدرس‌ها', 'permission' => 'student_messages.view', 'icon' => '✉', 'primary' => true],
            ['route' => 'student.results.index', 'label' => 'نمرات و عملکرد', 'permission' => 'results.view', 'icon' => '↗', 'primary' => false],
            ['route' => 'student.attendance.index', 'label' => 'حضور و غیاب', 'permission' => 'attendance.view', 'icon' => '✓', 'primary' => false],
            ['route' => 'student.achievements.index', 'label' => 'دستاوردها', 'permission' => 'achievements.view', 'icon' => '◎', 'primary' => false],
            ['route' => 'student.notes.index', 'label' => 'یادداشت‌ها', 'permission' => 'notes.view', 'icon' => '▤', 'primary' => false],
            ['route' => 'student.profile.edit', 'label' => 'پروفایل و حساب', 'permission' => 'profile.view', 'icon' => '⚙', 'primary' => false],
        ],
        'teacher' => [
            ['route' => 'teacher.dashboard', 'label' => 'خانه', 'permission' => 'dashboard.view', 'icon' => '⌂', 'primary' => true],
            ['route' => 'teacher.classrooms.index', 'label' => 'کلاس‌ها', 'permission' => 'classrooms.view', 'icon' => '▦', 'primary' => true],
            ['route' => 'teacher.assignments.index', 'label' => 'تکالیف', 'permission' => 'assignments.view', 'icon' => '✓', 'primary' => true],
            ['route' => 'teacher.messages.index', 'label' => 'پیام‌های اولیا', 'permission' => 'parent_messages.view', 'icon' => '✉', 'primary' => true],
            ['route' => 'teacher.courses.index', 'label' => 'دوره‌های من', 'permission' => 'courses.view', 'icon' => '▣', 'primary' => false],
            ['route' => 'teacher.schedule.index', 'label' => 'برنامه هفتگی', 'permission' => 'live_classes.view', 'icon' => '◷', 'primary' => false],
            ['route' => 'teacher.live-classes.index', 'label' => 'کلاس‌های آنلاین', 'permission' => 'live_classes.view', 'icon' => '◉', 'primary' => false],
            ['route' => 'teacher.exams.index', 'label' => 'آزمون‌ها', 'permission' => 'exams.view', 'icon' => '▤', 'primary' => false],
            ['route' => 'teacher.attendance.index', 'label' => 'حضور و غیاب', 'permission' => 'attendance.view', 'icon' => '✓', 'primary' => false],
            ['route' => 'teacher.student-messages.index', 'label' => 'پیام‌های دانش‌آموزان', 'permission' => 'student_messages.view', 'icon' => '✉', 'primary' => false],
            ['route' => 'teacher.direct-messages.index', 'label' => 'پیام مستقیم', 'permission' => 'parent_messages.manage', 'icon' => '✉', 'primary' => false],
            ['route' => 'teacher.communications.index', 'label' => 'اطلاعیه‌های کلاس', 'permission' => 'parent_messages.manage', 'icon' => '✉', 'primary' => false],
            ['route' => 'teacher.meetings.index', 'label' => 'جلسات با اولیا', 'permission' => 'live_classes.view', 'icon' => '◷', 'primary' => false],
            ['route' => 'teacher.students.index', 'label' => 'دانش‌آموزان', 'permission' => 'students.view', 'icon' => '◎', 'primary' => false],
            ['route' => 'teacher.payments.index', 'label' => 'حقوق و پرداخت‌ها', 'permission' => 'teacher_payments.view', 'icon' => '▤', 'primary' => false],
            ['route' => 'teacher.profile.edit', 'label' => 'پروفایل من', 'permission' => 'profile.view', 'icon' => '⚙', 'primary' => false],
            ['route' => 'teacher.dashboard', 'url' => route('teacher.dashboard') . '#teacher-progress', 'activeRoute' => 'teacher.dashboard', 'label' => 'گزارش عملکرد', 'permission' => 'reports.view', 'icon' => '↗', 'primary' => false, 'noActive' => true],
        ],
        'owner' => [
            ['route' => 'owner.dashboard', 'label' => 'مرکز کنترل', 'permission' => 'dashboard.view', 'icon' => '⌂', 'primary' => true],
            ['route' => 'owner.people.index', 'label' => 'اعضا', 'permission' => 'academy.view', 'icon' => '◎', 'primary' => true, 'requiresAcademy' => true],
            ['route' => 'owner.classrooms.index', 'label' => 'کلاس‌ها', 'permission' => 'classrooms.view', 'icon' => '▦', 'primary' => true, 'requiresAcademy' => true],
            ['route' => 'owner.messages.index', 'label' => 'پیام‌ها', 'permission' => 'parents.view', 'icon' => '✉', 'primary' => true, 'requiresAcademy' => true],
            ['route' => 'owner.attendance.index', 'label' => 'حضور و غیاب', 'permission' => 'attendance.view', 'icon' => '✓', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.assignments.index', 'label' => 'تکالیف و تصحیح', 'permission' => 'courses.view', 'icon' => '✓', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.schedules.index', 'label' => 'لینک کلاس آنلاین', 'permission' => 'live_classes.view', 'icon' => '◷', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.meetings.index', 'label' => 'هماهنگی جلسات', 'permission' => 'live_classes.view', 'icon' => '◷', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.communications.index', 'label' => 'ارسال اطلاعیه و پیام', 'permission' => 'parents.view', 'icon' => '✉', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.counseling.index', 'label' => 'جلسات مشاوره', 'permission' => 'parents.view', 'icon' => '◷', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.exams.index', 'label' => 'آزمون‌ها و نتایج', 'permission' => 'exams.view', 'icon' => '▤', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.recordings.index', 'label' => 'آرشیو ضبط کلاس‌ها', 'permission' => 'live_classes.view', 'icon' => '◉', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.teacher-payments.index', 'label' => 'حقوق مدرس‌ها', 'permission' => 'teacher_payments.view', 'icon' => '▤', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.resources.index', 'label' => 'ویدئو و منابع آموزشی', 'permission' => 'content.manage', 'icon' => '▤', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.showcase.index', 'label' => 'افتخارآفرینان و رضایتمندی', 'permission' => 'content.manage', 'icon' => '◎', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.products.index', 'label' => 'محصولات و منابع فروش', 'permission' => 'products.view', 'icon' => '▣', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.orders.index', 'label' => 'سفارش‌ها و رسیدها', 'permission' => 'orders.view', 'icon' => '▤', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.courses.index', 'label' => 'دوره‌ها', 'permission' => 'courses.view', 'icon' => '▣', 'primary' => false],
            ['route' => 'owner.website.index', 'label' => 'مرکز سایت', 'permission' => 'academy.view', 'icon' => '⌂', 'primary' => false],
            ['route' => 'owner.content.index', 'label' => 'محتوای آموزشگاه', 'permission' => 'content.manage', 'icon' => '▤', 'primary' => false],
            ['route' => 'owner.blog.index', 'label' => 'مقالات', 'permission' => 'blog.manage', 'icon' => '▤', 'primary' => false],
            ['route' => 'owner.academy.banners.edit', 'label' => 'بنرهای سایت', 'permission' => 'academy.view', 'icon' => '▣', 'primary' => false, 'requiresAcademy' => true],
            ['route' => 'owner.finance.index', 'label' => 'مالی و پرداخت‌ها', 'permission' => 'finance.view', 'icon' => '▤', 'primary' => false],
            ['route' => 'owner.legal-documents.index', 'label' => 'اسناد حقوقی و کپی‌رایت', 'permission' => 'legal.manage', 'icon' => '▤', 'primary' => false],
            ['route' => 'owner.reports.index', 'label' => 'عملکرد و گزارش', 'permission' => 'reports.view', 'icon' => '↗', 'primary' => false],
            ['route' => 'owner.academy.edit', 'label' => 'تنظیمات آموزشگاه', 'permission' => 'academy.view', 'icon' => '⚙', 'primary' => false, 'requiresAcademy' => true],
        ],
        'parent' => [
            ['route' => 'parent.dashboard', 'label' => 'خانه', 'permission' => 'dashboard.view', 'icon' => '⌂', 'primary' => true],
            ['route' => 'parent.attendance.index', 'label' => 'حضور و غیاب', 'permission' => 'dashboard.view', 'icon' => '✓', 'primary' => true],
            ['route' => 'parent.results.index', 'label' => 'نتایج', 'permission' => 'dashboard.view', 'icon' => '↗', 'primary' => true],
            ['route' => 'parent.messages.index', 'label' => 'پیام‌ها', 'permission' => 'dashboard.view', 'icon' => '✉', 'primary' => true],
            ['route' => 'parent.resources.index', 'label' => 'جزوه‌ها و منابع', 'permission' => 'dashboard.view', 'icon' => '▤', 'primary' => false],
            ['route' => 'parent.counseling.index', 'label' => 'درخواست مشاوره', 'permission' => 'dashboard.view', 'icon' => '◷', 'primary' => false],
            ['route' => 'library.index', 'label' => 'کتابخانه و خریدها', 'icon' => '▣', 'primary' => false],
            ['route' => 'courses.index', 'label' => 'کشف دوره‌ها', 'icon' => '▣', 'primary' => false],
        ],
        default => [],
    };

    if ($seoOnly) {
        $items = [
            ['route' => 'owner.seo.index', 'label' => 'ممیزی SEO صفحات', 'permission' => 'seo.manage', 'icon' => '⌕', 'primary' => true],
            ['route' => 'owner.seo.keywords.index', 'label' => 'کلمات کلیدی و رتبه', 'permission' => 'seo.manage', 'icon' => '▤', 'primary' => true],

        ];
    }

    $academy = $role === 'owner' && ! $seoOnly
        ? $user?->ownedAcademies()->where('status', 'active')->orderBy('name')->first()
        : null;

    $academyRoutes = [
        'owner.people.index', 'owner.classrooms.index', 'owner.attendance.index',
        'owner.assignments.index', 'owner.schedules.index', 'owner.meetings.index',
        'owner.messages.index', 'owner.communications.index', 'owner.counseling.index',
        'owner.exams.index', 'owner.recordings.index', 'owner.teacher-payments.index',
        'owner.resources.index', 'owner.showcase.index', 'owner.products.index', 'owner.orders.index',
        'owner.academy.banners.edit', 'owner.academy.edit',
    ];

    $visibleItems = collect($items)
        ->filter(fn (array $item) => !$user || (!isset($item['permission']) && !isset($item['permissions'])) || collect($item['permissions'] ?? [$item['permission'] ?? null])->filter()->contains(fn (string $permission) => $user->hasPermission($permission)))
        ->filter(fn (array $item) => empty($item['requiresAcademy']) || $academy)
        ->filter(fn (array $item) => \Illuminate\Support\Facades\Route::has($item['route']))
        ->map(function (array $item) use ($academy, $academyRoutes) {
            if ($academy && in_array($item['route'], $academyRoutes, true)) {
                $item['urlArgs'] = [$academy];
            }

            if (!isset($item['url']) && $item['route'] === 'teacher.dashboard' && !empty($item['activeRoute'])) {
                $item['url'] = route('teacher.dashboard') . '#teacher-progress';
            }

            return $item;
        })
        ->values();

    $primaryItems = $visibleItems->filter(fn (array $item) => !empty($item['primary']))->take(4)->values();
@endphp

@if($visibleItems->isNotEmpty())
    <nav
        class="role-mobile-bottom-nav"
        style="--role-mobile-nav-items: {{ $primaryItems->count() + 1 }};"
        aria-label="{{ match ($role) {
            'student' => 'دسترسی سریع دانش‌آموز',
            'teacher' => 'دسترسی سریع مدرس',
            'parent' => 'دسترسی سریع والد',
            'owner' => $seoOnly ? 'دسترسی سریع متخصص SEO' : 'دسترسی سریع ادمین / منشی',
            default => 'دسترسی سریع',
        } }}"
    >
        @foreach($primaryItems as $item)
            @php
                $activePattern = $item['activeRoute'] ?? $item['route'];
                $active = empty($item['noActive']) && (request()->routeIs($activePattern)
                    || (
                        str_ends_with($item['route'], '.index')
                        && request()->routeIs(str_replace('.index', '.*', $item['route']))
                    ));
            @endphp
            <a
                href="{{ $item['url'] ?? route($item['route'], $item['urlArgs'] ?? []) }}"
                class="{{ $active ? 'is-active' : '' }}"
                @if($active) aria-current="page" @endif
                aria-label="{{ $item['label'] }}"
            >
                <x-navigation.mobile-nav-icon :icon="$item['icon']" />
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
        <button type="button" class="role-mobile-more-trigger" data-role-more-open aria-controls="role-mobile-more-{{ $role }}" aria-expanded="false">
            <x-navigation.mobile-nav-icon icon="▦" />
            <span>بیشتر</span>
        </button>
    </nav>

    <div id="role-mobile-more-{{ $role }}" class="role-mobile-more" data-role-more-panel hidden>
        <button type="button" class="role-mobile-more-backdrop" data-role-more-close aria-label="بستن منوی صفحات"></button>
        <section class="role-mobile-more-dialog" role="dialog" aria-modal="true" aria-labelledby="role-mobile-more-title-{{ $role }}" tabindex="-1">
            <header class="role-mobile-more-head">
                <div><strong id="role-mobile-more-title-{{ $role }}">همه بخش‌ها</strong><small>{{ $user?->name }}</small></div>
                <button type="button" data-role-more-close aria-label="بستن">×</button>
            </header>
            <nav class="role-mobile-more-links" aria-label="همه صفحات پنل">
                @foreach($visibleItems as $item)
                    @php
                        $activePattern = $item['activeRoute'] ?? $item['route'];
                        $active = request()->routeIs($activePattern)
                            || (
                                str_ends_with($item['route'], '.index')
                                && request()->routeIs(str_replace('.index', '.*', $item['route']))
                            );
                    @endphp
                    <a href="{{ $item['url'] ?? route($item['route'], $item['urlArgs'] ?? []) }}" class="{{ $active ? 'is-active' : '' }}">
                        <x-navigation.mobile-nav-icon :icon="$item['icon']" />
                        <span>{{ $item['label'] }}</span>
                        @if($active)<span class="role-mobile-more-current">فعلی</span>@endif
                    </a>
                @endforeach
                <a href="{{ route('home') }}">
                    <x-navigation.mobile-nav-icon icon="⌂" />
                    <span>مشاهده سایت</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="danger">
                        <x-navigation.mobile-nav-icon icon="⚙" />
                        <span>خروج از حساب</span>
                    </button>
                </form>
            </nav>
        </section>
    </div>
@endif
