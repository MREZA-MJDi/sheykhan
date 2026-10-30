<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Classroom;
use App\Models\Media;
use App\Models\MediaAttachment;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Exam;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\CourseAccessService;
use App\Services\TeacherAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class TeacherAccessIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_cannot_use_a_course_from_an_academy_where_membership_is_archived(): void
    {
        [$teacher, $course] = $this->teacherWithArchivedCourseMembership();

        $this->assertFalse(
            app(TeacherAccessService::class)->canTeachCourse($teacher, $course)
        );
    }

    public function test_archived_teacher_cannot_view_course_or_classroom_media_through_generic_media_policy(): void
    {
        [$teacher, $course] = $this->teacherWithArchivedCourseMembership();

        $this->assertFalse(app(CourseAccessService::class)->canAccess($teacher, $course));

        $courseMedia = $this->privateMedia($teacher, 'old-course-handout.pdf');
        $course->media()->attach($courseMedia->id, [
            'collection' => 'course-assets',
            'sort_order' => 1,
            'is_featured' => false,
        ]);

        $this->assertFalse(Gate::forUser($teacher)->allows('view', $courseMedia));

        $classroom = Classroom::query()->create([
            'academy_id' => $course->academy_id,
            'course_id' => $course->id,
            'title' => 'کلاس آرشیوشده',
            'code' => 'ARCH-' . Str::upper(Str::random(8)),
            'capacity' => 20,
            'status' => 'active',
        ]);
        $classroom->teachers()->attach($teacher->id);

        $classMedia = $this->privateMedia($teacher, 'old-classroom-handout.pdf');
        MediaAttachment::query()->create([
            'media_id' => $classMedia->id,
            'mediable_type' => Classroom::class,
            'mediable_id' => $classroom->id,
            'collection' => 'classroom-assets',
            'sort_order' => 1,
            'is_featured' => false,
        ]);

        $this->assertFalse(Gate::forUser($teacher)->allows('view', $classMedia));
    }

    public function test_archived_academy_membership_blocks_viewing_exam_attempts_even_when_teacher_is_active_elsewhere(): void
    {
        [$teacher, $course] = $this->teacherWithArchivedCourseMembership();

        $exam = Exam::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'آزمون آموزشگاه قبلی',
            'duration_minutes' => 30,
            'attempts_allowed' => 1,
            'status' => 'published',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.attempts', $exam))
            ->assertForbidden();
    }

    public function test_archived_academy_membership_blocks_uploading_media_to_an_old_lesson(): void
    {
        [$teacher, $course] = $this->teacherWithArchivedCourseMembership();

        $section = CourseSection::create([
            'course_id' => $course->id,
            'title' => 'بخش قدیمی',
            'sort_order' => 1,
        ]);
        $lesson = Lesson::create([
            'course_section_id' => $section->id,
            'title' => 'درس قدیمی',
            'slug' => 'old-lesson',
            'type' => 'video',
            'status' => 'draft',
        ]);

        Storage::fake('local');

        $this->actingAs($teacher)
            ->post(route('teacher.lessons.media.store', $lesson), [
                'media' => UploadedFile::fake()->create('lesson.pdf', 20, 'application/pdf'),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('media', 0);
        $this->assertDatabaseCount('learning_resources', 0);
    }

    public function test_archived_academy_membership_blocks_grading_old_assignment_even_when_teacher_is_active_elsewhere(): void
    {
        [$teacher, $course] = $this->teacherWithArchivedCourseMembership();

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'تکلیف قدیمی',
            'max_score' => 20,
            'status' => 'published',
        ]);

        $student = User::factory()->create();
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'content' => 'پاسخ',
            'submitted_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->patch(route('teacher.assignments.submissions.update', [$assignment, $submission]), [
                'score' => 18,
                'feedback' => 'خوب بود',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('assignment_submissions', [
            'id' => $submission->id,
            'score' => 18,
        ]);
    }

    private function privateMedia(User $uploader, string $filename): Media
    {
        return Media::query()->create([
            'uploaded_by' => $uploader->id,
            'disk' => 'local',
            'path' => 'private/' . Str::uuid() . '.pdf',
            'original_name' => $filename,
            'file_name' => $filename,
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 123,
            'checksum' => hash('sha256', $filename),
            'visibility' => 'private',
            'collection' => 'test',
            'metadata' => [],
            'status' => 'active',
        ]);
    }

    private function teacherWithArchivedCourseMembership(): array
    {
        $role = Role::create([
            'name' => 'مدرس',
            'slug' => 'teacher',
            'description' => 'Teacher',
        ]);

        $permissions = collect([
            ['name' => 'assignments.manage', 'label' => 'Manage assignments', 'group' => 'assignments'],
            ['name' => 'exams.view', 'label' => 'View exams', 'group' => 'exams'],
            ['name' => 'media.upload', 'label' => 'Upload media', 'group' => 'media'],
        ])->map(fn (array $permission) => Permission::create($permission));

        $role->permissions()->attach($permissions->pluck('id')->all());

        $teacher = User::factory()->create([
            'email' => 'teacher-' . Str::random(8) . '@test.local',
        ]);
        $teacher->roles()->attach($role->id);

        $activeAcademy = Academy::create([
            'owner_id' => User::factory()->create()->id,
            'name' => 'آکادمی فعال',
            'slug' => 'active-' . Str::random(8),
            'status' => 'active',
        ]);

        $teacher->academies()->attach($activeAcademy->id, [
            'role' => 'teacher',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $archivedAcademy = Academy::create([
            'owner_id' => User::factory()->create()->id,
            'name' => 'آکادمی قبلی',
            'slug' => 'archived-' . Str::random(8),
            'status' => 'active',
        ]);

        $teacher->academies()->attach($archivedAcademy->id, [
            'role' => 'teacher',
            'status' => 'archived',
            'joined_at' => now()->subMonths(2),
        ]);

        $course = Course::create([
            'academy_id' => $archivedAcademy->id,
            'created_by' => $teacher->id,
            'title' => 'دوره قبلی',
            'slug' => 'old-course-' . Str::random(8),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now()->subDay(),
        ]);

        $course->teachers()->attach($teacher->id);

        return [$teacher, $course];
    }
}
