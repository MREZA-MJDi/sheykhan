@props([
    'title' => 'پنل شیخان',
    'role' => 'owner',
])

@php
    $topbarUser = auth()->user();
    $topbarAvatar = null;

    if ($role === 'teacher' && $topbarUser) {
        $topbarUser->loadMissing([
            'teacherProfile.media' => function ($query) {
                $query->wherePivot('collection', 'teacher-avatar')
                    ->orderByPivot('sort_order');
            },
        ]);

        $avatarMedia = $topbarUser->teacherProfile?->media?->first();
        $topbarAvatar = $avatarMedia?->visibility === 'public'
            ? $avatarMedia->url()
            : ($avatarMedia ? route('media.view', $avatarMedia) : null);
    } elseif ($role === 'student' && $topbarUser) {
        $topbarUser->loadMissing([
            'studentProfile.media' => function ($query) {
                $query->wherePivot('collection', 'student-avatar')
                    ->orderByPivot('sort_order');
            },
        ]);

        $avatarMedia = $topbarUser->studentProfile?->media?->first();
        $topbarAvatar = $avatarMedia?->visibility === 'public'
            ? $avatarMedia->url()
            : ($avatarMedia ? route('media.view', $avatarMedia) : null);
    }

    $seoOnlyRole = $role === 'owner'
        && ($topbarUser?->hasRole('seo-specialist') ?? false)
        && ! ($topbarUser?->hasRole('academy-owner') ?? false);

    $roleLabels = [
        'owner' => $seoOnlyRole ? 'پنل متخصص SEO' : 'کارتابل ادمین / منشی',
        'teacher' => 'پنل مدرس',
        'student' => 'پنل دانش‌آموز',
        'parent' => 'پنل والد',
    ];

    $topbarUserUrl = match ($role) {
        'teacher' => route('teacher.profile.edit'),
        'student' => route('student.profile.edit'),
        'parent' => route('parent.dashboard'),
        default => route('dashboard'),
    };

    $topbarUserLinkLabel = in_array($role, ['teacher', 'student'], true)
        ? 'ویرایش پروفایل کاربری'
        : ($role === 'parent' ? 'رفتن به داشبورد والد' : 'رفتن به داشبورد');
@endphp

<header class="role-topbar">
    <div class="role-topbar-leading">
        <button
            type="button"
            class="role-mobile-menu"
            data-role-menu-open
            aria-label="باز کردن منوی پنل"
            aria-expanded="false"
        >
            <span></span><span></span><span></span>
        </button>

        <div>
            <div class="role-topbar-eyebrow">شیخان · {{ $roleLabels[$role] ?? $title }}</div>
            <h1>{{ $title }}</h1>
        </div>
    </div>

    <div class="role-topbar-actions">
        <a href="{{ route('home') }}" class="role-topbar-site">مشاهده سایت</a>

        <a href="{{ $topbarUserUrl }}" class="role-user-chip" aria-label="{{ $topbarUserLinkLabel }}">
            <span class="role-user-avatar role-user-avatar-image">
                @if($topbarAvatar)
                    <img
                        data-profile-avatar
                        data-fallback-src="{{ asset('images/default-account-avatar.svg') }}"
                        src="{{ $topbarAvatar }}"
                        alt=""
                        width="40"
                        height="40"
                        loading="eager"
                        decoding="async"
                    >
                @else
                    <img
                        data-profile-avatar
                        src="{{ asset('images/default-account-avatar.svg') }}"
                        alt=""
                        width="40"
                        height="40"
                        loading="eager"
                        decoding="async"
                    >
                @endif
            </span>
            <span class="role-user-copy">
                <strong>{{ auth()->user()->name ?? 'کاربر' }}</strong>
                <small>{{ $roleLabels[$role] ?? 'کاربر' }}</small>
            </span>
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="role-logout-btn">خروج</button>
        </form>
    </div>
</header>
