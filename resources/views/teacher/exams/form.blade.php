@extends('layouts.teacher')
@section('title','ساخت آزمون | شیخان')
@section('header-title','طراحی آزمون')

@section('content')
<div class="teacher-exam-builder">
    <form method="POST" action="{{ route('teacher.exams.store') }}" class="grid gap-5" data-exam-builder>
        @csrf

        @if($errors->any())
            <div class="teacher-builder-alert danger">
                <strong>آزمون ساخته نشد.</strong>
                @foreach($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        @endif

        <section class="teacher-detail-panel">
            <header>
                <div>
                    <span class="teacher-kicker">EXAM SETUP</span>
                    <h3>مشخصات آزمون</h3>
                </div>
            </header>

            <div class="teacher-editor-grid exam">
                <label>
                    <span>دوره</span>
                    <select name="course_id" required data-course-scope-select data-classroom-target="exam-classroom">
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>کلاس</span>
                    <select name="classroom_id" id="exam-classroom">
                        <option value="">عمومی دوره</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" data-course-id="{{ $classroom->course_id }}">{{ $classroom->title }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="wide">
                    <span>عنوان آزمون</span>
                    <input name="title" required>
                </label>

                <label>
                    <span>مدت (دقیقه)</span>
                    <input type="number" name="duration_minutes" value="{{ $exam->duration_minutes }}" min="1">
                </label>

                <label>
                    <span>دفعات مجاز</span>
                    <input type="number" name="attempts_allowed" value="{{ $exam->attempts_allowed }}" min="1" max="255">
                </label>

                <label>
                    <span>شروع</span>
                    <input type="datetime-local" name="starts_at">
                </label>

                <label>
                    <span>پایان</span>
                    <input type="datetime-local" name="ends_at">
                </label>

                <label>
                    <span>وضعیت</span>
                    <select name="status">
                        <option value="draft">پیش‌نویس</option>
                        <option value="published">منتشر</option>
                        <option value="closed">بسته</option>
                    </select>
                </label>

                <label class="wide">
                    <span>توضیحات و دستورالعمل</span>
                    <textarea name="description" rows="5"></textarea>
                </label>
            </div>
        </section>

        <section class="teacher-detail-panel">
            <header>
                <div>
                    <span class="teacher-kicker">QUESTION BUILDER</span>
                    <h3>طراحی سؤال</h3>
                    <p class="teacher-exam-help">سؤال‌های عینی خودکار تصحیح می‌شوند؛ سؤال تشریحی برای بررسی دستی علامت‌گذاری می‌شود.</p>
                </div>

                <button type="button" data-exam-add-question class="teacher-builder-btn primary">+ سؤال جدید</button>
            </header>

            <div class="teacher-question-list" data-exam-questions></div>

            <template data-exam-question-template>
                <article class="teacher-question-card" data-exam-question>
                    <div class="teacher-question-card-head">
                        <span class="teacher-question-number">سؤال</span>
                        <button type="button" data-exam-remove-question>حذف</button>
                    </div>

                    <div class="teacher-editor-grid exam">
                        <label class="wide">
                            <span>متن سؤال</span>
                            <textarea data-name="question" rows="4" required></textarea>
                        </label>

                        <label>
                            <span>نوع سؤال</span>
                            <select data-name="type">
                                <option value="text">تشریحی</option>
                                <option value="single">تک‌گزینه‌ای</option>
                                <option value="multiple">چندگزینه‌ای</option>
                                <option value="checkbox">چند پاسخ صحیح</option>
                            </select>
                        </label>

                        <label>
                            <span>نمره</span>
                            <input data-name="score" type="number" value="1" min="0" step="0.25">
                        </label>

                        <label class="wide" data-options-field>
                            <span>گزینه‌ها</span>
                            <textarea data-name="options_text" rows="4" placeholder="هر گزینه در یک خط"></textarea>
                        </label>

                        <label class="wide" data-single-answer-field>
                            <span>پاسخ صحیح</span>
                            <input data-name="correct_answer" placeholder="متن دقیق پاسخ">
                        </label>

                        <label class="wide hidden" data-multiple-answer-field>
                            <span>پاسخ‌های صحیح</span>
                            <textarea data-correct-options-text rows="3" placeholder="هر پاسخ صحیح در یک خط"></textarea>
                        </label>
                    </div>
                </article>
            </template>
        </section>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('teacher.exams.index') }}" class="teacher-builder-btn ghost" style="color:#5c6577;border-color:#dfe3ea">انصراف</a>
            <button class="teacher-builder-btn primary" style="background:#5b5ce8;color:#fff">ساخت آزمون</button>
        </div>
    </form>
</div>
@endsection
