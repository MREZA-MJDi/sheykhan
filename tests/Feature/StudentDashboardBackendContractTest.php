<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Media;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\StudentDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDashboardBackendContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_contract_contains_scoped_courses_resources_and_recent_results(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $payload = app(StudentDashboardService::class)->build($student);

        $this->assertArrayHasKey('courses', $payload);
        $this->assertArrayHasKey('studentAvatarUrl', $payload);
        $this->assertArrayHasKey('resources', $payload);
        $this->assertArrayHasKey('recentResults', $payload);
        $this->assertArrayHasKey('pendingAssignments', $payload);
        $this->assertArrayHasKey('upcomingLiveClassCount', $payload);
        $this->assertGreaterThanOrEqual($payload['upcomingLiveClasses']->count(), $payload['upcomingLiveClassCount']);
        $this->assertArrayHasKey('nextLesson', $payload);
        $this->assertArrayHasKey('nextLiveClass', $payload);
        $this->assertArrayHasKey('completedLessonsCount', $payload);
        $this->assertArrayHasKey('totalLessonsCount', $payload);
        $this->assertArrayHasKey('studyMinutesLast7Days', $payload);
        $this->assertArrayHasKey('studyStreak', $payload);
        $this->assertArrayHasKey('studyWeek', $payload);
        $this->assertArrayHasKey('achievements', $payload);
        $this->assertArrayHasKey('achievementCount', $payload);
        $this->assertCount(7, $payload['studyWeek']);
        $this->assertCount(7, collect($payload['studyWeek'])->filter(
            fn (array $day) => array_key_exists('minutes', $day)
        ));
        $this->assertIsNumeric($payload['overallProgress']);
        $this->assertTrue($payload['resources']->count() <= 4);

        $course = $payload['courses']->firstWhere('slug', 'math-foundation-7');

        $this->assertNotNull($course);
        $this->assertTrue($course->relationLoaded('academy'));
        $this->assertSame('آکادمی شیخان', $course->academy->name);

        if ($payload['nextLiveClass']) {
            $this->assertInstanceOf(
                \Illuminate\Support\Carbon::class,
                $payload['nextLiveClass']->scheduled_at
            );
        }

        foreach ($payload['recentResults'] as $result) {
            $this->assertNotNull($result->occurred_at);
            $this->assertNotSame('', $result->status_label ?? '');
        }
    }

    public function test_persisted_student_avatar_is_shared_by_the_dashboard_shell_and_hero(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $profile = StudentProfile::query()->firstOrCreate(['user_id' => $student->id]);

        $media = Media::create([
            'uploaded_by' => $student->id,
            'disk' => 'local',
            'path' => 'students/' . $student->id . '/avatar.jpg',
            'original_name' => 'avatar.jpg',
            'file_name' => 'avatar.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 1024,
            'checksum' => hash('sha256', 'student-avatar-test'),
            'visibility' => 'public',
            'collection' => 'student-avatar',
            'metadata' => [],
            'status' => 'active',
        ]);

        $profile->media()->attach($media->id, [
            'collection' => 'student-avatar',
            'sort_order' => 0,
            'is_featured' => true,
        ]);

        $avatarUrl = route('media.public', $media);
        $payload = app(StudentDashboardService::class)->build($student->fresh());

        $this->assertSame($avatarUrl, $payload['studentAvatarUrl']);

        $html = $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->getContent();

        // The sidebar and account shortcut use the same persisted photo.
        $this->assertSame(2, substr_count($html, e($avatarUrl)));
        $this->assertStringContainsString('data-avatar-fallback', $html);
    }

    public function test_private_student_avatar_uses_authenticated_media_route_on_all_surfaces(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $profile = StudentProfile::query()->firstOrCreate(['user_id' => $student->id]);

        $media = Media::create([
            'uploaded_by' => $student->id,
            'disk' => 'local',
            'path' => 'students/' . $student->id . '/private-avatar.jpg',
            'original_name' => 'private-avatar.jpg',
            'file_name' => 'private-avatar.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 1024,
            'checksum' => hash('sha256', 'student-private-avatar-test'),
            'visibility' => 'private',
            'collection' => 'student-avatar',
            'metadata' => [],
            'status' => 'active',
        ]);

        $profile->media()->attach($media->id, [
            'collection' => 'student-avatar',
            'sort_order' => 0,
            'is_featured' => true,
        ]);

        $avatarUrl = route('media.view', $media);
        $payload = app(StudentDashboardService::class)->build($student->fresh());

        $this->assertSame($avatarUrl, $payload['studentAvatarUrl']);

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee($avatarUrl, false);

        $this->actingAs($student)
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertSee($avatarUrl, false);
    }

    public function test_pending_assignment_kpi_is_not_limited_to_the_dashboard_list(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $classroom = $course->classrooms()->where('code', 'MATH7-01')->firstOrFail();

        for ($index = 1; $index <= 8; $index++) {
            Assignment::create([
                'course_id' => $course->id,
                'classroom_id' => $classroom->id,
                'teacher_id' => User::where('email', 'teacher.math@sheykhan.test')->value('id'),
                'title' => 'تکلیف پیگیری ' . $index,
                'instructions' => 'تکلیف تستی',
                'due_at' => now()->addDays($index),
                'max_score' => 100,
                'status' => 'published',
            ]);
        }

        $payload = app(StudentDashboardService::class)->build($student);

        $this->assertSame(8, $payload['pendingAssignments']);
        $this->assertCount(6, $payload['assignments']);
    }

    public function test_classroom_specific_assignment_is_not_visible_to_a_course_peer_outside_the_classroom(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $otherStudent = User::where('email', 'student.parsa@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $targetClassroom = $course->classrooms()->where('code', 'MATH7-01')->firstOrFail();

        $otherClassroom = Classroom::create([
            'academy_id' => $course->academy_id,
            'course_id' => $course->id,
            'grade_id' => $targetClassroom->grade_id,
            'academic_year_id' => $targetClassroom->academic_year_id,
            'title' => 'کلاس ریاضی هفتم - گروه ۲',
            'code' => 'MATH7-02',
            'description' => 'کلاس تستی مستقل',
            'capacity' => 20,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonths(2),
        ]);

        $otherClassroom->students()->syncWithoutDetaching([
            $otherStudent->id => [
                'status' => 'active',
                'enrolled_at' => now(),
            ],
        ]);

        CourseEnrollment::updateOrCreate(
            [
                'course_id' => $course->id,
                'student_id' => $otherStudent->id,
            ],
            [
                'status' => 'active',
                'started_at' => now(),
            ]
        );

        Assignment::create([
            'course_id' => $course->id,
            'classroom_id' => $otherClassroom->id,
            'teacher_id' => User::where('email', 'teacher.math@sheykhan.test')->value('id'),
            'title' => 'تکلیف فقط گروه دو',
            'instructions' => 'نباید برای دانش‌آموز گروه یک دیده شود.',
            'due_at' => now()->addDay(),
            'max_score' => 100,
            'status' => 'published',
        ]);

        $payload = app(StudentDashboardService::class)->build($student);

        $this->assertFalse(
            $payload['assignments']->contains('title', 'تکلیف فقط گروه دو')
        );
    }
}
