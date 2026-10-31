<aside class="tp-side" id="tp-side" aria-label="منوی مدرس">
    <div class="tp-logo"><i>◆</i><span>{{ config('app.name', 'آکادمی شیخان') }}</span></div>
    <div class="tp-teacher-mini">
        <x-teacher.avatar :name="$tpUser?->name ?? 'مدرس'" :photo="$tpAvatarUrl" :size="42" />
        <div><strong>{{ $tpUser?->name ?? 'مدرس' }}</strong><small>پنل مدرس</small></div>
    </div>
    <nav class="tp-sidenav" aria-label="بخش‌های پنل">
        @foreach ($tpNav as $item)
            @php($patterns = explode('|', $item['active']))
            @php($isActive = collect($patterns)->contains(fn ($pattern) => request()->routeIs($pattern)))
            <a href="{{ route($item['route']) }}" class="tp-nav {{ $isActive ? 'is-on' : '' }}" @if($isActive) aria-current="page" @endif>
                <i aria-hidden="true">{{ $item['icon'] }}</i><span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
    <form method="POST" action="{{ route('logout') }}" class="tp-side__foot">
        @csrf
        <span>🏫 {{ config('app.name', 'آکادمی شیخان') }}</span>
        <button type="submit">خروج از حساب</button>
    </form>
</aside>