@extends('layouts.student')

@section('title', 'حضور و غیاب')
@section('header-title', 'حضور و غیاب')

@section('content')
@php
    $statusLabels = ['present' => 'حاضر', 'absent' => 'غایب', 'late' => 'با تأخیر', 'excused' => 'موجه'];
@endphp
<section class="page" id="attendance">
    <div class="card">
        <div class="chead"><h2>سوابق حضور و غیاب</h2><span class="pill">{{ \App\Support\PersianUi::digits($attendance->total()) }} رکورد</span></div>
        <div class="tw">
            <table class="tbl">
                <thead><tr><th>تاریخ</th><th>کلاس</th><th>وضعیت</th><th>مدت حضور</th><th>توضیح</th></tr></thead>
                <tbody>
                @forelse($attendance as $row)
                    <tr>
                        <td>{{ \App\Support\PersianUi::date($row->attendance_date) }}</td>
                        <td>{{ $row->classroom?->title ?? 'کلاس' }}</td>
                        <td><span class="tag {{ $row->status === 'present' ? 'ok' : ($row->status === 'absent' ? 'no' : 'w') }}">{{ $statusLabels[$row->status] ?? $row->status }}</span></td>
                        <td>
                            @if($row->attended_minutes !== null && $row->scheduled_minutes !== null)
                                {{ \App\Support\PersianUi::digits($row->attended_minutes) }} از {{ \App\Support\PersianUi::digits($row->scheduled_minutes) }} دقیقه
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $row->note ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">سابقهٔ حضور و غیابی برای کلاس‌های فعال تو ثبت نشده است.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($attendance->hasPages())<div class="frm">{{ $attendance->links() }}</div>@endif
    </div>
</section>
@endsection
