<header class="tp-top">
    <button class="tp-burger" type="button" aria-label="باز کردن منو" data-tp-drawer-open>☰</button>
    <div class="tp-top__heading">
        <h1>@yield('heading', $__env->yieldContent('header-title', 'پنل مدرس'))</h1>
        <small>{{ \App\Support\PersianUi::date(now()) }}</small>
    </div>
    <span class="tp-spacer"></span>
    @if ($tpUser?->hasPermission('profile.view'))
        <a class="tp-iconbtn" href="{{ route('teacher.profile.edit') }}" aria-label="تنظیمات حساب">⚙</a>
        <a href="{{ route('teacher.profile.edit') }}" aria-label="پروفایل {{ $tpUser?->name }}">
            <x-teacher.avatar :name="$tpUser?->name ?? 'مدرس'" :photo="$tpAvatarUrl" />
        </a>
    @else
        <x-teacher.avatar :name="$tpUser?->name ?? 'مدرس'" :photo="$tpAvatarUrl" />
    @endif
</header>