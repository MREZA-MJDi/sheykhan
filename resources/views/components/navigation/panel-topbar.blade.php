@props([
    'title' => 'پنل شیخان',
    'role' => 'owner',
])

@php
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

        <div class="role-user-chip">
            <span class="role-user-avatar">{{ mb_substr(auth()->user()->name ?? 'ش', 0, 1) }}</span>
            <span class="role-user-copy">
                <strong>{{ auth()->user()->name ?? 'کاربر' }}</strong>
                <small>{{ $roleLabels[$role] ?? 'کاربر' }}</small>
            </span>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="role-logout-btn">خروج</button>
        </form>
    </div>
</header>
