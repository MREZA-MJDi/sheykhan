@extends('layouts.student')

@section('title', 'پیام‌های من')
@section('header-title', 'پیام‌های من')

@section('content')
<section class="page" id="messages">
    <div class="card">
        <div class="chead"><h2>پیام از آموزشگاه</h2><span class="pill">{{ \App\Support\PersianUi::digits($academyMessages->total()) }} پیام</span></div>
        @forelse($academyMessages as $message)
            <article class="note" style="margin-bottom:10px">
                <div class="chead">
                    <strong>{{ $message->subject }}</strong>
                    <span class="tag">{{ $message->published_at ? \App\Support\PersianUi::dateTime($message->published_at) : \App\Support\PersianUi::dateTime($message->created_at) }}</span>
                </div>
                <p>{{ $message->body }}</p>
                <p style="margin-top:8px;font-size:11px;color:var(--muted)">فرستنده: {{ $message->sender?->name ?? $message->academy?->name ?? 'آموزشگاه' }}
                    @if($message->course) · {{ $message->course->title }} @endif
                    @if($message->classroom) · {{ $message->classroom->title }} @endif
                </p>
            </article>
        @empty
            <div class="empty">اطلاعیه‌ای از آموزشگاه برای تو ثبت نشده است.</div>
        @endforelse
        @if($academyMessages->hasPages())<div class="frm">{{ $academyMessages->links() }}</div>@endif
    </div>

    <div class="card">
        <div class="chead"><h2>پیام‌های مدرس‌ها</h2><span class="pill">{{ \App\Support\PersianUi::digits($teacherMessages->total()) }} پیام</span></div>
        @forelse($teacherMessages as $message)
            <article class="note" style="margin-bottom:10px">
                <div class="chead">
                    <strong>{{ $message->subject }}</strong>
                    <span class="tag {{ $message->status === 'responded' ? 'ok' : 'd' }}">{{ $message->status === 'responded' ? 'پاسخ داده‌شده' : 'در انتظار پاسخ' }}</span>
                </div>
                <p>{{ $message->body }}</p>
                <p style="margin-top:8px;font-size:11px;color:var(--muted)">
                    {{ $message->teacher?->name ?? 'مدرس' }} @if($message->course) · {{ $message->course->title }} @endif
                    @if($message->classroom) · {{ $message->classroom->title }} @endif
                    · {{ \App\Support\PersianUi::dateTime($message->created_at) }}
                </p>
                @if($message->response)
                    <div class="m" style="margin-top:10px"><strong>پاسخ تو:</strong><br>{{ $message->response }}</div>
                @elseif($message->status === 'sent')
                    <form class="frm" method="POST" action="{{ route('student.teacher-messages.reply', $message->id) }}">
                        @csrf
                        @method('PATCH')
                        <label for="response-{{ $message->id }}" class="visually-hidden">پاسخ به پیام</label>
                        <textarea class="fld" id="response-{{ $message->id }}" name="response" rows="2" maxlength="5000" minlength="2" required placeholder="پاسخت را بنویس..."></textarea>
                        <button class="btn s" type="submit">ارسال پاسخ</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="empty">پیامی از مدرس‌ها برای نمایش وجود ندارد.</div>
        @endforelse
        @if($teacherMessages->hasPages())<div class="frm">{{ $teacherMessages->links() }}</div>@endif
    </div>

    <div class="card">
        <div class="chead"><h2>پیام‌های ارسالی تو</h2><span class="pill">{{ \App\Support\PersianUi::digits($messages->total()) }} پیام</span></div>
        @forelse($messages as $message)
            <article class="note" style="margin-bottom:10px">
                <div class="chead">
                    <strong>{{ $message->subject }}</strong>
                    <span class="tag {{ $message->status === 'closed' ? 'ok' : 'w' }}">{{ match($message->status) { 'closed' => 'بسته‌شده', 'responded' => 'پاسخ داده‌شده', default => 'باز' } }}</span>
                </div>
                <p>{{ $message->body }}</p>
                <p style="margin-top:8px;font-size:11px;color:var(--muted)">
                    {{ $message->teacher?->name ?? 'مدرس' }}
                    @if($message->course) · {{ $message->course->title }} @endif
                    @if($message->classroom) · {{ $message->classroom->title }} @endif
                    · {{ \App\Support\PersianUi::dateTime($message->created_at) }}
                </p>
                @if($message->response)
                    <div class="m" style="margin-top:10px"><strong>پاسخ مدرس:</strong><br>{{ $message->response }}</div>
                @endif
            </article>
        @empty
            <div class="empty">هنوز پیامی ارسال نکرده‌ای.</div>
        @endforelse
        @if($messages->hasPages())<div class="frm">{{ $messages->links() }}</div>@endif
    </div>

    @if(auth()->user()->hasPermission('student_messages.manage'))
        <form class="card" method="POST" action="{{ route('student.messages.store') }}">
            @csrf
            <div class="chead"><h2>پیام جدید به مدرس</h2></div>
            @if($contactableTargets->isEmpty())
                <p class="empty">برای ارسال پیام باید در یک دوره یا کلاس فعال با مدرس مرتبط باشی.</p>
            @else
                <div class="pf">
                    <div>
                        <label for="message-target">مدرس و دوره / کلاس</label>
                        <select class="fld" id="message-target" name="target" required>
                            <option value="">انتخاب کن...</option>
                            @foreach($contactableTargets as $target)
                                <option value="{{ $target->key }}" @selected(old('target') === $target->key)>
                                    {{ $target->teacher_name }} · {{ $target->context_title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="message-category">موضوع پیام</label>
                        <select class="fld" id="message-category" name="category" required>
                            <option value="question" @selected(old('category') === 'question')>سؤال درسی</option>
                            <option value="suggestion" @selected(old('category') === 'suggestion')>پیشنهاد</option>
                            <option value="complaint" @selected(old('category') === 'complaint')>گزارش مشکل</option>
                            <option value="other" @selected(old('category') === 'other')>سایر</option>
                        </select>
                    </div>
                    <div style="grid-column:1/-1">
                        <label for="message-subject">عنوان</label>
                        <input class="fld" id="message-subject" name="subject" value="{{ old('subject') }}" maxlength="180" minlength="3" required>
                    </div>
                </div>
                <label for="message-body" style="display:block;font-size:12px;margin:14px 0 8px">متن پیام</label>
                <textarea class="fld" id="message-body" name="body" rows="4" maxlength="10000" minlength="5" required>{{ old('body') }}</textarea>
                <div class="frm"><button class="btn s" type="submit">ارسال پیام</button></div>
            @endif
        </form>
    @endif
</section>
@endsection
