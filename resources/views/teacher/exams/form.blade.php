@extends('layouts.teacher')

@section('title', ($exam->exists ? 'ویرایش آزمون' : 'ساخت آزمون') . ' | شیخان')
@section('header-title', $exam->exists ? 'ویرایش آزمون' : 'طراحی آزمون')

@section('content')
@php
    $selectedCourse = old('course_id', $exam->course_id);
    $selectedClassroom = old('classroom_id', $exam->classroom_id);
    $existingQuestions = old('questions', $exam->exists ? $exam->questions->map(function ($question) {
        return [
            'type' => $question->type,
            'question' => $question->question,
            'options' => $question->options ?? [],
            'correct_answer' => $question->correct_answer ?? null,
            'score' => $question->score,
        ];
    })->values()->all() : []);
@endphp

<div class="teacher-exam-builder">
    <form
        method="POST"
        action="{{ $exam->exists ? route('teacher.exams.update', $exam) : route('teacher.exams.store') }}"
        class="grid gap-5"
        data-exam-builder
        data-existing-questions="{{ count($existingQuestions) }}"
    >
        @csrf
        @if($exam->exists)
            @method('PATCH')
        @endif

        @if($errors->any())
            <div class="teacher-builder-alert danger">
                <strong>آزمون ذخیره نشد.</strong>
                @foreach($errors->all() as $error)
                    <span>{{ $error }}</span>
                @endforeach
            </div>
        @endif

        <section class="teacher-detail-panel">
            <header>
                <div>
                    <span class="teacher-kicker">EXAM SETUP</span>
                    <h3>{{ $exam->exists ? 'تنظیمات آزمون' : 'مشخصات آزمون' }}</h3>
                </div>
            </header>

            <div class="teacher-editor-grid exam">
                <label>
                    <span>دوره</span>
                    <select name="course_id" required data-course-scope-select data-classroom-target="exam-classroom">
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected((string)$selectedCourse === (string)$course->id)>{{ $course->title }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>کلاس</span>
                    <select name="classroom_id" id="exam-classroom">
                        <option value="">عمومی دوره</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" data-course-id="{{ $classroom->course_id }}" @selected((string)$selectedClassroom === (string)$classroom->id)>{{ $classroom->title }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="wide">
                    <span>عنوان آزمون</span>
                    <input name="title" required value="{{ old('title', $exam->title) }}">
                </label>

                <label>
                    <span>مدت (دقیقه)</span>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" min="0">
                </label>

                <label>
                    <span>دفعات مجاز</span>
                    <input type="number" name="attempts_allowed" value="{{ old('attempts_allowed', $exam->attempts_allowed) }}" min="1" max="255">
                </label>

                <label>
                    <span>شروع</span>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $exam->starts_at?->format('Y-m-d\TH:i')) }}">
                </label>

                <label>
                    <span>پایان</span>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $exam->ends_at?->format('Y-m-d\TH:i')) }}">
                </label>

                <label>
                    <span>وضعیت</span>
                    <select name="status">
                        <option value="draft" @selected(old('status', $exam->status) === 'draft')>پیش‌نویس</option>
                        <option value="published" @selected(old('status', $exam->status) === 'published')>منتشر</option>
                        <option value="closed" @selected(old('status', $exam->status) === 'closed')>بسته</option>
                    </select>
                </label>

                <label class="wide">
                    <span>توضیحات و دستورالعمل</span>
                    <textarea name="description" rows="5">{{ old('description', $exam->description) }}</textarea>
                </label>
            </div>
        </section>

        @if($exam->exists && $exam->attempts()->exists())
            <div class="teacher-builder-alert danger">
                <strong>این آزمون attempt ثبت‌شده دارد.</strong>
                <span>برای حفظ نتیجه‌های قبلی، ساختار سؤال‌ها قفل شده و قبل از شروع پاسخ‌دهی باید ویرایش شود.</span>
            </div>
        @endif

        <section class="teacher-detail-panel">
            <header>
                <div>
                    <span class="teacher-kicker">QUESTION BUILDER</span>
                    <h3>طراحی سؤال</h3>
                    <p class="teacher-exam-help">سؤال‌های عینی خودکار تصحیح می‌شوند؛ سؤال تشریحی برای بررسی دستی علامت‌گذاری می‌شود.</p>
                </div>

                <button type="button" data-exam-add-question class="teacher-builder-btn primary">+ سؤال جدید</button>
            </header>

            <div class="teacher-question-list" data-exam-questions>
                @foreach($existingQuestions as $questionIndex => $question)
                    <article class="teacher-question-card" data-exam-question>
                        <div class="teacher-question-card-head">
                            <span class="teacher-question-number">سؤال {{ $questionIndex + 1 }}</span>
                            <button type="button" data-exam-remove-question>حذف</button>
                        </div>

                        <div class="teacher-editor-grid exam">
                            <label class="wide">
                                <span>متن سؤال</span>
                                <textarea data-name="question" rows="4" required>{{ $question['question'] ?? '' }}</textarea>
                            </label>

                            <label>
                                <span>نوع سؤال</span>
                                <select data-name="type">
                                    <option value="text" @selected(($question['type'] ?? 'text') === 'text')>تشریحی</option>
                                    <option value="single" @selected(($question['type'] ?? '') === 'single')>تک‌گزینه‌ای</option>
                                    <option value="multiple" @selected(($question['type'] ?? '') === 'multiple')>چندگزینه‌ای</option>
                                    <option value="checkbox" @selected(($question['type'] ?? '') === 'checkbox')>چند پاسخ صحیح</option>
                                </select>
                            </label>

                            <label>
                                <span>نمره</span>
                                <input data-name="score" type="number" value="{{ $question['score'] ?? 1 }}" min="0" step="0.25">
                            </label>

                            <label class="wide" data-options-field>
                                <span>گزینه‌ها</span>
                                <textarea data-name="options_text" rows="4">{{ implode("\n", (array)($question['options'] ?? [])) }}</textarea>
                            </label>

                            <label class="wide" data-single-answer-field>
                                <span>پاسخ صحیح</span>
                                <input data-name="correct_answer" value="{{ is_array($question['correct_answer'] ?? null) ? '' : ($question['correct_answer'] ?? '') }}" placeholder="متن دقیق پاسخ">
                            </label>

                            <label class="wide hidden" data-multiple-answer-field>
                                <span>پاسخ‌های صحیح</span>
                                <textarea data-correct-options-text rows="3">{{ is_array($question['correct_answer'] ?? null) ? implode("\n", $question['correct_answer']) : '' }}</textarea>
                            </label>
                        </div>
                    </article>
                @endforeach
            </div>

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
            <button class="teacher-builder-btn primary" style="background:#5b5ce8;color:#fff">{{ $exam->exists ? 'ذخیره آزمون' : 'ساخت آزمون' }}</button>
        </div>
    </form>
</div>
@endsection
