@extends('layouts.student')

@section('title', $assignment->title)
@section('header-title', 'جزئیات تکلیف')

@section('content')
@php $submission = $assignment->submissions->sortByDesc('created_at')->first(); @endphp
<section class="page">
    <div class="card">
        <div class="chead"><h2>{{ $assignment->title }}</h2><a class="pill" href="{{ route('student.assignments.index') }}">بازگشت به تکالیف</a></div>
        <div class="sum">
            <span class="tag">{{ $assignment->course?->title ?? 'دوره' }}</span>
            @if($assignment->classroom)<span class="tag">{{ $assignment->classroom->title }}</span>@endif
            <span class="tag">حداکثر نمره: {{ \App\Support\PersianUi::number($assignment->max_score ?? 0, 1) }}</span>
            <span class="tag {{ $submission?->graded_at ? 'ok' : ($submission ? 'd' : 'w') }}">
                {{ $submission?->graded_at ? 'تصحیح‌شده' : ($submission ? 'ارسال‌شده؛ در انتظار بررسی' : 'ارسال نشده') }}
            </span>
        </div>
        <p class="note">{{ $assignment->instructions ?: 'برای این تکلیف توضیح جداگانه‌ای ثبت نشده است.' }}</p>
        <div class="frm"><span>مهلت ارسال: {{ $assignment->due_at ? \App\Support\PersianUi::dateTime($assignment->due_at) : 'بدون مهلت مشخص' }}</span></div>

        @if($assignment->media->isNotEmpty())
            <div class="chead" style="margin-top:18px"><h2>فایل‌های ضمیمهٔ تکلیف</h2></div>
            @foreach($assignment->media as $media)
                <div class="trk">
                    <div class="th"><svg class="i" aria-hidden="true"><use href="#i-file"/></svg></div>
                    <div><b>{{ $media->original_name }}</b><span>{{ strtoupper($media->extension ?? '') }} · {{ \App\Support\PersianUi::number(((int) $media->size) / 1024) }} کیلوبایت</span></div>
                    <a class="btn s" href="{{ route('media.view', $media->id) }}">مشاهده فایل</a>
                </div>
            @endforeach
        @endif
    </div>

    @if($submission)
        <div class="card">
            <div class="chead"><h2>آخرین پاسخ تو</h2><span class="tag {{ $submission->graded_at ? 'ok' : 'd' }}">{{ $submission->graded_at ? 'تصحیح‌شده' : 'در انتظار تصحیح' }}</span></div>
            <p class="note">{{ $submission->content ?: 'برای پاسخ متنی، توضیحی ثبت نشده است؛ فایل‌های ارسالی در ادامه نمایش داده می‌شوند.' }}</p>
            <div class="sum">
                <span class="tag">زمان ارسال: {{ \App\Support\PersianUi::dateTime($submission->submitted_at) }}</span>
                @if($submission->score !== null)<span class="tag ok">نمره: {{ \App\Support\PersianUi::number($submission->score, 1) }}</span>@endif
            </div>
            @if($submission->feedback)<div class="note"><strong>بازخورد مدرس:</strong><br>{{ $submission->feedback }}</div>@endif
            @foreach($submission->media as $media)
                <div class="trk">
                    <div class="th"><svg class="i" aria-hidden="true"><use href="#i-file"/></svg></div>
                    <div><b>{{ $media->original_name }}</b><span>{{ \App\Support\PersianUi::number(((int) $media->size) / 1024) }} کیلوبایت</span></div>
                    <a class="btn s" href="{{ route('media.view', $media->id) }}">مشاهده فایل</a>
                </div>
            @endforeach
        </div>
    @endif

    @if($canSubmit)
        <form class="card" method="POST" action="{{ route('student.assignments.submit', $assignment->id) }}" enctype="multipart/form-data" data-upload-progress>
            @csrf
            <div class="chead"><h2>{{ $submission ? 'ارسال نسخهٔ اصلاح‌شده' : 'ارسال پاسخ تکلیف' }}</h2><span class="tag w">تا پایان مهلت</span></div>
            <label for="assignment-content" style="display:block;font-size:12px;margin-bottom:8px">توضیح یا پاسخ متنی</label>
            <textarea class="fld" id="assignment-content" name="content" rows="5" maxlength="30000" placeholder="پاسخ خودت را اینجا بنویس...">{{ old('content', $submission?->content) }}</textarea>
            <div class="assignment-upload-box">
                <div class="assignment-upload-heading">
                    <span class="assignment-upload-heading-icon" aria-hidden="true">⇧</span>
                    <span>فایل پاسخ خودت را پیوست کن</span>
                </div>
                <label for="assignment-files" style="display:block;font-size:12px;font-weight:800;margin-top:4px">انتخاب فایل (PDF و سایر فرمت‌های مجاز)</label>
                <input class="fld" id="assignment-files" data-assignment-file-upload type="file" name="attachments[]" multiple accept=".pdf,.txt,.jpg,.jpeg,.png,.webp,.zip,.doc,.docx,.odt" aria-describedby="assignment-files-help assignment-files-selection">
                <p class="assignment-upload-help" id="assignment-files-help">حداکثر ۳ فایل، هرکدام تا ۱۰ مگابایت. فایل‌های پاسخ به‌صورت خصوصی ذخیره می‌شوند و فقط افراد مجاز به تکلیف دسترسی دارند.</p>
                <p class="assignment-upload-selection" id="assignment-files-selection" data-assignment-files-selection aria-live="polite">هنوز فایلی انتخاب نشده است.</p>
                <x-ui.upload-progress label="پیشرفت ارسال پاسخ تکلیف" />
            </div>
            <div class="frm"><button class="btn s" type="submit">ثبت پاسخ</button><span>پس از تصحیح مدرس، ارسال مجدد بسته می‌شود.</span></div>
        </form>
    @elseif($submission?->graded_at)
        <div class="card"><div class="note">این تکلیف تصحیح شده است و ارسال مجدد آن بسته است.</div></div>
    @else
        <div class="card"><div class="note">در حال حاضر امکان ارسال پاسخ وجود ندارد؛ ممکن است مهلت پایان یافته یا تکلیف غیرفعال شده باشد.</div></div>
    @endif
</section>
@endsection
