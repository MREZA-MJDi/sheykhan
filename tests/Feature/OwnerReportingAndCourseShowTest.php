<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\FinancialTransaction;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerReportingAndCourseShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_with_course_view_permission_can_open_course_without_manage_permission(): void
    {
        [$owner, $academy, $course] = $this->ownerWorkspace(['courses.view']);

        $this->actingAs($owner)
            ->get(route('owner.courses.show', $course))
            ->assertOk()
            ->assertViewHas('averageProgress', 0.0);
    }

    public function test_course_progress_is_student_weighted_and_missing_progress_counts_as_zero(): void
    {
        [$owner, $academy, $course] = $this->ownerWorkspace(['courses.view']);

        $firstStudent = User::factory()->create(['email' => 'student-one@test.local']);
        $secondStudent = User::factory()->create(['email' => 'student-two@test.local']);

        CourseEnrollment::create([
            'course_id' => $course->id,
            'student_id' => $firstStudent->id,
            'classroom_id' => null,
            'status' => 'active',
            'paid_amount' => 0,
            'started_at' => now(),
        ]);

        CourseEnrollment::create([
            'course_id' => $course->id,
            'student_id' => $secondStudent->id,
            'classroom_id' => null,
            'status' => 'active',
            'paid_amount' => 0,
            'started_at' => now(),
        ]);

        $section = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'بخش اول',
            'description' => null,
            'sort_order' => 1,
        ]);

        $lessonOne = Lesson::create([
            'course_section_id' => $section->id,
            'title' => 'درس اول',
            'slug' => 'lesson-one-' . Str::random(6),
            'type' => 'video',
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 1,
        ]);

        $lessonTwo = Lesson::create([
            'course_section_id' => $section->id,
            'title' => 'درس دوم',
            'slug' => 'lesson-two-' . Str::random(6),
            'type' => 'video',
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 2,
        ]);

        LessonProgress::create([
            'lesson_id' => $lessonOne->id,
            'user_id' => $firstStudent->id,
            'progress_percent' => 100,
        ]);

        LessonProgress::create([
            'lesson_id' => $lessonTwo->id,
            'user_id' => $firstStudent->id,
            'progress_percent' => 100,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.courses.show', $course))
            ->assertOk()
            ->assertViewHas('averageProgress', 50.0);
    }

    public function test_owner_reports_use_report_analytics_data_contract(): void
    {
        [$owner, $academy] = $this->ownerWorkspace(['reports.view']);

        $this->actingAs($owner)
            ->get(route('owner.reports.index'))
            ->assertOk()
            ->assertViewHas('academy')
            ->assertViewHas('courseReports')
            ->assertViewHas('classroomReports')
            ->assertViewHas('recentEnrollments')
            ->assertViewHas('metrics', fn (array $metrics) => array_key_exists('attendance_rate', $metrics)
                && array_key_exists('exam_average', $metrics)
                && array_key_exists('pending_reviews', $metrics));
    }


    public function test_shared_analytics_uses_latest_exam_attempt_per_student(): void
    {
        [$owner, $academy, $course] = $this->ownerWorkspace(['courses.view']);

        $studentOne = User::factory()->create(['email' => 'analytics-one@test.local']);
        $studentTwo = User::factory()->create(['email' => 'analytics-two@test.local']);
        $teacher = User::factory()->create(['email' => 'analytics-teacher@test.local']);

        CourseEnrollment::create([
            'course_id' => $course->id,
            'student_id' => $studentOne->id,
            'status' => 'active',
            'paid_amount' => 0,
            'started_at' => now(),
        ]);

        CourseEnrollment::create([
            'course_id' => $course->id,
            'student_id' => $studentTwo->id,
            'status' => 'active',
            'paid_amount' => 0,
            'started_at' => now(),
        ]);

        $section = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'بخش تحلیل',
            'sort_order' => 1,
        ]);

        $lesson = Lesson::create([
            'course_section_id' => $section->id,
            'title' => 'درس تحلیل',
            'slug' => 'analytics-' . Str::random(6),
            'type' => 'video',
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 1,
        ]);

        LessonProgress::create([
            'lesson_id' => $lesson->id,
            'user_id' => $studentOne->id,
            'progress_percent' => 100,
        ]);

        $exam = Exam::create([
            'course_id' => $course->id,
            'classroom_id' => null,
            'teacher_id' => $teacher->id,
            'title' => 'آزمون تحلیل',
            'duration_minutes' => 30,
            'attempts_allowed' => 3,
            'status' => 'published',
        ]);

        ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $studentOne->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinutes(30),
            'submitted_at' => now()->subMinutes(20),
            'score' => 50,
            'status' => 'submitted',
        ]);

        ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $studentOne->id,
            'attempt_number' => 2,
            'started_at' => now()->subMinutes(15),
            'submitted_at' => now()->subMinutes(5),
            'score' => 90,
            'status' => 'submitted',
        ]);

        ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $studentTwo->id,
            'attempt_number' => 1,
            'started_at' => now()->subMinutes(10),
            'submitted_at' => now()->subMinutes(2),
            'score' => 70,
            'status' => 'submitted',
        ]);

        $analytics = app(\App\Services\OwnerLearningAnalyticsService::class);

        $this->assertSame(50.0, (float) $analytics->courseProgress([$course->id])->get($course->id));
        $this->assertSame(80.0, (float) $analytics->courseExamAverages([$course->id])->get($course->id));
    }

    public function test_owner_report_teacher_sales_use_financial_ledger_net_of_refunds(): void
    {
        [$owner, $academy, $course] = $this->ownerWorkspace(['reports.view']);

        $teacher = User::factory()->create(['email' => 'ledger-teacher-' . Str::random(6) . '@test.local']);
        $student = User::factory()->create(['email' => 'ledger-student-' . Str::random(6) . '@test.local']);

        $academy->users()->attach($teacher->id, [
            'role' => 'teacher',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $course->teachers()->attach($teacher->id, ['is_primary' => true]);

        $academy->users()->attach($student->id, [
            'role' => 'student',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $enrollment = CourseEnrollment::create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'status' => 'active',
            'paid_amount' => 200000,
            'started_at' => now(),
        ]);

        FinancialTransaction::create([
            'academy_id' => $academy->id,
            'enrollment_id' => $enrollment->id,
            'user_id' => $student->id,
            'recorded_by' => $owner->id,
            'type' => 'enrollment_payment',
            'status' => 'completed',
            'amount' => 200000,
            'currency' => 'IRT',
            'reference' => 'test-payment-' . Str::uuid(),
            'description' => 'test payment',
            'occurred_at' => now(),
        ]);

        FinancialTransaction::create([
            'academy_id' => $academy->id,
            'enrollment_id' => $enrollment->id,
            'user_id' => $student->id,
            'recorded_by' => $owner->id,
            'type' => 'refund',
            'status' => 'completed',
            'amount' => 25000,
            'currency' => 'IRT',
            'reference' => 'test-refund-' . Str::uuid(),
            'description' => 'test refund',
            'occurred_at' => now(),
        ]);

        $report = app(\App\Services\OwnerReportService::class)->build($owner);
        $teacherReport = $report['teacherReports']->first();

        $this->assertNotNull($teacherReport);
        $this->assertEquals(175000.0, (float) $teacherReport->enrollment_sales);
    }

    public function test_owner_teacher_reports_use_only_students_from_each_teachers_assigned_classroom(): void
    {
        [$owner, $academy, $course] = $this->ownerWorkspace(['reports.view', 'dashboard.view']);

        $teacherA = User::factory()->create(['name' => 'مدرس گروه الف', 'email' => 'cohort-a-' . Str::random(6) . '@test.local']);
        $teacherB = User::factory()->create(['name' => 'مدرس گروه ب', 'email' => 'cohort-b-' . Str::random(6) . '@test.local']);
        $studentA = User::factory()->create(['email' => 'cohort-student-a-' . Str::random(6) . '@test.local']);
        $studentB = User::factory()->create(['email' => 'cohort-student-b-' . Str::random(6) . '@test.local']);

        foreach ([$teacherA, $teacherB] as $teacher) {
            $academy->users()->attach($teacher->id, [
                'role' => 'teacher',
                'status' => 'active',
                'joined_at' => now(),
            ]);
            $course->teachers()->attach($teacher->id, ['is_primary' => $teacher->is($teacherA)]);
        }

        $classA = Classroom::create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'گروه الف',
            'code' => 'COHORT-A-' . Str::random(5),
            'capacity' => 20,
            'status' => 'active',
        ]);
        $classB = Classroom::create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'گروه ب',
            'code' => 'COHORT-B-' . Str::random(5),
            'capacity' => 20,
            'status' => 'active',
        ]);

        $classA->teachers()->attach($teacherA->id);
        $classB->teachers()->attach($teacherB->id);

        foreach ([[$classA, $studentA], [$classB, $studentB]] as [$classroom, $student]) {
            $academy->users()->attach($student->id, [
                'role' => 'student',
                'status' => 'active',
                'joined_at' => now(),
            ]);
            $classroom->students()->attach($student->id, [
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
            CourseEnrollment::create([
                'course_id' => $course->id,
                'student_id' => $student->id,
                'classroom_id' => $classroom->id,
                'status' => 'active',
                'payment_status' => 'paid',
                'price_amount' => 0,
                'paid_amount' => 0,
                'started_at' => now(),
            ]);
        }

        $section = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'محتوای مشترک',
            'sort_order' => 1,
        ]);
        $lesson = Lesson::create([
            'course_section_id' => $section->id,
            'title' => 'درس مشترک',
            'slug' => 'shared-lesson-' . Str::random(6),
            'type' => 'text',
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 1,
        ]);
        LessonProgress::create([
            'lesson_id' => $lesson->id,
            'user_id' => $studentA->id,
            'progress_percent' => 100,
        ]);
        LessonProgress::create([
            'lesson_id' => $lesson->id,
            'user_id' => $studentB->id,
            'progress_percent' => 0,
        ]);

        $analytics = app(\\App\\Services\\OwnerLearningAnalyticsService::class);
        $teacherProgress = $analytics->teacherProgressForAcademies(
            [$academy->id],
            [$teacherA->id, $teacherB->id],
        );
        $this->assertSame(100.0, (float) $teacherProgress[$teacherA->id]);
        $this->assertSame(0.0, (float) $teacherProgress[$teacherB->id]);

        $report = app(\\App\\Services\\OwnerReportService::class)->build($owner);
        $reportsByTeacher = $report['teacherReports']->getCollection()->keyBy('teacher_id');
        $this->assertSame(1, (int) $reportsByTeacher[$teacherA->id]->student_count);
        $this->assertSame(1, (int) $reportsByTeacher[$teacherB->id]->student_count);
        $this->assertSame(100.0, (float) $reportsByTeacher[$teacherA->id]->progress_average);
        $this->assertSame(0.0, (float) $reportsByTeacher[$teacherB->id]->progress_average);

        $dashboard = app(\\App\\Services\\OwnerDashboardService::class)->build($owner);
        $dashboardByTeacher = $dashboard['teacherReports']->keyBy('teacher_id');
        $this->assertSame(1, (int) $dashboardByTeacher[$teacherA->id]->student_count);
        $this->assertSame(1, (int) $dashboardByTeacher[$teacherB->id]->student_count);
        $this->assertSame(100.0, (float) $dashboardByTeacher[$teacherA->id]->progress_average);
        $this->assertSame(0.0, (float) $dashboardByTeacher[$teacherB->id]->progress_average);
    }

    private function ownerWorkspace(array $permissions): array
    {
        $owner = User::factory()->create(['email' => 'owner-' . Str::random(8) . '@test.local']);

        $role = Role::create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner',
        ]);

        $permissionIds = [];
        foreach ($permissions as $permissionName) {
            $permissionIds[] = Permission::create([
                'name' => $permissionName,
                'label' => $permissionName,
                'group' => 'owner',
            ])->id;
        }

        $role->permissions()->attach($permissionIds);
        $owner->roles()->attach($role->id);

        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی تست',
            'slug' => 'academy-' . Str::random(8),
            'status' => 'active',
        ]);

        $course = Course::create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'ریاضی تست',
            'slug' => 'math-' . Str::random(8),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now(),
        ]);

        return [$owner, $academy, $course];
    }
}
