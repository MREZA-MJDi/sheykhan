@extends('layouts.owner')

@section('title', $exam->exists ? 'ویرایش آزمون | شیخان' : 'ساخت آزمون | شیخان')
@section('header-title', $exam->exists ? 'ویرایش آزمون' : 'ساخت آزمون')

@push('styles')
    @vite(['resources/css/teacher-workspace.css'])
@endpush

@push('scripts')
    @vite(['resources/js/exam-builder.js'])
@endpush

@section('content')
<div class="teacher-form-shell">
    <header class="teacher-form-head">
        <div>
            <span class="text-xs font-black text-[var(--panel-primary)]">ارزیابی آموزشی · کارتابل ادمین / منشی</span>
            <h1>{{ $exam->exists ? 'ویرایش آزمون' : 'ساخت آزمون برای یک دوره' }}</h1>
            <p>آزمون به دوره‌ی همین آموزشگاه متصل می‌شود. برای حفظ نتایج، پس از اولین تلاش دانش‌آموز امکان تغییر سؤال‌ها بسته می‌شود.</p>
        </div>
        <a href="{{ route('owner.exams.index', $academy) }}" class="text-xs font-black text-[var(--panel-primary)]">بازگشت به آزمون‌ها ←</a>
    </header>

    @if($courses->isEmpty())
        <section class="teacher-form-section">
            <h2>هنوز دوره‌ی قابل انتخاب وجود ندارد</h2>
            <p>برای ساخت آزمون، ابتدا یک دوره ایجاد کنید و دست‌کم یک مدرس را به آن تخصیص دهید.</p>
            <a class="mt-4 inline-flex min-h-10 items-center rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white" href="{{ route('owner.courses.index') }}">رفتن به دوره‌ها</a>
        </section>
    @else
        @if($exam->exists)
            <form method="POST" action="{{ route('owner.exams.update', [$academy, $exam->id]) }}" class="grid gap-4" data-exam-builder data-initial-questions="{{ json_encode(old('questions', $initialQuestions), JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) }}">
                @csrf
                @method('PATCH')
        @else
            <form method="POST" action="{{ route('owner.exams.store', $academy) }}" class="grid gap-4" data-exam-builder data-initial-questions="{{ json_encode(old('questions', $initialQuestions), JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) }}">
                @csrf
        @endif
            <section class="teacher-form-section">
                <h2>مشخصات و محدوده آزمون</h2>
                <p>دوره و کلاس به‌صورت سمت سرور بررسی می‌شوند؛ شناسه‌ی متعلق به آموزشگاه دیگر پذیرفته نمی‌شود.</p>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="teacher-form-field">
                        <span>دوره</span>
                        <select name="course_id" data-course-select required>
                            <option value="">انتخاب دوره</option>
                            @foreach($courses as $course)
                                @php($primaryTeacher = $course->teachers->first())
                                <option value="{{ $course->id }}" @selected((string) old('course_id', $exam->course_id) === (string) $course->id)>{{ $course->title }}@if($primaryTeacher) — مدرس: {{ $primaryTeacher->name }}@endif</option>
                            @endforeach
                        </select>
                        <small class="owner-field-help">آزمون به این دوره تعلق می‌گیرد؛ دوره‌های خارج از آموزشگاه در فهرست پذیرفته نمی‌شوند.</small>
                        @error('course_id')<small class="text-rose-600">{{ $message }}</small>@enderror
                    </label>
                    <label class="teacher-form-field">
                        <span>کلاس / گروه <small class="text-slate-400">اختیاری</small></span>
                        <select name="classroom_id" data-course-classroom-select data-course-select="[name=course_id]">
                            <option value="">همه دانش‌آموزان دوره</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" data-course-id="{{ $classroom->course_id }}" @selected((string) old('classroom_id', $exam->classroom_id) === (string) $classroom->id)>{{ $classroom->title }} — {{ $classroom->course?->title }}</option>
                            @endforeach
                        </select>
                        <small class="owner-field-help">با انتخاب کلاس، آزمون به همان گروه محدود می‌شود؛ خالی گذاشتن آن دامنهٔ دوره را نگه می‌دارد.</small>
                        @error('classroom_id')<small class="text-rose-600">{{ $message }}</small>@enderror
                    </label>
                    <label class="teacher-form-field md:col-span-2">
                        <span>عنوان آزمون</span>
                        <input name="title" value="{{ old('title', $exam->title) }}" maxlength="255" required placeholder="مثلاً آزمون فصل اول علوم">
                        @error('title')<small class="text-rose-600">{{ $message }}</small>@enderror
                    </label>
                    <label class="teacher-form-field"><span>مدت آزمون (دقیقه)</span><input type="number" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes ?? 60) }}" min="0" max="1440"><small class="owner-field-help">مدت را به دقیقه وارد کن؛ صفر یعنی محدودیت زمانی تعریف نشده است.</small></label>
                    <label class="teacher-form-field"><span>دفعات مجاز</span><input type="number" name="attempts_allowed" value="{{ old('attempts_allowed', $exam->attempts_allowed ?? 1) }}" min="1" max="255" required><small class="owner-field-help">تعداد تلاش مجاز هر دانش‌آموز را تعیین می‌کند؛ حداقل یک بار لازم است.</small></label>
                    <label class="teacher-form-field"><span>شروع آزمون (اختیاری)</span><input type="datetime-local" name="starts_at" value="{{ old('starts_at', $exam->starts_at?->format('Y-m-d\TH:i')) }}"><small class="owner-field-help">قبل از این زمان، آزمون برای شروع در دسترس نخواهد بود.</small></label>
                    <label class="teacher-form-field"><span>پایان آزمون (اختیاری)</span><input type="datetime-local" name="ends_at" value="{{ old('ends_at', $exam->ends_at?->format('Y-m-d\TH:i')) }}">@error('ends_at')<small class="text-rose-600">{{ $message }}</small>@enderror<small class="owner-field-help">در صورت تعیین پایان، باید بعد از زمان شروع باشد.</small></label>
                    <label class="teacher-form-field">
                        <span>وضعیت انتشار</span>
                        <select name="status">
                            <option value="draft" @selected(old('status', $exam->status ?? 'draft') === 'draft')>پیش‌نویس</option>
                            <option value="published" @selected(old('status', $exam->status ?? 'draft') === 'published')>منتشرشده</option>
                            <option value="closed" @selected(old('status', $exam->status ?? 'draft') === 'closed')>بسته</option>
                        </select>
                        <small class="text-slate-500">برای انتشار، حداقل یک سؤال معتبر لازم است.</small>
                    </label>
                    <label class="teacher-form-field md:col-span-2"><span>توضیحات و راهنمای دانش‌آموز (اختیاری)</span><textarea name="description" rows="4" placeholder="زمان‌بندی، منابع مجاز یا توضیح موردنیاز را بنویسید.">{{ old('description', $exam->description) }}</textarea><small class="owner-field-help">دستورهای لازم پیش از شروع، منابع مجاز و قواعد آزمون را برای دانش‌آموز بنویس.</small></label>
                </div>
            </section>

            <section class="teacher-form-section">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2>سؤال‌های آزمون</h2>
                        <p>سؤال تشریحی برای تصحیح دستی است؛ سؤال تک‌گزینه‌ای و چندپاسخی با پاسخ صحیح ثبت می‌شوند.</p>
                        <p class="mt-2 text-xs leading-6 text-slate-500">برای سؤال گزینه‌ای، هر گزینه را در یک خط بنویسید و پاسخ صحیح را از بین همان گزینه‌ها انتخاب کنید.</p>
                    </div>
                    <button type="button" data-exam-add-question class="rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-black text-white">+ افزودن سؤال</button>
                </div>
                <div class="mt-5 grid gap-4" data-exam-questions></div>
                <template data-exam-question-template>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4" data-exam-question>
                        <div class="mb-4 flex items-center justify-between"><strong class="text-xs">سؤال جدید</strong><button type="button" data-exam-remove-question class="text-xs font-bold text-rose-600">حذف سؤال</button></div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="teacher-form-field md:col-span-2"><span>متن سؤال</span><textarea data-name="question" rows="3" required maxlength="5000" placeholder="متن سؤال را وارد کنید..."></textarea><small class="owner-field-help">سؤال را کامل و بدون وابستگی به توضیح خارج از آزمون بنویس.</small></label>
                            <label class="teacher-form-field"><span>نوع سؤال</span><select data-name="type"><option value="text">تشریحی</option><option value="single">تک‌گزینه‌ای</option><option value="multiple">چندپاسخی</option></select></label>
                            <label class="teacher-form-field"><span>نمره</span><input data-name="score" type="number" value="1" min="0" step="0.25"><small class="owner-field-help">سهم این سؤال از نمرهٔ نهایی است؛ گام اعشار ۰٫۲۵ است.</small></label>
                            <label class="teacher-form-field md:col-span-2" data-question-options><span>گزینه‌ها <small class="text-slate-400">هر گزینه در یک خط</small></span><textarea data-name="options_text" rows="4" placeholder="گزینه اول&#10;گزینه دوم&#10;گزینه سوم"></textarea></label>
                            <div class="teacher-form-field md:col-span-2" data-question-correct><span data-correct-answer-label>پاسخ صحیح</span><div class="grid gap-2" data-correct-answer-choices></div><small data-correct-answer-help class="text-slate-500">برای سؤال تشریحی نیازی به انتخاب پاسخ صحیح نیست.</small></div>
                        </div>
                    </div>
                </template>
            </section>

            @if($errors->any())
                <div class="rounded-2xl bg-rose-50 px-4 py-3 text-xs leading-7 text-rose-700">
                    <strong>آزمون ذخیره نشد. موارد زیر را بررسی کنید:</strong>
                    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif
            <div class="teacher-form-actions"><a href="{{ route('owner.exams.index', $academy) }}">انصراف</a><button type="submit">{{ $exam->exists ? 'ذخیره تغییرات' : 'ساخت آزمون' }}</button></div>
        </form>
    @endif
</div>
@endsection
