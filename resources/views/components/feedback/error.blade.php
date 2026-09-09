@props([
'title' => 'خطایی رخ داده است',
'errors' => [],
])

@php
    $messages = is_iterable($errors)
        ? collect($errors)->flatten()
        : collect([$errors]);

    if ($messages->isEmpty() && isset($message)) {
        $messages = collect([$message]);
    }
@endphp

<div
    {{ $attributes->merge([
        'class' => 'rounded-xl border border-[var(--color-danger-100)] bg-[var(--color-danger-50)] p-4 text-[var(--color-danger-700)]',
        'role' => 'alert',
    ]) }}
    dir="rtl"
>
    <div class="flex items-start gap-3">
        <div class="mt-0.5 shrink-0">
            <svg
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <circle cx="12" cy="12" r="9" />
                <path d="m15 9-6 6M9 9l6 6" />
            </svg>
        </div>

        <div class="min-w-0 flex-1">
            <h3 class="font-bold">
                {{ $title }}
            </h3>

            @if($messages->isNotEmpty())
                <ul class="mt-2 space-y-1 text-sm leading-6">
                    @foreach($messages as $message)
                        <li>
                            {{ $message }}
                        </li>
                    @endforeach
                </ul>
            @elseif($slot->isNotEmpty())
                <div class="mt-1 text-sm leading-6">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </div>
</div>
