@extends('layouts.teacher')

@section('title','آزمون جدید | شیخان')
@section('header-title','طراحی برگه آزمون')

@section('content')
<div class="teacher-form-shell teacher-exam-builder-shell">
    <div class="teacher-form-head teacher-exam-page-head">
        <div>
            <span class="teacher-form-kicker">آزمون‌ساز شیخان</span>
            <h1>طراحی برگهٔ امتحانی</h1>
            <p>سؤال‌ها را مانند یک برگهٔ امتحان مرتب کن، نوع هر سؤال را انتخاب کن و کلید پاسخ یا پاسخ مرجع معلم را ثبت کن.</p>
        </div>
        <a href="{{ route('teacher.exams.index') }}" class="teacher-workspace-btn secondary">بازگشت به آزمون‌ها</a>
    </div>

    <form method="POST"
          action="{{ route('teacher.exams.store') }}"
          class="teacher-exam-builder-form"
          data-exam-builder
          data-initial-questions="{{ json_encode(old('questions', []), JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) }}">
        @csrf

        <section class="teacher-form-section teacher-exam-settings-section" aria-labelledby="exam-settings-title">
            <header class="teacher-exam-section-head">
                <div>
                    <span class="teacher-form-kicker">مرحلهٔ اول</span>
                    <h2 id="exam-settings-title">مشخصات و زمان‌بندی</h2>
                    <p>این اطلاعات بالای برگهٔ دانش‌آموز نمایش داده می‌شوند.</p>
                </div>
                <span class="teacher-exam-step-number">۰۱</span>
            </header>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <label class="teacher-form-field">
                    <span>دوره</span>
                    <select name="course_id" data-course-select required>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->title }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="teacher-form-field">
                    <span>کلاس / گروه <small>اختیاری</small></span>
                    <select name="classroom_id" data-course-classroom-select data-course-select="[name=course_id]">
                        <option value="">همهٔ دانش‌آموزان دوره</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" data-course-id="{{ $classroom->course_id }}" @selected(old('classroom_id') == $classroom->id)>{{ $classroom->title }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="teacher-form-field md:col-span-2">
                    <span>عنوان آزمون</span>
                    <input name="title" data-exam-title value="{{ old('title') }}" placeholder="مثلاً آزمون فصل‌های اول فیزیک" required maxlength="255">
                </label>

                <label class="teacher-form-field">
                    <span>مدت آزمون <small>دقیقه</small></span>
                    <input type="number" name="duration_minutes" data-exam-duration value="{{ old('duration_minutes',$exam->duration_minutes) }}" min="1" inputmode="numeric">
                </label>

                <label class="teacher-form-field">
                    <span>تعداد دفعات مجاز</span>
                    <input type="number" name="attempts_allowed" value="{{ old('attempts_allowed',$exam->attempts_allowed) }}" min="1" inputmode="numeric" required>
                </label>

                <x-teacher.jalali-datetime name="starts_at" :value="old('starts_at')" label="شروع آزمون" />
                <x-teacher.jalali-datetime name="ends_at" :value="old('ends_at')" label="پایان آزمون" />

                <label class="teacher-form-field">
                    <span>وضعیت انتشار</span>
                    <select name="status">
                        <option value="draft" @selected(old('status',$exam->status)==='draft')>پیش‌نویس</option>
                        <option value="published" @selected(old('status',$exam->status)==='published')>منتشرشده</option>
                        <option value="closed" @selected(old('status',$exam->status)==='closed')>بسته</option>
                    </select>
                </label>

                <label class="teacher-form-field md:col-span-2">
                    <span>راهنمای آزمون <small>اختیاری · برای دانش‌آموز نمایش داده می‌شود</small></span>
                    <textarea name="description" data-exam-description rows="3" placeholder="زمان‌بندی، منابع مجاز یا توضیح لازم برای دانش‌آموز...">{{ old('description') }}</textarea>
                </label>
            </div>
        </section>

        <section class="teacher-exam-editor-section" aria-labelledby="exam-paper-editor-title">
            <header class="teacher-exam-editor-toolbar">
                <div>
                    <span class="teacher-form-kicker">مرحلهٔ دوم</span>
                    <h2 id="exam-paper-editor-title">برگهٔ آزمون (نمای A4)</h2>
                    <p>برای سؤال تشریحی، پاسخ مرجع مخصوص مدرس بنویس. برای سؤال چهارگزینه‌ای، چهار گزینه و پاسخ صحیح را تعیین کن.</p>
                </div>
                <div class="teacher-exam-editor-actions">
                    <div class="teacher-exam-summary-chip">
                        <strong data-exam-question-count>۰</strong>
                        <span>سؤال</span>
                    </div>
                    <div class="teacher-exam-summary-chip is-total">
                        <strong data-exam-total-score>۰</strong>
                        <span>نمرهٔ کل</span>
                    </div>
                    <button type="button" data-exam-add-question class="teacher-workspace-btn primary">＋ افزودن سؤال</button>
                </div>
            </header>

            <div class="teacher-exam-paper-viewport">
                <div class="teacher-exam-paper-sheet" data-exam-paper>
                    <header class="teacher-exam-sheet-heading">
                        <div class="teacher-exam-sheet-brand">
                            <span class="teacher-exam-sheet-mark" aria-hidden="true">ش</span>
                            <div>
                                <span>شیخان · برگهٔ ارزیابی</span>
                                <h3 data-exam-preview-title>{{ old('title') ?: 'عنوان آزمون شما' }}</h3>
                                <p data-exam-preview-course>دورهٔ انتخاب‌شده</p>
                            </div>
                        </div>
                        <div class="teacher-exam-sheet-student-fields">
                            <div><span>نام و نام خانوادگی</span><i></i></div>
                            <div><span>تاریخ</span><i></i></div>
                        </div>
                    </header>

                    <div class="teacher-exam-sheet-facts">
                        <div><span>تعداد سؤال</span><strong data-exam-question-count>۰</strong></div>
                        <div><span>نمرهٔ کل</span><strong data-exam-total-score>۰</strong></div>
                        <div><span>زمان پاسخ‌گویی</span><strong data-exam-preview-duration>۶۰ دقیقه</strong></div>
                    </div>

                    <p class="teacher-exam-sheet-note">به متن هر سؤال دقت کن و پاسخ را در فضای مشخص‌شده بنویس. برای سؤال چهارگزینه‌ای فقط گزینهٔ درست را انتخاب کن.</p>

                    <div class="teacher-exam-questions" data-exam-questions></div>

                    <footer class="teacher-exam-sheet-footer">
                        <span>پایان برگه</span>
                        <span>شیخان · فضای طراحی آزمون</span>
                    </footer>
                </div>
            </div>
        </section>

        @if($errors->any())
            <div class="teacher-workspace-alert error" role="alert">
                <strong>برگه نیاز به اصلاح دارد.</strong>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="teacher-exam-final-actions">
            <a href="{{ route('teacher.exams.index') }}" class="teacher-workspace-btn secondary">انصراف</a>
            <button type="submit" class="teacher-workspace-btn primary">ذخیره و ساخت آزمون</button>
        </div>

        <template data-exam-question-template>
            <article class="teacher-exam-question-editor" data-exam-question>
                <header class="teacher-exam-question-editor-head">
                    <div class="teacher-exam-question-editor-heading">
                        <span class="teacher-exam-question-number" data-question-number>سؤال ۱</span>
                        <span class="teacher-exam-question-kind" data-question-kind>تشریحی</span>
                    </div>
                    <button type="button" data-exam-remove-question class="teacher-exam-remove-question">حذف سؤال</button>
                </header>

                <label class="teacher-form-field teacher-exam-question-prompt">
                    <span>متن سؤال</span>
                    <textarea data-name="question" rows="3" required maxlength="5000" placeholder="صورت سؤال را روشن و دقیق بنویس..."></textarea>
                </label>

                <div class="teacher-exam-question-config">
                    <label class="teacher-form-field">
                        <span>نوع سؤال</span>
                        <select data-name="type">
                            <option value="text">تشریحی · پاسخ نوشتنی</option>
                            <option value="single">چهارگزینه‌ای · فقط یک پاسخ صحیح</option>
                            <option value="multiple" hidden data-legacy-question-type>چندپاسخی · سؤال قدیمی</option>
                            <option value="checkbox" hidden data-legacy-question-type>چندپاسخی · سؤال قدیمی</option>
                        </select>
                    </label>
                    <label class="teacher-form-field teacher-exam-score-field">
                        <span>نمرهٔ این سؤال</span>
                        <input data-name="score" type="number" value="1" min="0" max="999999.99" step="0.25" inputmode="decimal" required>
                    </label>
                </div>

                <fieldset class="teacher-exam-option-editor" data-question-options>
                    <legend>چهار گزینهٔ پاسخ</legend>
                    <p>چهار گزینه را کامل کن؛ سپس گزینهٔ صحیح را از همین فهرست انتخاب کن.</p>
                    <div class="teacher-exam-option-fields">
                        @foreach(['الف','ب','ج','د'] as $optionIndex => $optionLabel)
                            <label class="teacher-exam-option-field">
                                <span>{{ $optionLabel }}</span>
                                <input type="text" data-option-entry data-option-index="{{ $optionIndex }}" maxlength="500" placeholder="متن گزینهٔ {{ $optionLabel }}">
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="teacher-exam-answer-key" data-question-correct>
                    <div>
                        <span data-correct-answer-label>کلید پاسخ</span>
                        <small data-correct-answer-help>بعد از وارد کردن چهار گزینه، پاسخ صحیح را انتخاب کن.</small>
                    </div>
                    <div class="teacher-exam-answer-key-choices" data-correct-answer-choices></div>
                </div>

                <label class="teacher-form-field teacher-exam-reference-answer" data-question-reference>
                    <span>پاسخ مرجع معلم <small>فقط برای راهنمایی در تصحیح؛ به دانش‌آموز نشان داده نمی‌شود.</small></span>
                    <textarea data-name="expected_answer" rows="3" maxlength="15000" placeholder="پاسخ نمونه، مراحل حل یا نکته‌هایی که باید در پاسخ دانش‌آموز دیده شود..."></textarea>
                </label>

                <footer class="teacher-exam-question-editor-foot">
                    <span data-question-foot-note>دانش‌آموز برای این سؤال فضای نوشتن پاسخ خواهد داشت.</span>
                </footer>
            </article>
        </template>
    </form>
</div>
@endsection
