<nav class="flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white p-2" aria-label="ارتباط با آموزشگاه">
    <a href="{{ route('parent.messages.index') }}"
       class="inline-flex min-h-10 items-center rounded-xl px-4 text-sm font-bold {{ request()->routeIs('parent.messages.*') ? 'bg-[var(--panel-primary)] text-white' : 'text-slate-700 hover:bg-slate-50' }}"
       @if(request()->routeIs('parent.messages.*')) aria-current="page" @endif>پیام‌ها و اطلاعیه‌ها</a>
    <a href="{{ route('parent.counseling.index') }}"
       class="inline-flex min-h-10 items-center rounded-xl px-4 text-sm font-bold {{ request()->routeIs('parent.counseling.*') ? 'bg-[var(--panel-primary)] text-white' : 'text-slate-700 hover:bg-slate-50' }}"
       @if(request()->routeIs('parent.counseling.*')) aria-current="page" @endif>مشاوره و درخواست جلسه</a>
</nav>
