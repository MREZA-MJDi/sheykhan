@props([
'tabs' => [],
'active' => null,
])

@php
    $defaultActive = $active
        ?? ($tabs[0]['id'] ?? null);
@endphp

<div
    x-data="{ active: @js($defaultActive) }"
    {{ $attributes->merge(['class' => 'w-full']) }}
    dir="rtl"
>
    {{-- Tabs navigation --}}
    <div class="-mb-px overflow-x-auto border-b border-[var(--color-border)]">
        <div
            role="tablist"
            aria-label="تب‌های صفحه"
            class="flex min-w-max gap-1"
        >
            @foreach($tabs as $tab)
                @php
                    $tabId = $tab['id'];
                    $panelId = $tabId . '-panel';
                @endphp

                <button
                    type="button"
                    role="tab"
                    id="{{ $tabId }}-tab"
                    aria-controls="{{ $panelId }}"
                    :aria-selected="active === @js($tabId)"
                    @click="active = @js($tabId)"
                    class="
                        relative
                        border-b-2
                        px-4
                        py-3
                        text-sm
                        font-semibold
                        whitespace-nowrap
                        transition-colors
                        duration-200
                        focus-visible:outline-none
                        focus-visible:ring-4
                        focus-visible:ring-[color-mix(in_srgb,var(--color-brand-200)_70%,transparent)]
                        border-transparent
                        text-[var(--color-text-secondary)]
                        hover:text-[var(--color-text-primary)]
                    "
                    :class="{
                        '!border-[var(--color-brand-600)] !text-[var(--color-brand-600)]': active === @js($tabId)
                    }"
                >
                    {{ $tab['label'] }}

                    @if(isset($tab['badge']))
                        <span
                            class="mr-1.5 rounded-full bg-[var(--color-brand-50)] px-1.5 py-0.5 text-[10px] font-bold text-[var(--color-brand-700)]"
                        >
                            {{ $tab['badge'] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Tab panels --}}
    @foreach($tabs as $tab)
        @php
            $tabId = $tab['id'];
            $panelId = $tabId . '-panel';
        @endphp

        <div
            x-show="active === @js($tabId)"
            x-cloak
            x-transition:enter="transition duration-150 ease-out"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            role="tabpanel"
            id="{{ $panelId }}"
            aria-labelledby="{{ $tabId }}-tab"
            tabindex="0"
            class="mt-5"
        >
            @if(isset($tab['content']))
                {{ $tab['content'] }}
            @endif
        </div>
    @endforeach

    {{ $slot }}
</div>
