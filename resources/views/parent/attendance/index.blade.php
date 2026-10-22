@extends('layouts.parent')

@section('title', 'حضور و غیاب فرزندان | شیخان')
@section('header-title', 'حضور و غیاب')

@section('content')
<div class="parent-dashboard space-y-6">
    <header class="parent-panel dashboard-panel p-5 sm:p-7">
        <p class="parent-kicker" style="color:var(--panel-primary)">پیگیری آموزشی</p>
        <h1 class="mt-1 text-2xl font-black">سوابق حضور و غیاب فرزندان</h1>
        <p class="mt-2 text-sm leading-7 text-slate-500">فقط سابقهٔ دانش‌آموزانی نمایش داده می‌شود که به حساب والد متصل هستند. تاریخ‌ها به تقویم شمسی نمایش داده می‌شوند.</p>
    </header>
    <section class="parent-panel dashboard-panel overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-right">
                <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="p-4">دانش‌آموز</th><th class="p-4">کلاس</th><th class="p-4">دوره</th><th class="p-4">تاریخ</th><th class="p-4">وضعیت</th><th class="p-4">یادداشت</th></tr></thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($attendance as $item)
                        <tr>
                            <td class="p-4 font-bold">{{ $item->student?->name ?? '—' }}</td>
                            <td class="p-4">{{ $item->classroom?->title ?? '—' }}</td>
                            <td class="p-4">{{ $item->classroom?->course?->title ?? '—' }}</td>
                            <td class="p-4 whitespace-nowrap">{{ \App\Support\PersianUi::date($item->attendance_date) }}</td>
                            <td class="p-4">{{ ['present'=>'حاضر','absent'=>'غایب','late'=>'با تأخیر','excused'=>'غیبت موجه'][$item->status] ?? $item->status }}</td>
                            <td class="p-4 max-w-sm whitespace-pre-line text-slate-500">{{ $item->note ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-slate-500">سابقهٔ حضور و غیابی برای فرزندان متصل ثبت نشده است.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-4">{{ $attendance->links() }}</div>
    </section>
</div>
@endsection
