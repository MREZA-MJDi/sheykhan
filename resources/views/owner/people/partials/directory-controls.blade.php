@php
    $directoryTitle = $title ?? 'اعضا';
    $showPerPage = $showPerPage ?? true;
@endphp
<div class="mt-4 grid gap-2 {{ $showPerPage ? 'sm:grid-cols-[minmax(0,1fr)_140px_100px_100px_auto]' : 'sm:grid-cols-[minmax(0,1fr)_140px_100px_auto]' }}" data-directory-controls>
    <label class="grid gap-1 text-[10px] font-bold text-slate-500">جست‌وجو
        <input type="search" data-directory-query maxlength="120" autocomplete="off" placeholder="نام، موبایل یا کد ملی…" aria-label="جست‌وجو در {{ $directoryTitle }}" class="min-h-9 rounded-lg border border-slate-200 px-2.5 text-xs">
    </label>
    <label class="grid gap-1 text-[10px] font-bold text-slate-500">مرتب‌سازی
        <select data-directory-sort class="min-h-9 rounded-lg border border-slate-200 px-2.5 text-xs">
            <option value="name">نام و نام خانوادگی</option>
            <option value="mobile">موبایل</option>
            <option value="joined_at">تاریخ عضویت</option>
            <option value="created_at">تاریخ ثبت</option>
        </select>
    </label>
    <label class="grid gap-1 text-[10px] font-bold text-slate-500">جهت
        <select data-directory-direction class="min-h-9 rounded-lg border border-slate-200 px-2.5 text-xs">
            <option value="asc">صعودی</option>
            <option value="desc">نزولی</option>
        </select>
    </label>
    @if($showPerPage)
        <label class="grid gap-1 text-[10px] font-bold text-slate-500">تعداد در صفحه
            <select data-directory-per-page class="min-h-9 rounded-lg border border-slate-200 px-2.5 text-xs">
                <option value="10" selected>۱۰</option>
                <option value="25">۲۵</option>
                <option value="50">۵۰</option>
                <option value="100">۱۰۰</option>
            </select>
        </label>
    @endif
    <button type="button" data-directory-reset class="self-end rounded-lg border border-slate-200 bg-white px-3 py-2 text-[10px] font-bold">پاک‌کردن</button>
</div>
