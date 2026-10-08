@extends('layouts.teacher')
@section('title','جلسه آنلاین جدید | شیخان')
@section('header-title','ایجاد جلسه آنلاین')
@section('content')
@php($calendar = App\Support\PersianUi::calendar(now()))
<div class="teacher-live-form" data-live-class-form
     data-jalali-year="{{ $calendar['year'] }}"
     data-jalali-month="{{ $calendar['month'] }}"
     data-jalali-day="{{ $calendar['day'] }}">
    <div class="teacher-live-form-head">
        <div>
            <span>برنامه‌ریزی کلاس</span>
            <h1>جلسه آنلاین جدید</h1>
            <p>فقط اطلاعاتی را وارد کن که دانش‌آموز برای شرکت در جلسه واقعاً به آن نیاز دارد.</p>
        </div>
        <a href="{{ route('teacher.live-classes.index') }}">بازگشت به جلسات ←</a>
    </div>

    <form method="POST" action="{{ route('teacher.live-classes.store') }}" class="teacher-live-form-grid">
        @csrf

        <section class="teacher-live-form-section teacher-live-form-section-wide">
            <div class="teacher-live-form-section-head">
                <span>۱</span>
                <div><h2>این جلسه برای کدام دوره است؟</h2><p>دوره را انتخاب کن تا جلسه به مسیر آموزشی درست وصل شود.</p></div>
            </div>
            <label>
                <span>دوره</span>
                <select name="course_id" required>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>کلاس / گروه <small>اختیاری</small></span>
                <select name="classroom_id">
                    <option value="">برای همه دانش‌آموزان دوره</option>
                    @foreach($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" @selected(old('classroom_id') == $classroom->id)>{{ $classroom->title }}</option>
                    @endforeach
                </select>
                <em>اگر کلاس مشخصی انتخاب کنی، فقط اعضای همان کلاس جلسه را می‌بینند.</em>
            </label>
        </section>

        <section class="teacher-live-form-section">
            <div class="teacher-live-form-section-head">
                <span>۲</span>
                <div><h2>جلسه چه زمانی برگزار می‌شود؟</h2><p>تاریخ را شمسی انتخاب کن؛ ساعت به وقت محلی سامانه ثبت می‌شود.</p></div>
            </div>
            <div class="teacher-live-date-row">
                <label>
                    <span>تاریخ برگزاری</span>
                    <button type="button" class="teacher-jalali-trigger" data-jalali-trigger>
                        <b data-jalali-label>انتخاب تاریخ</b><i>▾</i>
                    </button>
                    <input type="hidden" name="scheduled_date_jalali" data-jalali-date value="{{ old('scheduled_date_jalali') }}">
                </label>
                <label>
                    <span>ساعت شروع</span>
                    <input type="time" name="scheduled_time" data-scheduled-time value="{{ old('scheduled_time') }}" required>
                </label>
            </div>
            <input type="hidden" name="scheduled_at" data-scheduled-at value="{{ old('scheduled_at') }}">
            <div class="teacher-jalali-picker" data-jalali-picker hidden>
                <div class="teacher-jalali-picker-head">
                    <button type="button" data-jalali-prev aria-label="ماه قبل">‹</button>
                    <strong data-jalali-month-label></strong>
                    <button type="button" data-jalali-next aria-label="ماه بعد">›</button>
                </div>
                <div class="teacher-jalali-week"><span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span>ج</span></div>
                <div class="teacher-jalali-days" data-jalali-days></div>
            </div>
        </section>

        <section class="teacher-live-form-section">
            <div class="teacher-live-form-section-head">
                <span>۳</span>
                <div><h2>دانش‌آموز از کجا وارد شود؟</h2><p>ارائه‌دهنده یعنی سرویسی که لینک جلسه از آن ساخته شده؛ نه نام شخص یا مدرس.</p></div>
            </div>
            <label>
                <span>سرویس جلسه</span>
                <select name="provider" data-provider>
                    <option value="">انتخاب سرویس</option>
                    <option value="Jitsi" @selected(old('provider') === 'Jitsi')>Jitsi</option>
                    <option value="Google Meet" @selected(old('provider') === 'Google Meet')>Google Meet</option>
                    <option value="Zoom" @selected(old('provider') === 'Zoom')>Zoom</option>
                    <option value="سایر" @selected(old('provider') === 'سایر')>سایر</option>
                </select>
                <em>مثلاً اگر جلسه را در Google Meet ساخته‌ای، همین گزینه را انتخاب کن.</em>
            </label>
            <label>
                <span>لینک ورود دانش‌آموزان</span>
                <input type="url" name="meeting_url" value="{{ old('meeting_url') }}" placeholder="https://meet.google.com/..." autocomplete="url">
                <em>این همان لینکی است که دانش‌آموز با زدن «ورود به جلسه» باز می‌کند.</em>
            </label>
        </section>

        <section class="teacher-live-form-section">
            <div class="teacher-live-form-section-head">
                <span>۴</span>
                <div><h2>جلسه را برای دانش‌آموز قابل تشخیص کن</h2><p>عنوان و مدت کوتاه و واضح انتخاب کن.</p></div>
            </div>
            <label>
                <span>عنوان جلسه</span>
                <input name="title" value="{{ old('title') }}" placeholder="مثلاً: حل تمرین فصل سوم" required>
            </label>
            <label>
                <span>مدت جلسه</span>
                <div class="teacher-duration-field"><input type="number" name="duration_minutes" value="{{ old('duration_minutes', $liveClass->duration_minutes) }}" min="1" max="1440" required><b>دقیقه</b></div>
            </label>
            <label class="teacher-live-description">
                <span>توضیحات برای دانش‌آموز <small>اختیاری</small></span>
                <textarea name="description" rows="4" placeholder="مثلاً: قبل از شروع، صفحات ۲۵ تا ۳۰ را مرور کنید.">{{ old('description') }}</textarea>
            </label>
        </section>

        @if($errors->any())
            <div class="teacher-live-form-errors">
                @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif

        <div class="teacher-live-form-actions">
            <a href="{{ route('teacher.live-classes.index') }}">انصراف</a>
            <button type="submit">ثبت و برنامه‌ریزی جلسه</button>
        </div>
    </form>
</div>
@endsection