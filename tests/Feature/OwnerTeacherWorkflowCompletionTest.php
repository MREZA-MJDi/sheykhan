<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\AcademyMeeting;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\LiveClass;
use App\Models\Permission;
use App\Models\Question;
use App\Models\Role;
use App\Models\TeacherDirectMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class OwnerTeacherWorkflowCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_upload_a_private_recording_then_publish_it_for_eligible_students(): void
    {
        Storage::fake('local');
        $workspace = $this->workspace();
        $session = LiveClass::query()->create([
            'course_id' => $workspace['course']->id,
            'classroom_id' => $workspace['classroom']->id,
            'teacher_id' => $workspace['teacher']->id,
            'title' => 'جلسه ضبط‌دار',
            'provider' => 'Jitsi',
            'meeting_url' => 'https://meet.jit.si/Sheykhan-test-recording',
            'scheduled_at' => now()->subHours(2),
            'scheduled_end_at' => now()->subHour(),
            'ended_at' => now()->subHour(),
            'duration_minutes' => 60,
            'status' => 'completed',
        ]);

        $this->actingAs($workspace['owner'])
            ->post(route('owner.recordings.upload', [$workspace['academy'], $session->id]), [
                'recording' => UploadedFile::fake()->create('lesson-recording.mp4', 256, 'video/mp4'),
            ])
            ->assertRedirect(route('owner.recordings.index', $workspace['academy']))
            ->assertSessionHas('success');

        $session->refresh();
        $this->assertNotNull($session->recording_media_id);
        $this->assertNull($session->recording_released_at);
        $this->assertSame('private', $session->recording_visibility);
        $this->assertSame('completed', $session->status);
        $this->assertDatabaseHas('media', [
            'id' => $session->recording_media_id,
            'visibility' => 'private',
            'status' => 'active',
            'original_name' => 'lesson-recording.mp4',
        ]);

        $this->actingAs($workspace['owner'])
            ->get(route('owner.recordings.index', $workspace['academy']))
            ->assertOk()
            ->assertSee('lesson-recording.mp4');

        $this->actingAs($workspace['owner'])
            ->patch(route('owner.recordings.update', [$workspace['academy'], $session->id]), [
                'action' => 'publish',
            ])
            ->assertRedirect(route('owner.recordings.index', $workspace['academy']))
            ->assertSessionHas('success');

        $session->refresh();
        $this->assertNotNull($session->recording_released_at);
        $this->assertSame('enrolled_students', $session->recording_visibility);
        $this->assertSame('published', $session->status);
    }

    public function test_owner_can_manually_grade_a_submitted_online_exam_attempt(): void
    {
        $workspace = $this->workspace();
        $exam = Exam::query()->create([
            'course_id' => $workspace['course']->id,
            'classroom_id' => $workspace['classroom']->id,
            'teacher_id' => $workspace['teacher']->id,
            'title' => 'آزمون نهایی',
            'description' => 'بررسی گردش تصحیح توسط مدیر',
            'duration_minutes' => 45,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'attempts_allowed' => 1,
            'status' => 'published',
        ]);
        $question = Question::query()->create([
            'exam_id' => $exam->id,
            'type' => 'text',
            'question' => 'راه‌حل خود را توضیح بده.',
            'correct_answer' => null,
            'score' => 5,
            'sort_order' => 1,
        ]);
        $attempt = ExamAttempt::query()->create([
            'exam_id' => $exam->id,
            'student_id' => $workspace['student']->id,
            'attempt_number' => 1,
            'started_at' => now()->subHour(),
            'submitted_at' => now()->subMinutes(10),
            'status' => 'needs_review',
        ]);
        $answer = ExamAnswer::query()->create([
            'exam_attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'answer' => 'ابتدا داده‌ها را مشخص می‌کنم و سپس از رابطهٔ داده‌شده استفاده می‌کنم.',
        ]);

        $this->actingAs($workspace['owner'])
            ->get(route('owner.exams.show', [$workspace['academy'], $exam->id]))
            ->assertOk()
            ->assertSee('تصحیح دستی')
            ->assertSee('ثبت / اصلاح نمره');

        $this->actingAs($workspace['owner'])
            ->patch(route('owner.exams.attempts.grade', [$workspace['academy'], $exam->id, $attempt->id]), [
                'scores' => [$answer->id => 4.5],
            ])
            ->assertRedirect(route('owner.exams.show', [$workspace['academy'], $exam->id]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('exam_answers', [
            'id' => $answer->id,
            'score' => 4.5,
        ]);
        $this->assertDatabaseHas('exam_attempts', [
            'id' => $attempt->id,
            'score' => 4.5,
            'status' => 'graded',
        ]);
    }

    public function test_teacher_can_message_linked_student_and_parent_and_they_can_reply(): void
    {
        $workspace = $this->workspace();

        $this->actingAs($workspace['teacher'])
            ->get(route('teacher.direct-messages.index'))
            ->assertOk()
            ->assertSee($workspace['student']->name)
            ->assertSee($workspace['parent']->name);

        $this->actingAs($workspace['teacher'])
            ->post(route('teacher.direct-messages.store'), [
                'target' => 'student:' . $workspace['classroom']->id . ':' . $workspace['student']->id,
                'category' => 'question',
                'subject' => 'پیگیری تمرین ریاضی',
                'body' => 'لطفاً تا جلسهٔ بعد، تمرین‌های مشخص‌شده را مرور کن.',
            ])
            ->assertRedirect(route('teacher.direct-messages.index'))
            ->assertSessionHas('success');

        $studentMessage = TeacherDirectMessage::query()->where('recipient_type', 'student')->sole();
        $this->actingAs($workspace['student'])
            ->get(route('student.messages.index'))
            ->assertOk()
            ->assertSee('رابط کاربری جدید بخش دانش‌آموز در حال آماده‌سازی است.');

        $this->actingAs($workspace['student'])
            ->patch(route('student.teacher-messages.reply', $studentMessage->id), [
                'response' => 'تمرین‌ها را مرور کردم و یک سؤال دارم.',
            ])
            ->assertRedirect(route('student.messages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('teacher_direct_messages', [
            'id' => $studentMessage->id,
            'status' => 'responded',
            'responded_by' => $workspace['student']->id,
            'response' => 'تمرین‌ها را مرور کردم و یک سؤال دارم.',
        ]);

        $this->actingAs($workspace['teacher'])
            ->get(route('teacher.direct-messages.index'))
            ->assertOk()
            ->assertSee('تمرین‌ها را مرور کردم و یک سؤال دارم.');

        $this->actingAs($workspace['teacher'])
            ->post(route('teacher.direct-messages.store'), [
                'target' => 'parent:' . $workspace['classroom']->id . ':' . $workspace['student']->id . ':' . $workspace['parent']->id,
                'category' => 'other',
                'subject' => 'هماهنگی با خانواده',
                'body' => 'برای پیگیری تمرین‌ها، لطفاً در صورت نیاز از همین مسیر پاسخ دهید.',
            ])
            ->assertRedirect(route('teacher.direct-messages.index'))
            ->assertSessionHas('success');

        $parentMessage = TeacherDirectMessage::query()->where('recipient_type', 'parent')->sole();
        $this->actingAs($workspace['parent'])
            ->get(route('parent.messages.index'))
            ->assertOk()
            ->assertSee('هماهنگی با خانواده')
            ->assertSee('پاسخ به مدرس');

        $this->actingAs($workspace['parent'])
            ->patch(route('parent.teacher-messages.reply', $parentMessage->id), [
                'response' => 'ممنون، تمرین‌های فرزندم را پیگیری می‌کنم.',
            ])
            ->assertRedirect(route('parent.messages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('teacher_direct_messages', [
            'id' => $parentMessage->id,
            'status' => 'responded',
            'responded_by' => $workspace['parent']->id,
            'response' => 'ممنون، تمرین‌های فرزندم را پیگیری می‌کنم.',
        ]);
    }

    public function test_parent_can_message_only_the_active_class_teacher_and_teacher_can_reply(): void
    {
        $workspace = $this->workspace();
        $parent = $workspace['parent'];
        $teacher = $workspace['teacher'];
        $student = $workspace['student'];
        $classroom = $workspace['classroom'];

        $target = 'classroom:' . $classroom->id . ':' . $student->id . ':' . $teacher->id;

        $this->actingAs($parent)
            ->get(route('parent.messages.index'))
            ->assertOk()
            ->assertSee('ارسال پیام مستقیم به مدرس')
            ->assertSee($teacher->name);

        // A parent cannot forge another user's ID to message a teacher who is
        // not assigned to the child's currently active classroom.
        $invalidTarget = 'classroom:' . $classroom->id . ':' . $student->id . ':' . $workspace['owner']->id;
        $this->actingAs($parent)
            ->post(route('parent.messages.teacher.store'), [
                'teacher_target' => $invalidTarget,
                'teacher_category' => 'question',
                'teacher_subject' => 'پیام به مدرس نامرتبط',
                'teacher_body' => 'این پیام نباید به مدرس خارج از کلاس ارسال شود.',
            ])
            ->assertNotFound();

        $this->actingAs($parent)
            ->post(route('parent.messages.teacher.store'), [
                'teacher_target' => $target,
                'teacher_category' => 'question',
                'teacher_subject' => 'پیگیری روند یادگیری فرزند',
                'teacher_body' => 'لطفاً درباره پیشرفت فرزندم در این کلاس راهنمایی بدهید.',
            ])
            ->assertRedirect(route('parent.messages.index'))
            ->assertSessionHas('success');

        $message = TeacherDirectMessage::query()
            ->where('sender_id', $parent->id)
            ->where('sender_type', 'parent')
            ->sole();

        $this->assertDatabaseHas('teacher_direct_messages', [
            'id' => $message->id,
            'teacher_id' => $teacher->id,
            'recipient_type' => 'teacher',
            'recipient_id' => $teacher->id,
            'student_id' => $student->id,
            'classroom_id' => $classroom->id,
            'status' => 'sent',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.direct-messages.index'))
            ->assertOk()
            ->assertSee('پیگیری روند یادگیری فرزند')
            ->assertSee($parent->name)
            ->assertSee('پاسخ به والد');

        $this->actingAs($teacher)
            ->patch(route('teacher.direct-messages.reply', $message->id), [
                'response' => 'پیشرفت فرزند شما در این کلاس ثبت شده و تمرین هفته هم در دسترس است.',
            ])
            ->assertRedirect(route('teacher.direct-messages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('teacher_direct_messages', [
            'id' => $message->id,
            'status' => 'responded',
            'responded_by' => $teacher->id,
            'response' => 'پیشرفت فرزند شما در این کلاس ثبت شده و تمرین هفته هم در دسترس است.',
        ]);

        $this->actingAs($parent)
            ->get(route('parent.messages.index'))
            ->assertOk()
            ->assertSee('پیشرفت فرزند شما در این کلاس ثبت شده و تمرین هفته هم در دسترس است.');
    }

    public function test_student_can_submit_a_word_document_for_an_accessible_assignment(): void
    {
        Storage::fake('local');
        $workspace = $this->workspace();
        $assignment = Assignment::query()->create([
            'course_id' => $workspace['course']->id,
            'classroom_id' => $workspace['classroom']->id,
            'teacher_id' => $workspace['teacher']->id,
            'title' => 'ارسال فایل Word',
            'instructions' => 'پاسخ را در قالب Word ارسال کن.',
            'due_at' => now()->addDay(),
            'max_score' => 100,
            'status' => 'published',
        ]);

        $file = UploadedFile::fake()->create(
            'answer.docx',
            12,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );

        $this->actingAs($workspace['student'])
            ->post(route('student.assignments.submit', $assignment), [
                'content' => null,
                'attachments' => [$file],
            ])
            ->assertRedirect(route('student.assignments.show', $assignment))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $workspace['student']->id,
        ]);
        $this->assertDatabaseHas('media', [
            'original_name' => 'answer.docx',
            'status' => 'active',
        ]);
    }

    public function test_teacher_can_propose_a_family_meeting_owner_can_approve_it_and_both_dashboards_show_it(): void
    {
        $workspace = $this->workspace();
        $title = 'جلسه پیشنهادی مدرس برای خانواده';
        $proposedAt = now()->addDays(3)->setTime(11, 0);
        $endAt = $proposedAt->copy()->addMinutes(30);

        $this->actingAs($workspace['teacher'])
            ->post(route('teacher.meetings.proposals.store'), [
                'meeting_target' => $workspace['classroom']->id . ':' . $workspace['student']->id . ':' . $workspace['parent']->id,
                'title' => $title,
                'scheduled_at' => $proposedAt->format('Y-m-d\TH:i'),
                'scheduled_end_at' => $endAt->format('Y-m-d\TH:i'),
                'message' => 'پیشنهاد برای بررسی روند یادگیری',
            ])
            ->assertRedirect(route('teacher.meetings.index'))
            ->assertSessionHas('success');

        $meeting = AcademyMeeting::query()->where('title', $title)->firstOrFail();
        $this->assertSame('proposed', $meeting->status);

        $this->actingAs($workspace['student'])
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertDontSee($title);

        $this->actingAs($workspace['parent'])
            ->get(route('parent.dashboard'))
            ->assertOk()
            ->assertDontSee($title);

        $approvedAt = now()->addDays(4)->setTime(12, 0);
        $approvedEndAt = $approvedAt->copy()->addMinutes(45);

        $this->actingAs($workspace['owner'])
            ->get(route('owner.meetings.index', $workspace['academy']))
            ->assertOk()
            ->assertSee($title)
            ->assertSee('تأیید و زمان‌بندی');

        $this->actingAs($workspace['owner'])
            ->patch(route('owner.meetings.status', [$workspace['academy'], $meeting->id]), [
                'status' => 'scheduled',
                'scheduled_at' => $approvedAt->format('Y-m-d\TH:i'),
                'scheduled_end_at' => $approvedEndAt->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('owner.meetings.index', $workspace['academy']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('academy_meetings', [
            'id' => $meeting->id,
            'status' => 'scheduled',
            'scheduled_at' => $approvedAt->format('Y-m-d H:i:s'),
        ]);

        $this->actingAs($workspace['student'])
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('رابط کاربری جدید بخش دانش‌آموز در حال آماده‌سازی است.');

        $this->actingAs($workspace['parent'])
            ->get(route('parent.dashboard'))
            ->assertOk()
            ->assertSee($title);
    }

    private function workspace(): array
    {
        $owner = User::factory()->create(['status' => 'active']);
        $teacher = User::factory()->create(['status' => 'active']);
        $student = User::factory()->create(['status' => 'active']);
        $parent = User::factory()->create(['status' => 'active']);

        $ownerRole = $this->role('academy-owner', [
            'dashboard.view', 'academy.view', 'live_classes.view', 'live_classes.manage',
            'exams.view', 'exams.manage', 'parents.view', 'parents.manage',
        ]);
        $teacherRole = $this->role('teacher', [
            'dashboard.view', 'live_classes.view', 'live_classes.manage',
            'exams.view', 'exams.manage', 'parent_messages.manage',
            'student_messages.view', 'student_messages.manage',
        ]);
        $studentRole = $this->role('student', [
            'dashboard.view', 'student_messages.view', 'student_messages.manage',
            'live_classes.view', 'courses.view', 'assignments.view', 'assignments.submit', 'results.view',
            'resources.view', 'profile.view',
        ]);
        $parentRole = $this->role('parent', ['dashboard.view']);

        foreach ([[$owner, $ownerRole], [$teacher, $teacherRole], [$student, $studentRole], [$parent, $parentRole]] as [$user, $role]) {
            $user->roles()->attach($role->id);
        }

        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی گردش‌کار تست',
            'slug' => 'workflow-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);

        foreach ([[$teacher, 'teacher'], [$student, 'student'], [$parent, 'parent']] as [$user, $role]) {
            $academy->users()->attach($user->id, [
                'role' => $role,
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        $student->parents()->attach($parent->id, ['relation' => 'parent']);

        $course = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دورهٔ گردش‌کار',
            'slug' => 'workflow-course-' . Str::lower(Str::random(8)),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now()->subDay(),
        ]);
        $course->teachers()->attach($teacher->id, ['is_primary' => true]);

        $classroom = Classroom::query()->create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس گردش‌کار',
            'code' => 'WF-' . strtoupper(Str::random(6)),
            'capacity' => 20,
            'status' => 'active',
        ]);
        $classroom->teachers()->attach($teacher->id);
        $classroom->students()->attach($student->id, ['status' => 'active', 'enrolled_at' => now()]);
        $student->enrollments()->create([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'status' => 'active',
            'paid_amount' => 0,
            'started_at' => now(),
        ]);

        return compact('owner', 'teacher', 'student', 'parent', 'academy', 'course', 'classroom');
    }

    private function role(string $slug, array $permissionNames): Role
    {
        $role = Role::query()->create(['name' => $slug, 'slug' => $slug, 'description' => $slug]);
        $permissionIds = [];

        foreach ($permissionNames as $permissionName) {
            $permission = Permission::query()->firstOrCreate(
                ['name' => $permissionName],
                ['label' => $permissionName, 'group' => explode('.', $permissionName)[0]],
            );
            $permissionIds[] = $permission->id;
        }

        $role->permissions()->sync($permissionIds);

        return $role;
    }
}
