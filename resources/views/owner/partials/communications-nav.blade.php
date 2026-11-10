@php($communicationAcademy = $academy ?? request()->route('academy'))
@if($communicationAcademy)
<nav class="flex flex-wrap gap-2 rounded-2xl border border-[var(--panel-border)] bg-[var(--panel-surface)] p-2" aria-label="بخش‌های ارتباطات آموزشگاه">
    <a href="{{ route('owner.messages.index', $communicationAcademy) }}"
       class="inline-flex min-h-10 items-center rounded-xl px-4 text-sm font-bold {{ request()->routeIs('owner.messages.*') ? 'bg-[var(--panel-primary)] text-white' : 'text-[var(--panel-text)] hover:bg-[var(--panel-muted)]' }}"
       @if(request()->routeIs('owner.messages.*')) aria-current="page" @endif>صندوق پیام خانواده‌ها</a>
    <a href="{{ route('owner.communications.index', $communicationAcademy) }}"
       class="inline-flex min-h-10 items-center rounded-xl px-4 text-sm font-bold {{ request()->routeIs('owner.communications.*') ? 'bg-[var(--panel-primary)] text-white' : 'text-[var(--panel-text)] hover:bg-[var(--panel-muted)]' }}"
       @if(request()->routeIs('owner.communications.*')) aria-current="page" @endif>اطلاعیه‌ها و پیام‌های خروجی</a>
    <a href="{{ route('owner.counseling.index', $communicationAcademy) }}"
       class="inline-flex min-h-10 items-center rounded-xl px-4 text-sm font-bold {{ request()->routeIs('owner.counseling.*') ? 'bg-[var(--panel-primary)] text-white' : 'text-[var(--panel-text)] hover:bg-[var(--panel-muted)]' }}"
       @if(request()->routeIs('owner.counseling.*')) aria-current="page" @endif>درخواست‌های مشاوره</a>
</nav>
@endif
