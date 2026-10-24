@extends('layouts.teacher')
@section('title','بررسی تکلیف | شیخان')
@section('header-title','بررسی پاسخ‌ها')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">ارزیابی</span>
            <h1 class="teacher-workspace-title">{{ $assignment->title }}</h1>
            <p class="teacher-workspace-description">{{ $assignment->classroom?->title ?? 'همه دانش‌آموزان دوره' }} · نمره کل {{ App\Support\PersianUi::digits($assignment->max_score ?? 0) }}</p>
        </div>
        <a href="{{ route('teacher.assignments.index') }}" class="teacher-workspace-btn secondary">بازگشت به تکالیف</a>
    </header>

    @if(session('success'))<div class="teacher-workspace-alert success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="teacher-workspace-alert error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head"><div><h2>پاسخ‌های دانش‌آموزان</h2><p>هر پاسخ در همین صفحه قابل نمره‌دهی و بازخورد است.</p></div><span class="teacher-workspace-chip">{{ App\Support\PersianUi::digits($submissions->total()) }} پاسخ</span></div>
        <div class="teacher-workspace-list">
            @forelse($submissions as $submission)
                <article class="teacher-workspace-item teacher-submission-row">
                    <div class="teacher-workspace-item-main">
                        <strong class="teacher-workspace-item-title">{{ $submission->student?->name ?: 'دانش‌آموز' }}</strong>
                        <div class="teacher-workspace-item-meta">
                            <span>تحویل {{ $submission->submitted_at ? App\Support\PersianUi::date($submission->submitted_at).' · '.App\Support\PersianUi::time($submission->submitted_at) : '—' }}</span>
                            @if($submission->graded_at)<span>تصحیح‌شده</span>@else<span>در انتظار تصحیح</span>@endif
                        </div>
                        <div class="teacher-submission-answer">{{ $submission->content ?: 'پاسخ متنی ثبت نشده است.' }}</div>
                        @if($submission->media->isNotEmpty())
                            <div class="teacher-submission-files" aria-label="فایل‌های پیوست پاسخ">
                                <strong class="teacher-submission-files-title">فایل‌های پیوست پاسخ</strong>
                                @foreach($submission->media as $attachment)
                                    <a class="teacher-submission-file" href="{{ route('media.view', $attachment) }}" target="_blank" rel="noopener noreferrer" aria-label="مشاهده فایل {{ $attachment->original_name }}">
                                        <span class="teacher-submission-file-type" aria-hidden="true">{{ strtoupper($attachment->extension ?: 'FILE') }}</span>
                                        <span class="teacher-submission-file-copy">
                                            <strong>{{ $attachment->original_name }}</strong>
                                            <small>{{ \App\Support\PersianUi::number((int) ceil(((int) $attachment->size) / 1024)) }} کیلوبایت</small>
                                        </span>
                                        <span class="teacher-submission-file-open">مشاهده ↗</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <form class="teacher-submission-grade" method="POST" action="{{ route('teacher.assignments.submissions.update',[$assignment,$submission]) }}" style="width:min(320px,100%);display:grid;gap:8px">
                        @csrf @method('PATCH')
                        <label class="teacher-workspace-field"><span>نمره</span><input type="number" step="0.01" min="0" max="{{ $assignment->max_score ?? 999999 }}" name="score" value="{{ $submission->score }}" required></label>
                        <label class="teacher-workspace-field"><span>بازخورد</span><textarea name="feedback" rows="4">{{ $submission->feedback }}</textarea></label>
                        <button type="submit" class="teacher-workspace-btn primary">ثبت تصحیح</button>
                    </form>
                </article>
            @empty
                <div class="teacher-workspace-empty"><strong>هنوز پاسخی ثبت نشده است.</strong>پس از ارسال پاسخ توسط دانش‌آموزان، اینجا نمایش داده می‌شود.</div>
            @endforelse
        </div>
        @if($submissions->hasPages())<div class="teacher-workspace-pagination">{{ $submissions->links('components.navigation.pagination') }}</div>@endif
    </section>
</div>
@endsection
