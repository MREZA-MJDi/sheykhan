<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل دانش‌آموز | شیخان')</title>
    @vite(['resources/css/student.css', 'resources/js/student.js'])
    @stack('styles')
</head>
<body class="student-shell">
    <div class="role-layout">
        <x-navigation.student-sidebar />

        <div class="role-content">
            @php($studentHeaderTitle = trim($__env->yieldContent('header-title', 'پنل دانش‌آموز')))
            <x-navigation.panel-topbar role="student" :title="$studentHeaderTitle" />

            <main class="role-main">
                @if(session('success'))
                    <div class="student-alert student-alert-success" role="status" aria-live="polite">
                        <span class="student-alert-icon" aria-hidden="true">✓</span>
                        <div>
                            <strong>انجام شد</strong>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="student-alert student-alert-danger" role="alert" aria-live="assertive">
                        <span class="student-alert-icon" aria-hidden="true">!</span>
                        <div>
                            <strong>نیاز به توجه</strong>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="student-alert student-alert-danger" role="alert" aria-live="assertive">
                        <span class="student-alert-icon" aria-hidden="true">!</span>
                        <div>
                            <strong>اطلاعات بررسی نشد</strong>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
