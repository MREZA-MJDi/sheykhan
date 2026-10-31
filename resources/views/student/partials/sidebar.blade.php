@php($navItems = \App\Support\StudentNav::items())
<aside class="side">
  <div class="me">
    <div class="av">
      @if (!empty($studentShellAvatarUrl))
        <img src="{{ $studentShellAvatarUrl }}" alt="تصویر پروفایل {{ $studentShellUser->name }}" width="104" height="104" loading="eager" decoding="async" style="width:100%;height:100%;object-fit:cover">
        <svg data-avatar-fallback hidden viewBox="0 0 120 120" width="100%" height="100%" aria-hidden="true"><use href="#avatar"/></svg>
      @else
        <svg data-avatar-fallback viewBox="0 0 120 120" width="100%" height="100%" aria-hidden="true"><use href="#avatar"/></svg>
      @endif
    </div>
    <h3>سلام {{ $studentShellUser->name }}!</h3>
  </div>

  <nav class="nav" aria-label="منوی دانش‌آموز">
    @foreach ($navItems as $item)
      @if (isset($item['group']))
        <h6>{{ $item['group'] }}</h6>
      @else
        <a href="{{ route($item['route']) }}"
           @class(['on' => request()->routeIs(...(array) $item['active'])])
           @if (request()->routeIs(...(array) $item['active'])) aria-current="page" @endif>
          <svg class="i" aria-hidden="true"><use href="#{{ $item['icon'] }}"/></svg>
          {{ $item['label'] }}
        </a>
      @endif
    @endforeach
  </nav>

  <div class="prem">
    <h4>مسیر یادگیری</h4>
    <p>درس‌ها، کلاس‌ها و تکالیف خودت را از همین‌جا دنبال کن.</p>
    <a class="btn s" href="{{ route('student.courses.index') }}">ادامه یادگیری</a>
    <form method="POST" action="{{ route('logout') }}" style="margin-top:8px">
      @csrf
      <button class="btn s" type="submit" style="width:100%;background:rgba(255,255,255,.1);box-shadow:none">خروج از حساب</button>
    </form>
  </div>
</aside>
