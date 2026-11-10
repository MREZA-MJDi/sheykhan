@php
    $attendanceSheets = [
        ['key' => 'history', 'label' => 'ثبت و سوابق', 'icon' => '▤'],
        ['key' => 'report', 'label' => 'غیبت‌ها و گزارش کلاسی', 'icon' => '▥'],
    ];
@endphp
<section class="owner-workspace-tabs rounded-2xl border border-teal-100 bg-gradient-to-l from-teal-50 via-white to-cyan-50 p-4 sm:p-5" aria-label="بخش حضور و غیاب">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div><p class="text-[11px] font-black text-teal-700">بخش حضور و غیاب</p><h2 class="mt-1 text-base font-black text-slate-900">پیگیری وضعیت کلاس‌ها</h2><p class="mt-1 text-xs leading-6 text-slate-600">ثبت و اصلاح حضور، دیدن سوابق و گرفتن گزارش</p></div>
        <span class="rounded-xl border border-teal-100 bg-white px-3 py-2 text-xs font-bold text-teal-800">{{ $academy->name }}</span>
    </div>
    <nav class="mt-4 flex flex-wrap gap-2">
        @foreach($attendanceSheets as $sheetItem)
            <a href="{{ route('owner.attendance.index', ['academy' => $academy->id, ...request()->except('page', 'sheet'), 'sheet' => $sheetItem['key']]) }}"
               @if(($activeSheet ?? '') === $sheetItem['key']) aria-current="page" @endif
               class="inline-flex min-h-10 items-center gap-2 rounded-xl px-4 text-xs font-black transition {{ ($activeSheet ?? '') === $sheetItem['key'] ? 'bg-teal-700 text-white shadow-sm' : 'border border-teal-100 bg-white text-slate-700 hover:border-teal-300 hover:text-teal-800' }}">
                <span aria-hidden="true">{{ $sheetItem['icon'] }}</span>{{ $sheetItem['label'] }}
            </a>
        @endforeach
    </nav>
</section>
