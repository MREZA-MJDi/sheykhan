@php($pageHeading = trim($__env->yieldContent('heading')) ?: trim($__env->yieldContent('header-title')) ?: 'مرکز یادگیری')
<header class="top">
  <h1>{{ $pageHeading }}</h1>
  <form class="search" role="search" method="GET" action="{{ route('student.courses.index') }}">
    <svg class="i" aria-hidden="true"><use href="#i-search"/></svg>
    <input type="search" name="q" value="{{ request('q') }}" placeholder="جست‌وجوی دوره‌های من..." aria-label="جست‌وجوی دوره‌های من">
  </form>
  <a class="ib" href="{{ route('student.messages.index') }}" aria-label="پیام‌ها">
    <svg class="i"><use href="#i-bell"/></svg>
  </a>
  <a class="ib pr" href="{{ route('student.profile.edit') }}" aria-label="حساب کاربری">
    @if (!empty($studentShellAvatarUrl))
      <img data-avatar-image src="{{ $studentShellAvatarUrl }}" alt="" width="46" height="46" loading="eager" decoding="async" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
      <svg data-avatar-fallback hidden class="i" aria-hidden="true"><use href="#i-user"/></svg>
    @else
      <svg data-avatar-fallback class="i" aria-hidden="true"><use href="#i-user"/></svg>
    @endif
  </a>
</header>
