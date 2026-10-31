@extends('layouts.teacher')
@section('title', 'تکالیف')
@section('heading', 'تکالیف')
@section('content')
    <div class="tp-toolbar"><div><h2>تکالیف دوره‌های من</h2><p class="tp-muted">مدیریت مهلت، وضعیت انتشار و پاسخ‌های دریافت‌شده.</p></div>
        @if ($tpUser->hasPermission('assignments.manage'))<a class="tp-btn" href="{{ route('teacher.assignments.create') }}">+ تکلیف جدید</a>@endif
    </div>
    <div class="tp-grid tp-grid--three">
        <article class="tp-card"><small class="tp-muted">تکالیف این صفحه</small><b class="tp-number" style="display:block;font-size:24px">{{ \App\Support\PersianUi::digits($assignments->count()) }}</b></article>
        <article class="tp-card"><small class="tp-muted">ارسال‌شده‌ها</small><b class="tp-number" style="display:block;font-size:24px">{{ \App\Support\PersianUi::digits($assignments->getCollection()->sum('submitted_count')) }}</b></article>
        <article class="tp-card"><small class="tp-muted">منتظر تصحیح</small><b class="tp-number" style="display:block;font-size:24px">{{ \App\Support\PersianUi::digits($assignments->getCollection()->sum('pending_review_count')) }}</b></article>
    </div>
    <x-teacher.card title="فهرست تکالیف">
        <x-teacher.table :heads="['عنوان','دوره / کلاس','موعد تحویل','ارسال‌شده','منتظر تصحیح','وضعیت','اقدام']">
            @forelse ($assignments as $assignment)
                @php($status = ['draft' => ['پیش‌نویس','warn'], 'published' => ['منتشرشده','ok'], 'closed' => ['بسته','bad']][$assignment->status] ?? [$assignment->status,'default'])
                <tr>
                    <td><strong>{{ $assignment->title }}</strong></td>
                    <td>{{ $assignment->course?->title }}<small class="tp-muted" style="display:block">{{ $assignment->classroom?->title ?? 'همه دانش‌آموزان دوره' }}</small></td>
                    <td>{{ $assignment->due_at ? \App\Support\PersianUi::date($assignment->due_at) : 'بدون موعد' }}</td>
                    <td>{{ \App\Support\PersianUi::digits($assignment->submitted_count) }}</td>
                    <td><span class="tp-status-inline {{ $assignment->pending_review_count ? 'warn' : 'ok' }}">{{ \App\Support\PersianUi::digits($assignment->pending_review_count) }}</span></td>
                    <td><x-teacher.pill :type="$status[1]">{{ $status[0] }}</x-teacher.pill></td>
                    <td><a class="tp-link" href="{{ route('teacher.assignments.submissions', $assignment) }}">پاسخ‌ها</a></td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="tp-empty"><strong>تکلیفی ثبت نشده است.</strong></div></td></tr>
            @endforelse
        </x-teacher.table>
        <div class="tp-pager">{{ $assignments->links() }}</div>
    </x-teacher.card>
@endsection