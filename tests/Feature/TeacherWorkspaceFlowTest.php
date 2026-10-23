<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Academy;
use App\Models\Role;
use App\Models\User;
use App\Services\CourseManagementService;
use App\Services\TeacherWorkspaceService;
use App\Services\MeetingLinkService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherWorkspaceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_workspace_entries_are_available_and_paginated(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        foreach ([
            'teacher.courses.index',
            'teacher.classrooms.index',
            'teacher.schedule.index',
            'teacher.attendance.index',
            'teacher.assignments.index',
            'teacher.exams.index',
            'teacher.live-classes.index',
        ] as $route) {
            $this->actingAs($teacher)
                ->get(route($route))
                ->assertOk();
        }

        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.content', $course))
            ->assertOk();

        $this->actingAs($teacher)
            ->get(route('teacher.courses.progress', $course))
            ->assertOk();
    }

    public function test_teacher_routes_show_only_assigned_courses_even_when_account_also_has_owner_role(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $academy = Academy::where('slug', 'sheykhan-academy')->firstOrFail();
        $academy->update(['owner_id' => $teacher->id]);

        // Reproduce the dual-role edge case that previously made the shared
        // course query return every course in an owned academy.
        $ownerRole = Role::where('slug', 'academy-owner')->firstOrFail();
        $teacher->roles()->syncWithoutDetaching([$ownerRole->id]);
        $teacher->unsetRelation('roles');

        $assignedCourse = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $unassignedCourse = Course::where('slug', 'science-foundation-8')->firstOrFail();

        $response = $this->actingAs($teacher)
            ->get(route('teacher.courses.index'))
            ->assertOk()
            ->assertSee('ریاضی پایه هفتم')
            ->assertDontSee('علوم پایه هشتم')
            ->assertDontSee('آمادگی تیزهوشان پایه نهم');

        $ids = app(\App\Services\CourseManagementService::class)
            ->coursesForTeacherPaginated($teacher)
            ->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertSame([$assignedCourse->id], $ids);

        $this->actingAs($teacher)
            ->get(route('teacher.courses.edit', $unassignedCourse))
            ->assertForbidden();
    }

    public function test_partial_course_update_preserves_fields_that_were_not_sent(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $course->update([
            'level' => 'پیشرفته',
            'short_description' => 'خلاصه اصلی',
            'description' => 'توضیح کامل اصلی',
            'duration_minutes' => 180,
        ]);

        $updated = app(CourseManagementService::class)->update(
            $teacher,
            $course,
            ['title' => 'ریاضی هفتم — عنوان اصلاح‌شده'],
        );

        $this->assertSame('پیشرفته', $updated->level);
        $this->assertSame('خلاصه اصلی', $updated->short_description);
        $this->assertSame('توضیح کامل اصلی', $updated->description);
        $this->assertSame(180, (int) $updated->duration_minutes);
    }

    public function test_exam_can_be_edited_before_attempt_and_is_locked_after_student_starts(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $classroom = Classroom::where('course_id', $course->id)
            ->whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))
            ->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('teacher.exams.store'), [
                'course_id' => $course->id,
                'classroom_id' => $classroom->id,
                'title' => 'برگهٔ قابل ویرایش - تست',
                'description' => 'راهنمای اولیه',
                'duration_minutes' => 45,
                'attempts_allowed' => 1,
                'starts_at' => now()->subMinute()->toDateTimeString(),
                'ends_at' => now()->addDay()->toDateTimeString(),
                'status' => 'draft',
                'questions' => [
                    [
                        'type' => 'text',
                        'question' => 'سؤال تشریحی اولیه',
                        'expected_answer' => 'پاسخ مرجع اولیه',
                        'score' => 2,
                    ],
                    [
                        'type' => 'single',
                        'question' => 'سؤال چهارگزینه‌ای اولیه',
                        // Drafts can be resumed before the teacher finishes the options and key.
                        'options' => ['الف', 'ب'],
                        'score' => 1,
                    ],
                ],
            ])
            ->assertRedirect(route('teacher.exams.index'))
            ->assertSessionHasNoErrors();

        $exam = \App\Models\Exam::where('title', 'برگهٔ قابل ویرایش - تست')->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.exams.edit', $exam))
            ->assertOk()
            ->assertSee('ویرایش برگهٔ امتحانی')
            ->assertSee('سؤال تشریحی اولیه')
            ->assertSee('پاسخ مرجع اولیه');

        $this->actingAs($teacher)
            ->patch(route('teacher.exams.update', $exam), [
                'course_id' => $course->id,
                'classroom_id' => $classroom->id,
                'title' => 'برگهٔ ویرایش‌شده - تست',
                'description' => 'راهنمای اصلاح‌شده',
                'duration_minutes' => 30,
                'attempts_allowed' => 1,
                'starts_at' => now()->subMinute()->toDateTimeString(),
                'ends_at' => now()->addDay()->toDateTimeString(),
                'status' => 'draft',
                'questions' => [
                    [
                        'type' => 'text',
                        'question' => 'سؤال تشریحی اصلاح‌شده',
                        'expected_answer' => 'پاسخ مرجع اصلاح‌شده',
                        'score' => 3,
                    ],
                ],
            ])
            ->assertRedirect(route('teacher.exams.index'))
            ->assertSessionHasNoErrors();

        $exam->refresh();
        $questions = $exam->questions()->orderBy('sort_order')->get();
        $this->assertSame('برگهٔ ویرایش‌شده - تست', $exam->title);
        $this->assertSame('راهنمای اصلاح‌شده', $exam->description);
        $this->assertCount(1, $questions);
        $this->assertSame('سؤال تشریحی اصلاح‌شده', $questions[0]->question);
        $this->assertSame('پاسخ مرجع اصلاح‌شده', $questions[0]->expected_answer);

        \App\Models\ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'attempt_number' => 1,
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.edit', $exam))
            ->assertRedirect(route('teacher.exams.index'))
            ->assertSessionHas('warning');

        $this->actingAs($teacher)
            ->from(route('teacher.exams.edit', $exam))
            ->patch(route('teacher.exams.update', $exam), [
                'course_id' => $course->id,
                'classroom_id' => $classroom->id,
                'title' => 'نباید تغییر کند',
                'duration_minutes' => 30,
                'attempts_allowed' => 1,
                'status' => 'draft',
                'questions' => [
                    [
                        'type' => 'text',
                        'question' => 'تغییر غیرمجاز',
                        'score' => 1,
                    ],
                ],
            ])
            ->assertRedirect(route('teacher.exams.edit', $exam))
            ->assertSessionHasErrors('exam');

        $this->assertSame('برگهٔ ویرایش‌شده - تست', $exam->fresh()->title);
        $this->assertSame('سؤال تشریحی اصلاح‌شده', $exam->questions()->firstOrFail()->question);
    }

    public function test_exam_builder_persists_essay_single_and_multi_choice_questions_correctly(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $classroom = Classroom::where('course_id', $course->id)
            ->whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))
            ->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.exams.create'))
            ->assertOk()
            ->assertSee('data-question-correct', false)
            ->assertSee('data-correct-answer-choices', false);

        $this->actingAs($teacher)
            ->post(route('teacher.exams.store'), [
                'course_id' => $course->id,
                'classroom_id' => $classroom->id,
                'title' => 'آزمون قالب‌های سؤال - تست',
                'description' => 'بررسی مسیر واقعی ذخیره و تصحیح سؤال‌ها',
                'duration_minutes' => 45,
                'attempts_allowed' => 1,
                'starts_at' => now()->subMinute()->toDateTimeString(),
                'ends_at' => now()->addDay()->toDateTimeString(),
                'status' => 'draft',
                'questions' => [
                    [
                        'type' => 'text',
                        'question' => 'راه‌حل خود را توضیح بده.',
                        'expected_answer' => 'راهنمای خصوصی آزمون: ابتدا داده‌ها را مشخص کن و سپس مراحل حل را توضیح بده.',
                        'score' => 3,
                    ],
                    [
                        'type' => 'single',
                        'question' => '۲ + ۲ چند می‌شود؟',
                        'options' => ['۳', '۴', '۵', '۶'],
                        'correct_answer' => '۴',
                        'score' => 1,
                    ],
                    [
                        'type' => 'multiple',
                        'question' => 'کدام گزینه‌ها عدد زوج‌اند؟',
                        'options' => ['۱', '۲', '۳', '۴'],
                        'correct_answer' => ['۲', '۴'],
                        'score' => 2,
                    ],
                ],
            ])
            ->assertRedirect(route('teacher.exams.index'))
            ->assertSessionHasNoErrors();

        $exam = \App\Models\Exam::where('title', 'آزمون قالب‌های سؤال - تست')->firstOrFail();
        $questions = $exam->questions()->orderBy('sort_order')->get();

        $this->assertCount(3, $questions);
        $this->assertSame('text', $questions[0]->type);
        $this->assertNull($questions[0]->options);
        $this->assertNull($questions[0]->correct_answer);
        $this->assertSame('راهنمای خصوصی آزمون: ابتدا داده‌ها را مشخص کن و سپس مراحل حل را توضیح بده.', $questions[0]->expected_answer);

        $this->assertSame('single', $questions[1]->type);
        $this->assertSame(['۳', '۴', '۵', '۶'], $questions[1]->options);
        $this->assertSame('۴', $questions[1]->correct_answer);

        $this->assertSame('multiple', $questions[2]->type);
        $this->assertSame(['۱', '۲', '۳', '۴'], $questions[2]->options);
        $this->assertSame(['۲', '۴'], json_decode($questions[2]->correct_answer, true));
    }

    public function test_essay_exam_submission_waits_for_teacher_review_then_shows_the_final_score(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $course->update(['access_type' => 'free', 'price' => 0]);

        $classroom = Classroom::where('course_id', $course->id)
            ->whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))
            ->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('teacher.exams.store'), [
                'course_id' => $course->id,
                'classroom_id' => $classroom->id,
                'title' => 'آزمون تشریحی - گردش کار کامل',
                'description' => 'یک سؤال تشریحی و یک سؤال تستی',
                'duration_minutes' => 30,
                'attempts_allowed' => 1,
                'starts_at' => now()->subMinute()->toDateTimeString(),
                'ends_at' => now()->addDay()->toDateTimeString(),
                'status' => 'published',
                'questions' => [
                    [
                        'type' => 'text',
                        'question' => 'روش حل را با توضیح بنویس.',
                        'expected_answer' => 'راهنمای خصوصی آزمون: ابتدا معادله را ساده کن و بعد جای‌گذاری کن.',
                        'score' => 2,
                    ],
                    [
                        'type' => 'single',
                        'question' => '۳ + ۴ چند می‌شود؟',
                        'options' => ['۶', '۷', '۸', '۹'],
                        'correct_answer' => '۷',
                        'score' => 1,
                    ],
                ],
            ])
            ->assertRedirect(route('teacher.exams.index'))
            ->assertSessionHasNoErrors();

        $exam = \App\Models\Exam::where('title', 'آزمون تشریحی - گردش کار کامل')->firstOrFail();
        $essay = $exam->questions()->where('type', 'text')->firstOrFail();
        $choice = $exam->questions()->where('type', 'single')->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.exams.show', $exam))
            ->assertOk()
            ->assertSee('آزمون تشریحی - گردش کار کامل')
            ->assertDontSee('راهنمای خصوصی آزمون: ابتدا معادله را ساده کن و بعد جای‌گذاری کن.');

        $this->actingAs($student)
            ->post(route('student.exams.start', $exam))
            ->assertRedirect();

        $attempt = \App\Models\ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.exams.attempt', [$exam, $attempt]))
            ->assertOk()
            ->assertSee('textarea', false)
            ->assertSee('type="radio"', false)
            ->assertDontSee('راهنمای خصوصی آزمون: ابتدا معادله را ساده کن و بعد جای‌گذاری کن.');

        // Simulate the final timer-triggered request arriving three seconds
        // after the deadline; the bounded server grace must preserve the answer sheet.
        $attempt->update(['started_at' => now()->subMinutes(30)->subSeconds(3)]);

        $this->actingAs($student)
            ->post(route('student.exam-attempts.submit', $attempt), [
                'answers' => [
                    $essay->id => 'ابتدا داده‌ها را جای‌گذاری می‌کنم و سپس نتیجه را توضیح می‌دهم.',
                    $choice->id => '۷',
                ],
            ])
            ->assertRedirect(route('student.exams.index'))
            ->assertSessionHasNoErrors();

        $attempt->refresh();
        $this->assertSame('pending_review', $attempt->status);
        $this->assertNull($attempt->score);

        $essayAnswer = $attempt->answers()->where('question_id', $essay->id)->firstOrFail();
        $choiceAnswer = $attempt->answers()->where('question_id', $choice->id)->firstOrFail();

        $this->assertSame('ابتدا داده‌ها را جای‌گذاری می‌کنم و سپس نتیجه را توضیح می‌دهم.', $essayAnswer->answer);
        $this->assertNull($essayAnswer->score);
        $this->assertTrue((bool) $choiceAnswer->is_correct);
        $this->assertSame(1.0, (float) $choiceAnswer->score);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.attempts', $exam))
            ->assertOk()
            ->assertSee('روش حل را با توضیح بنویس.')
            ->assertSee('راهنمای خصوصی آزمون: ابتدا معادله را ساده کن و بعد جای‌گذاری کن.')
            ->assertSee('کلید پاسخ صحیح')
            ->assertSee('بازبینی دستی');

        $this->actingAs($teacher)
            ->post(route('teacher.exam-attempts.grade', $attempt))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $attempt->refresh();
        $this->assertSame('needs_review', $attempt->status);
        $this->assertSame(1.0, (float) $attempt->score);

        // Provisional auto-grading must still count toward the attempt limit.
        $this->actingAs($student)
            ->post(route('student.exams.start', $exam))
            ->assertSessionHasErrors('exam');

        $this->actingAs($student)
            ->get(route('student.exams.result', $exam))
            ->assertOk()
            ->assertSee('نمرهٔ موقت تستی')
            ->assertDontSee('راهنمای خصوصی آزمون: ابتدا معادله را ساده کن و بعد جای‌گذاری کن.');

        $this->actingAs($student)
            ->get(route('student.results.index'))
            ->assertOk()
            ->assertSee('نمره موقت')
            ->assertSee('آزمون تشریحی - گردش کار کامل');

        $this->actingAs($teacher)
            ->patch(route('teacher.exam-attempts.grade-manual', $attempt), [
                'answers' => [
                    $essayAnswer->id => 2,
                    $choiceAnswer->id => 1,
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $attempt->refresh();
        $this->assertSame('graded', $attempt->status);
        $this->assertSame(3.0, (float) $attempt->score);
        $this->assertNotNull($attempt->answers()->where('question_id', $essay->id)->firstOrFail()->score);

        $this->actingAs($student)
            ->get(route('student.exams.result', $exam))
            ->assertOk()
            ->assertSee('نمرهٔ نهایی')
            ->assertSee('۳');

        // Closing the exam blocks new attempts, but must not hide the student's submitted grade.
        $exam->update(['status' => 'closed']);

        $this->actingAs($student)
            ->get(route('student.exams.show', $exam))
            ->assertNotFound();

        $this->actingAs($student)
            ->get(route('student.exams.result', $exam))
            ->assertOk()
            ->assertSee('نمرهٔ نهایی')
            ->assertSee('تصحیح کامل شد')
            ->assertSee('۳');

        $this->actingAs($student)
            ->get(route('student.results.index'))
            ->assertOk()
            ->assertSee('آزمون تشریحی - گردش کار کامل')
            ->assertSee('تصحیح‌شده');
    }

    public function test_exam_builder_requires_exactly_four_options_for_single_choice_questions(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $this->actingAs($teacher)
            ->from(route('teacher.exams.create'))
            ->post(route('teacher.exams.store'), [
                'course_id' => $course->id,
                'title' => 'آزمون چهارگزینه‌ای ناقص - تست',
                'duration_minutes' => 30,
                'attempts_allowed' => 1,
                'status' => 'draft',
                'questions' => [
                    [
                        'type' => 'single',
                        'question' => 'کدام گزینه درست است؟',
                        'options' => ['گزینه الف', 'گزینه ب', 'گزینه ج'],
                        'correct_answer' => 'گزینه ب',
                        'score' => 1,
                    ],
                ],
            ])
            ->assertSessionHasErrors('questions.0.options');

        $this->assertDatabaseMissing('exams', [
            'title' => 'آزمون چهارگزینه‌ای ناقص - تست',
        ]);
    }

    public function test_exam_builder_rejects_a_correct_answer_that_is_not_one_of_the_options(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $this->actingAs($teacher)
            ->from(route('teacher.exams.create'))
            ->post(route('teacher.exams.store'), [
                'course_id' => $course->id,
                'title' => 'آزمون پاسخ ناسازگار - تست',
                'duration_minutes' => 30,
                'attempts_allowed' => 1,
                'status' => 'draft',
                'questions' => [
                    [
                        'type' => 'single',
                        'question' => 'یک سؤال تستی',
                        'options' => ['گزینه الف', 'گزینه ب'],
                        'correct_answer' => 'گزینه ج',
                        'score' => 1,
                    ],
                ],
            ])
            ->assertSessionHasErrors('questions.0.correct_answer');

        $this->assertDatabaseMissing('exams', ['title' => 'آزمون پاسخ ناسازگار - تست']);
    }

    public function test_exam_builder_retains_question_options_after_validation_and_escapes_quotes_safely(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $this->actingAs($teacher)
            ->from(route('teacher.exams.create'))
            ->followingRedirects()
            ->post(route('teacher.exams.store'), [
                'course_id' => $course->id,
                'title' => 'آزمون حفظ وضعیت فرم - تست',
                'duration_minutes' => 30,
                'attempts_allowed' => 1,
                'status' => 'draft',
                'questions' => [
                    [
                        'type' => 'multiple',
                        'question' => "سؤال با عنوان 'ویژه' باقی بماند",
                        'options' => ['گزینه اول', 'گزینه دوم'],
                        'correct_answer' => ['گزینه خارج از فهرست'],
                        'score' => 2,
                    ],
                ],
            ])
            ->assertOk()
            ->assertSee('data-initial-questions', false)
            ->assertSee('سؤال با عنوان', false)
            ->assertSee('\u0027ویژه\u0027', false)
            ->assertSee('گزینه اول', false)
            ->assertSee('گزینه دوم', false)
            ->assertSee('تمام پاسخ‌های صحیح باید از بین گزینه‌های همین سؤال انتخاب شوند.');

        $this->assertDatabaseMissing('exams', [
            'title' => 'آزمون حفظ وضعیت فرم - تست',
        ]);
    }

    public function test_teacher_can_create_a_course_section_for_owned_course(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('teacher.courses.sections.store', $course), [
                'title' => 'سرفصل تست',
                'description' => 'توضیح تست',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('course_sections', [
            'course_id' => $course->id,
            'title' => 'سرفصل تست',
            'description' => 'توضیح تست',
        ]);
    }

    public function test_teacher_can_create_a_class_with_the_manage_permission(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('teacher.classrooms.store'), [
                'course_id' => $course->id,
                'title' => 'کلاس تست استاد',
                'code' => 'TEST-MATH-01',
                'capacity' => 20,
            ])
            ->assertRedirect(route('teacher.classrooms.index'));

        $this->assertDatabaseHas('classrooms', [
            'course_id' => $course->id,
            'title' => 'کلاس تست استاد',
            'code' => 'TEST-MATH-01',
        ]);
    }

    public function test_overlapping_weekly_schedule_is_rejected(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = Classroom::whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))->firstOrFail();

        $before = $classroom->schedules()->count();

        $classroom->schedules()->create([
            'weekday' => 6,
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.schedule.store'), [
                'classroom_id' => $classroom->id,
                'weekday' => 6,
                'provider' => 'Jitsi',
                'start_time' => '10:00',
                'end_time' => '11:00',
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertSame($before + 1, $classroom->schedules()->count());

    }

    public function test_weekly_schedule_generates_a_jitsi_meeting_link(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = Classroom::whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('teacher.schedule.store'), [
                'classroom_id' => $classroom->id,
                'weekday' => 6,
                'provider' => 'Jitsi',
                'start_time' => '09:00',
                'end_time' => '10:30',
                'room' => 'کلاس آنلاین',
            ])
            ->assertSessionHasNoErrors();

        $schedule = $classroom->schedules()->where('weekday', 6)->latest('id')->firstOrFail();

        $this->assertSame('Jitsi', $schedule->provider);
        $this->assertStringStartsWith('https://meet.jit.si/Sheykhan-', (string) $schedule->meeting_url);
        $this->assertGreaterThan(20, strlen((string) $schedule->meeting_url));
    }

    public function test_weekly_schedule_keeps_a_real_google_meet_link_and_provider(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = Classroom::whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))->firstOrFail();

        $url = 'https://meet.google.com/abc-defg-hij';

        $this->actingAs($teacher)
            ->post(route('teacher.schedule.store'), [
                'classroom_id' => $classroom->id,
                'weekday' => 5,
                'start_time' => '23:00',
                'end_time' => '23:30',
                'provider' => 'Google Meet',
                'meeting_url' => $url,
                'room' => 'جلسه Meet',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('class_schedules', [
            'classroom_id' => $classroom->id,
            'weekday' => 5,
            'provider' => 'Google Meet',
            'meeting_url' => $url,
        ]);
    }

    public function test_live_class_creation_generates_a_jitsi_link_when_provider_is_selected_without_a_url(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $scheduledAt = Carbon::now()->addDays(2)->setTime(18, 0, 0);

        $this->actingAs($teacher)
            ->post(route('teacher.live-classes.store'), [
                'course_id' => $course->id,
                'title' => 'جلسه Jitsi خودکار',
                'provider' => 'Jitsi',
                'scheduled_at' => $scheduledAt->toDateTimeString(),
                'duration_minutes' => 45,
            ])
            ->assertRedirect(route('teacher.live-classes.index'))
            ->assertSessionHasNoErrors();

        $class = \App\Models\LiveClass::query()->where('title', 'جلسه Jitsi خودکار')->firstOrFail();
        $this->assertStringStartsWith('https://meet.jit.si/Sheykhan-', (string) $class->meeting_url);
        $this->assertDatabaseHas('live_classes', [
            'id' => $class->id,
            'provider' => 'Jitsi',
            'scheduled_end_at' => $scheduledAt->copy()->addMinutes(45)->toDateTimeString(),
        ]);
    }

    public function test_google_meet_rejects_a_jitsi_url_instead_of_mislabelling_the_provider(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('teacher.live-classes.store'), [
                'course_id' => $course->id,
                'title' => 'لینک ناسازگار',
                'provider' => 'Google Meet',
                'meeting_url' => 'https://meet.jit.si/not-a-google-meet',
                'scheduled_at' => Carbon::now()->addDays(2)->toDateTimeString(),
                'duration_minutes' => 30,
            ])
            ->assertSessionHasErrors('meeting_url');

        $this->assertDatabaseMissing('live_classes', ['title' => 'لینک ناسازگار']);
    }

    public function test_meeting_link_service_requires_a_room_path_for_known_providers(): void
    {
        $service = app(MeetingLinkService::class);

        $this->assertFalse($service->urlMatchesProvider('Jitsi', 'https://meet.jit.si'));
        $this->assertFalse($service->urlMatchesProvider('Google Meet', 'https://meet.google.com'));
        $this->assertFalse($service->urlMatchesProvider('Zoom', 'https://zoom.us'));

        $this->assertTrue($service->urlMatchesProvider('Jitsi', 'https://meet.jit.si/Sheykhan-test-room'));
        $this->assertTrue($service->urlMatchesProvider('Google Meet', 'https://meet.google.com/abc-defg-hij'));
        $this->assertTrue($service->urlMatchesProvider('Zoom', 'https://us02web.zoom.us/j/123456789'));
    }

    public function test_live_class_creation_persists_a_calculated_end_time(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $scheduledAt = Carbon::now()->addDay()->setTime(18, 0, 0);

        $this->actingAs($teacher)
            ->post(route('teacher.live-classes.store'), [
                'course_id' => $course->id,
                'title' => 'جلسه تست آنلاین',
                'provider' => 'Jitsi',
                'meeting_url' => 'https://meet.jit.si/sheykhan-test',
                'scheduled_at' => $scheduledAt->toDateTimeString(),
                'duration_minutes' => 60,
            ])
            ->assertRedirect(route('teacher.live-classes.index'));

        $this->assertDatabaseHas('live_classes', [
            'teacher_id' => $teacher->id,
            'course_id' => $course->id,
            'status' => 'scheduled',
            'scheduled_end_at' => $scheduledAt->copy()->addHour()->toDateTimeString(),
        ]);
    }

    public function test_teacher_can_store_attendance_with_a_jalali_date(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = Classroom::whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))->firstOrFail();
        $student = $classroom->students()->wherePivot('status', 'active')->firstOrFail();
        $attendance = $classroom->students()
            ->wherePivot('status', 'active')
            ->pluck('users.id')
            ->mapWithKeys(static fn ($studentId) => [(int) $studentId => 'present'])
            ->all();
        $attendance[$student->id] = 'absent';

        $this->actingAs($teacher)
            ->post(route('teacher.classrooms.attendance.store', $classroom), [
                'attendance_date' => '۱۴۰۵/۰۷/۱۶',
                'attendance' => $attendance,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('attendances', [
            'classroom_id' => $classroom->id,
            'student_id' => $student->id,
            'attendance_date' => '2026-10-08',
            'status' => 'absent',
            'marked_by' => $teacher->id,
        ]);
    }

    public function test_attendance_edit_loads_existing_status_for_the_selected_date(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = Classroom::whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))->firstOrFail();
        $student = $classroom->students()->wherePivot('status', 'active')->firstOrFail();

        $attendance = Attendance::query()
            ->where('classroom_id', $classroom->id)
            ->where('student_id', $student->id)
            ->whereDate('attendance_date', '2026-10-08')
            ->first();

        if (! $attendance) {
            $attendance = new Attendance([
                'classroom_id' => $classroom->id,
                'student_id' => $student->id,
                'attendance_date' => '2026-10-08',
            ]);
        }

        $attendance->forceFill([
            'marked_by' => $teacher->id,
            'status' => 'absent',
        ])->save();

        $attendanceUrl = route('teacher.classrooms.attendance.edit', $classroom)
            . '?attendance_date=2026-10-08';

        $this->actingAs($teacher)
            ->get($attendanceUrl)
            ->assertOk()
            ->assertSee('value="absent" selected', false);
    }

    public function test_teacher_courses_classrooms_and_roster_follow_explicit_assignments(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $workspace = app(TeacherWorkspaceService::class);

        $this->assertSame(
            ['math-foundation-7'],
            $workspace->courses($teacher)->pluck('slug')->all()
        );

        $this->assertSame(
            ['MATH7-01'],
            $workspace->classrooms($teacher)->pluck('code')->all()
        );

        $this->assertEqualsCanonicalizing(
            ['student.armin@sheykhan.test', 'student.ava@sheykhan.test'],
            $workspace->students($teacher)->pluck('email')->all()
        );
    }
}
