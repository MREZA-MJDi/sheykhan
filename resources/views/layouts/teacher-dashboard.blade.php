<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'داشبورد مدرس | شیخان')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/teacher-dashboard.css', 'resources/js/teacher-dashboard.js'])
    @stack('styles')
</head>
<body class="teacher-dashboard-shell" data-theme="light">
    <div class="tdb-app" id="teacher-dashboard-app">
        <x-navigation.teacher-sidebar />

        <div class="tdb-workspace">
            <header class="tdb-topbar">
                <button class="tdb-icon-button tdb-sidebar-toggle" type="button" data-sidebar-toggle aria-controls="teacher-dashboard-sidebar" aria-expanded="false" aria-label="باز کردن منو">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>

                <label class="tdb-search">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
                    <input type="search" data-dashboard-search placeholder="جست‌وجو در داشبورد…" aria-label="جست‌وجو در داشبورد مدرس">
                </label>

                <div class="tdb-topbar-actions">
                    @if(auth()->user()?->hasPermission('profile.view'))
                        <a class="tdb-profile" href="{{ route('teacher.profile.edit') }}" aria-label="رفتن به پروفایل مدرس">
                    @else
                        <div class="tdb-profile">
                    @endif
                        <span class="tdb-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::substr(trim((string) auth()->user()->name), 0, 1) ?: 'م' }}</span>
                        <span class="tdb-profile-copy"><strong>{{ auth()->user()->name }}</strong><small>پنل مدرس</small></span>
                    @if(auth()->user()?->hasPermission('profile.view'))
                        </a>
                    @else
                        </div>
                    @endif
                    <button class="tdb-icon-button" type="button" data-teacher-theme-toggle aria-label="فعال‌کردن تم تیره" aria-pressed="false" title="تغییر تم">
                        <svg data-theme-moon viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5 8.7 8.7 0 1 0 20.5 14.2Z"></path></svg>
                        <svg data-theme-sun viewBox="0 0 24 24" aria-hidden="true" hidden><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path></svg>
                    </button>
                    @if(auth()->user()?->hasPermission('parent_messages.view'))
                        <a class="tdb-icon-button" href="{{ route('teacher.messages.index') }}" title="پیام‌های اولیا" aria-label="رفتن به پیام‌های اولیا">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v12H9l-5 4z"></path><path d="M8 9h8M8 13h5"></path></svg>
                        </a>
                    @endif
                </div>
            </header>

            <main class="tdb-main">
                <x-ui.flash-messages />
                @yield('content')
            </main>
        </div>
    </div>

    <button class="tdb-backdrop" type="button" data-sidebar-close aria-label="بستن منوی کناری" hidden></button>
    @stack('scripts')
</body>
</html>
