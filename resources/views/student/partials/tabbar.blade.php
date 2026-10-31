@php($tabItems = \App\Support\StudentNav::items()->filter(fn ($item) => isset($item['key'])))
<nav class="tabbar" aria-label="منوی موبایل">
  @foreach ($tabItems->where('tab', true) as $item)
    <a href="{{ route($item['route']) }}"
       @class(['on' => request()->routeIs(...(array) $item['active'])])
       @if (request()->routeIs(...(array) $item['active'])) aria-current="page" @endif>
      <svg class="i"><use href="#{{ $item['icon'] }}"/></svg>
      {{ explode(' ', $item['label'], 2)[0] }}
    </a>
  @endforeach
  <details class="more">
    <summary><svg class="i"><use href="#i-more"/></svg>بیشتر</summary>
    <div class="sheet">
      @foreach ($tabItems->where('tab', '!=', true) as $item)
        <a href="{{ route($item['route']) }}"
           @class(['on' => request()->routeIs(...(array) $item['active'])])>
          <svg class="i"><use href="#{{ $item['icon'] }}"/></svg>{{ $item['label'] }}
        </a>
      @endforeach
    </div>
  </details>
</nav>
