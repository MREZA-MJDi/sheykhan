@props([
    'href' => null,
    'linkLabel' => null,
    'eyebrow' => 'راهنمای سریع',
])

<aside class="my-4 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-slate-700 sm:flex-row sm:items-center sm:justify-between sm:gap-6" role="note">
    <div class="min-w-0">
        <span class="text-[11px] font-black text-slate-500">{{ $eyebrow }}</span>
        <p class="mt-1 text-sm leading-7">{{ $slot }}</p>
    </div>
    @if($href && $linkLabel)
        <a href="{{ $href }}" class="inline-flex min-h-10 shrink-0 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-black text-slate-800 transition hover:bg-slate-100">
            {{ $linkLabel }} <span class="ms-2" aria-hidden="true">←</span>
        </a>
    @endif
</aside>
