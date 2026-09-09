@props([
'title' => 'پنل فرزین',
'items' => [],
])

<aside
    {{ $attributes->merge([
        'class' => 'flex h-full w-full flex-col border-l border-[var(--color-border)] bg-[var(--color-surface)]'
    ]) }}
    dir="rtl"
>
    {{-- Header --}}
    <div class="border-b border-[var(--color-border)] p-5">
        <a
            href="{{ route('home') }}"
            class="flex items-center gap-3"
        >
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-brand-600)] text-base font-black text-white">
                ف
            </span>

            <div class="min-w-0">
                <div class="truncate text-sm font-extrabold text-[var(--color-text-primary)]">
                    فرزین
                </div>

                <div class="truncate text-xs text-[var(--color-text-muted)]">
                    {{ $title }}
                </div>
            </div>
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto p-4" aria-label="منوی پنل">

        @if(count($items))
            <div class="space-y-1">
                @foreach($items as $item)
                    @php
                        $active = !empty($item['route'])
                            ? request()->routeIs($item['route'])
                            : false;
                    @endphp

                    <a
                        href="{{ $item['url'] ?? '#' }}"
                        class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-semibold transition-colors duration-200
                        {{ $active
                            ? 'bg-[var(--color-brand-50)] text-[var(--color-brand-700)]'
                            : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-neutral-50)] hover:text-[var(--color-text-primary)]' }}"
                    >
                        @if(!empty($item['icon']))
                            <span
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                                {{ $active
                                    ? 'bg-white text-[var(--color-brand-600)] shadow-[var(--shadow-xs)]'
                                    : 'bg-[var(--color-neutral-100)] text-[var(--color-text-muted)] group-hover:bg-white' }}"
                            >
                                {!! $item['icon'] !!}
                            </span>
                        @else
                            <span
                                class="h-2 w-2 shrink-0 rounded-full
                                {{ $active ? 'bg-[var(--color-brand-600)]' : 'bg-[var(--color-neutral-300)]' }}"
                            ></span>
                        @endif

                        <span class="min-w-0 flex-1 truncate">
                            {{ $item['label'] }}
                        </span>

                        @if(isset($item['badge']))
                            <span class="ui-badge ui-badge-brand">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        @else
            {{ $slot }}
        @endif
    </nav>

    {{-- Footer --}}
    <div class="border-t border-[var(--color-border)] p-4">
        <div class="rounded-xl bg-[var(--color-brand-50)] p-4">
            <p class="text-xs font-medium leading-6 text-[var(--color-brand-700)]">
                مسیر یادگیریت را با فرزین ادامه بده.
            </p>
        </div>
    </div>
</aside>
