@props([
'items' => [],
])

<nav
    class="flex items-center gap-4 overflow-x-auto whitespace-nowrap"
    aria-label="مسیر صفحه"
    dir="rtl"
>
    {{-- خانه --}}
    <a
        href="{{ url('/') }}"
        class="flex items-center shrink-0 text-gray-500 transition-colors duration-200 hover:text-gray-900"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-5 h-5"
            viewBox="0 0 20 20"
            fill="currentColor"
        >
            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
        </svg>

        <span class="mr-2 text-sm">
            خانه
        </span>
    </a>

    @foreach ($items as $item)
        <span class="text-gray-300 shrink-0">
            /
        </span>

        @if (!empty($item['url']))
            <a
                href="{{ $item['url'] }}"
                class="text-sm text-gray-500 transition-colors duration-200 hover:text-gray-900"
            >
                {{ $item['label'] }}
            </a>
        @else
            <span class="text-sm font-medium text-gray-900">
                {{ $item['label'] }}
            </span>
        @endif
    @endforeach
</nav>
