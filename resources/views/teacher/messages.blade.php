@extends('layouts.teacher')
@section('title', 'پیام‌های اولیا')
@section('heading', 'پیام‌های اولیا')
@section('content')
    <div class="tp-toolbar"><div><h2>گفت‌وگوهای مرتبط با کلاس‌های تو</h2><p class="tp-muted">فقط پیام‌های والدین دانش‌آموزان کلاس‌ها و دوره‌های مجاز تو نمایش داده می‌شوند.</p></div></div>
    <div class="tp-tabs" role="tablist" aria-label="انواع پیام">
        <a href="{{ route('teacher.messages.index') }}" class="is-on" role="tab" aria-selected="true">پیام‌های اولیا</a>
        @if ($tpUser->hasPermission('student_messages.view'))<a href="{{ route('teacher.student-messages.index') }}" role="tab">پیام‌های دانش‌آموزان</a>@endif
        @if ($tpUser->hasPermission('parent_messages.manage'))<a href="{{ route('teacher.direct-messages.index') }}" role="tab">پیام مستقیم</a>@endif
    </div>
    @php($statusLabels = ['open' => 'در انتظار بررسی', 'in_review' => 'در حال پیگیری', 'responded' => 'پاسخ داده‌شده', 'closed' => 'بسته‌شده'])
    @php($categoryLabels = ['question' => 'پرسش', 'suggestion' => 'پیشنهاد', 'complaint' => 'انتقاد یا شکایت', 'other' => 'سایر'])
    <div class="tp-grid">
        @forelse ($messages as $message)
            <article class="tp-card">
                <div class="tp-status-row"><div><small class="tp-muted">{{ $message->academy?->name }} · دانش‌آموز: {{ $message->student?->name }}</small><h3>{{ $message->subject }}</h3></div><span class="tp-status-inline {{ $message->status === 'responded' ? 'ok' : ($message->status === 'closed' ? 'bad' : 'warn') }}">{{ $statusLabels[$message->status] ?? $message->status }}</span></div>
                <p style="margin-top:12px;white-space:pre-line;line-height:1.9">{{ $message->body }}</p>
                <div class="tp-status-row tp-muted" style="margin-top:10px"><span>{{ $message->parent?->name }}</span><span>{{ $categoryLabels[$message->category] ?? 'پیام' }}</span><span>{{ \App\Support\PersianUi::date($message->created_at) }}</span></div>
                @if ($message->response)
                    <div class="tp-flash tp-flash--success" style="margin-top:12px"><strong>پاسخ ثبت‌شده@if($message->responder) · {{ $message->responder->name }}@endif</strong><p style="margin-top:5px;white-space:pre-line">{{ $message->response }}</p></div>
                @endif
                @if ($message->status !== 'closed' && $tpUser->hasPermission('parent_messages.manage'))
                    <form method="POST" action="{{ route('teacher.messages.reply', $message) }}" class="tp-stack-form" style="margin-top:14px">
                        @csrf @method('PATCH')
                        <label class="tp-field">پاسخ برای والد<textarea name="response" rows="3" maxlength="5000" required placeholder="پاسخ روشن و محترمانه بنویس…">{{ old('response') }}</textarea></label>
                        <div><button class="tp-btn" type="submit">ثبت پاسخ</button></div>
                    </form>
                @endif
            </article>
        @empty
            <div class="tp-empty"><strong>پیامی برای کلاس‌ها و دوره‌های تو ثبت نشده است.</strong><span>پیام‌های والدین دانش‌آموزان مجاز در این صفحه ظاهر می‌شوند.</span></div>
        @endforelse
    </div>
    <div class="tp-pager">{{ $messages->links() }}</div>
@endsection