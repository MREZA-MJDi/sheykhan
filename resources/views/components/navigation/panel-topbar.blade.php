@props([
    'title' => 'پنل شیخان',
    'role' => 'owner',
])

@php
    $topbarUser = auth()->user();
    $topbarAvatar = null;

    if ($role === 'teacher' && $topbarUser) {
        $topbarUser->loadMissing([
            'teacherProfile.media' => fn ($query) => $query
                ->wherePivot('collection', 'teacher-avatar')
                ->orderByPivot('sort_order'),
        ]);

        $avatarMedia = $topbarUser->teacherProfile?->media?->first();
        $topbarAvatar = $avatarMedia?->visibility === 'public'
            ? $avatarMedia->url()
            : ($avatarMedia ? route('media.view', $avatarMedia) : null);
    }

    $roleLabels = [
        'owner' => 'مدیریت آموزشگاه',
        'teacher' => 'پنل استاد',
        'student' => 'پنل دانش‌آموز',
        'parent' => 'پنل والد',
    ];
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

        <a href="{{ $role === 'teacher' ? route('teacher.profile.edit') : ($role === 'student' ? route('student.profile.edit') : route('home')) }}" class="role-user-chip" aria-label="پروفایل کاربری">
            <span class="role-user-avatar role-user-avatar-image">
                @if($topbarAvatar)
                    <img src="{{ $topbarAvatar }}" alt="" loading="lazy">
                @else
                    <img src="{{ asset('images/default-account-avatar.svg') }}" alt="" loading="lazy">
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
