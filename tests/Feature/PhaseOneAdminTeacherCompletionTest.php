<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\AcademyMeeting;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\CounselingRequest;
use App\Models\Course;
use App\Models\Exam;
use App\Models\LiveClass;
use App\Models\Media;
use App\Models\ParentMessage;
use App\Models\Permission;
use App\Models\Question;
use App\Models\Role;
use App\Models\TeacherPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PhaseOneAdminTeacherCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_feedback_and_counseling_have_an_end_to_end_workflow(): void
    {
        [$owner, $academy, $course, $classroom, $teacher, $student, $parent] = $this->workspace();

        $this->actingAs($parent)->post(route('parent.messages.store'), [
            'target' => $academy->id . ':' . $student->id,
            'category' => 'suggestion',
            'subject' => 'پیشنهاد درباره تکلیف',
            'body' => 'لطفاً راهنمای تکلیف جلسه اخیر را شفاف‌تر کنید.',
        ])->assertRedirect(route('parent.messages.index'));

        $message = ParentMessage::query()->where('parent_id', $parent->id)->firstOrFail();

        $this->actingAs($owner)->patch(route('owner.messages.reply', [$academy, $message->id]), [
            'status' => 'responded',
            'response' => 'پیشنهاد ثبت شد و به مدرس مربوط منتقل شد.',
        ])->assertRedirect(route('owner.messages.index', $academy));

        $this->actingAs($parent)->get(route('parent.messages.index'))
            ->assertOk()->assertSee('پیشنهاد درباره تکلیف')->assertSee('پیشنهاد ثبت شد و به مدرس مربوط منتقل شد.');

        $this->actingAs($parent)->post(route('parent.counseling.store'), [
            'target' => $academy->id . ':' . $student->id,
            'subject' => 'مشاوره برنامه‌ریزی',
            'details' => 'برای برنامه مطالعه فرزندمان جلسه می‌خواهیم.',
            'preferred_at' => now()->addDays(3)->setTime(11, 0)->format('Y-m-d\TH:i'),
        ])->assertRedirect(route('parent.counseling.index'));

        $request = CounselingRequest::query()->where('parent_id', $parent->id)->firstOrFail();
        $finalTime = now()->addDays(4)->setTime(13, 30);

        $this->actingAs($owner)->patch(route('owner.counseling.update', [$academy, $request->id]), [
            'status' => 'scheduled',
            'scheduled_at' => $finalTime->format('Y-m-d\TH:i'),
            'response_notes' => 'جلسه در زمان ثبت‌شده برگزار می‌شود.',
        ])->assertRedirect(route('owner.counseling.index', $academy));

        $this->actingAs($parent)->get(route('parent.counseling.index'))
            ->assertOk()->assertSee('مشاوره برنامه‌ریزی')->assertSee('جلسه در زمان ثبت‌شده برگزار می‌شود.');

        $this->assertDatabaseHas('counseling_requests', [
            'id' => $request->id, 'academy_id' => $academy->id,
            'parent_id' => $parent->id, 'student_id' => $student->id,
            'status' => 'scheduled', 'handled_by' => $owner->id,
        ]);
    }

    public function test_owner_can_update_attendance_only_inside_the_owned_academy(): void
    {
        [$owner, $academy, $course, $classroom, $teacher, $student] = $this->workspace();
        $attendance = Attendance::query()->create([
            'classroom_id' => $classroom->id, 'student_id' => $student->id,
            'marked_by' => $teacher->id, 'attendance_date' => today()->toDateString(),
            'scheduled_minutes' => 60, 'attended_minutes' => 40,
            'status' => 'late', 'note' => 'ثبت اولیه',
        ]);

        $this->actingAs($owner)->patch(route('owner.attendance.update', [$academy, $attendance->id]), [
            'status' => 'present', 'scheduled_minutes' => 60,
            'attended_minutes' => 55, 'note' => 'اصلاح‌شده پس از بررسی',
        ])->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id, 'status' => 'present',
            'attended_minutes' => 55, 'marked_by' => $owner->id,
        ]);

        $otherOwner = User::factory()->create(['status' => 'active', 'email' => 'other-' . Str::random(8) . '@test.local']);
        $otherOwner->roles()->attach($this->role('academy-owner', ['dashboard.view','academy.view','attendance.view','attendance.manage'])->id);
        $otherAcademy = Academy::query()->create([
            'owner_id' => $otherOwner->id, 'name' => 'آکادمی دیگر',
            'slug' => 'other-' . Str::lower(Str::random(8)), 'status' => 'active',
        ]);

        $this->actingAs($owner)->patch(route('owner.attendance.update', [$otherAcademy, $attendance->id]), [
            'status' => 'absent', 'scheduled_minutes' => 60, 'attended_minutes' => 0,
        ])->assertNotFound();

        $this->assertSame('present', $attendance->fresh()->status);
    }

    public function test_owner_can_view_exam_questions_and_only_publish_an_exam_with_questions(): void
    {
        [$owner, $academy, $course, $classroom, $teacher] = $this->workspace();
        $exam = Exam::query()->create([
            'course_id' => $course->id, 'classroom_id' => $classroom->id, 'teacher_id' => $teacher->id,
            'title' => 'آزمون فصل اول', 'description' => 'آزمون آزمایشی',
            'duration_minutes' => 30, 'attempts_allowed' => 1, 'status' => 'draft',
        ]);

        $this->actingAs($owner)->get(route('owner.exams.index', $academy))
            ->assertOk()->assertSee('آزمون فصل اول');

        $this->actingAs($owner)->patch(route('owner.exams.status', [$academy, $exam->id]), ['status' => 'published'])
            ->assertSessionHasErrors('status');

        Question::query()->create([
            'exam_id' => $exam->id, 'type' => 'multiple_choice',
            'question' => 'دو به‌علاوه دو چند می‌شود؟',
            'options' => ['A' => '۳', 'B' => '۴'],
            'correct_answer' => 'B', 'score' => 1, 'sort_order' => 1,
        ]);

        $this->actingAs($owner)->patch(route('owner.exams.status', [$academy, $exam->id]), ['status' => 'published'])
            ->assertRedirect(route('owner.exams.show', [$academy, $exam->id]));

        $this->actingAs($owner)->get(route('owner.exams.show', [$academy, $exam->id]))
            ->assertOk()->assertSee('آزمون فصل اول')->assertSee('منتشرشده');

        $this->assertSame('published', $exam->fresh()->status);
    }

    public function test_owner_recording_management_keeps_media_private_and_scopes_by_academy(): void
    {
        Storage::fake('local');
        [$owner, $academy, $course, $classroom, $teacher] = $this->workspace();
        Storage::disk('local')->put('recordings/session.mp4', 'test video');
        $media = Media::query()->create([
            'uploaded_by' => $teacher->id, 'disk' => 'local', 'path' => 'recordings/session.mp4',
            'original_name' => 'session.mp4', 'file_name' => 'session.mp4',
            'mime_type' => 'video/mp4', 'extension' => 'mp4', 'size' => 10,
            'checksum' => sha1('test video'), 'visibility' => 'private',
            'collection' => 'recordings', 'status' => 'active',
        ]);
        $liveClass = LiveClass::query()->create([
            'course_id' => $course->id, 'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id, 'recording_media_id' => $media->id,
            'title' => 'جلسه ضبط‌شده', 'provider' => 'Jitsi',
            'meeting_url' => 'https://meet.jit.si/Sheykhan-Test',
            'scheduled_at' => now()->subDay(), 'scheduled_end_at' => now()->subDay()->addHour(),
            'started_at' => now()->subDay(), 'ended_at' => now()->subDay()->addHour(),
            'recording_visibility' => 'private', 'duration_minutes' => 60, 'status' => 'completed',
        ]);

        $this->actingAs($owner)->get(route('owner.recordings.index', $academy))
            ->assertOk()->assertSee('session.mp4');

        $this->actingAs($owner)->patch(route('owner.recordings.update', [$academy, $liveClass->id]), [
            'action' => 'publish',
        ])->assertRedirect(route('owner.recordings.index', $academy));

        $this->assertTrue($liveClass->fresh()->isRecordingAvailable());
        $this->assertSame('private', $media->fresh()->visibility);

        $otherOwner = User::factory()->create(['status' => 'active', 'email' => 'rec-owner-' . Str::random(8) . '@test.local']);
        $otherOwner->roles()->attach($this->role('academy-owner', ['dashboard.view','academy.view','live_classes.view','live_classes.manage'])->id);
        $otherAcademy = Academy::query()->create([
            'owner_id' => $otherOwner->id, 'name' => 'آکادمی ضبط دیگر',
            'slug' => 'recording-other-' . Str::lower(Str::random(8)), 'status' => 'active',
        ]);

        $this->actingAs($owner)->patch(route('owner.recordings.update', [$otherAcademy, $liveClass->id]), ['action' => 'unpublish'])
            ->assertNotFound();

        $this->assertTrue($liveClass->fresh()->isRecordingAvailable());
    }

    public function test_teacher_can_read_assigned_class_messages_and_only_own_payment_records(): void
    {
        [$owner, $academy, $course, $classroom, $teacher, $student, $parent, $otherTeacher] = $this->workspace(true);
        ParentMessage::query()->create([
            'academy_id' => $academy->id, 'parent_id' => $parent->id, 'student_id' => $student->id,
            'category' => 'question', 'subject' => 'پیگیری کلاس', 'body' => 'پیام مربوط به کلاس مدرس',
            'status' => 'open',
        ]);
        $meeting = AcademyMeeting::query()->create([
            'academy_id' => $academy->id, 'classroom_id' => $classroom->id, 'course_id' => $course->id,
            'student_id' => $student->id, 'parent_id' => $parent->id, 'created_by' => $owner->id,
            'title' => 'جلسه با خانواده', 'message' => 'بررسی روند یادگیری',
            'scheduled_at' => now()->addDay(), 'scheduled_end_at' => now()->addDay()->addHour(), 'status' => 'scheduled',
        ]);

        $this->actingAs($teacher)->get(route('teacher.messages.index'))->assertOk()->assertSee('پیگیری کلاس');
        $this->actingAs($teacher)->get(route('teacher.meetings.index'))->assertOk()->assertSee('جلسه با خانواده');
        $this->actingAs($teacher)->patch(route('teacher.meetings.status', $meeting->id), ['status' => 'completed'])
            ->assertRedirect(route('teacher.meetings.index'));
        $this->assertSame('completed', $meeting->fresh()->status);

        $this->actingAs($owner)->post(route('owner.teacher-payments.store', $academy), [
            'teacher_id' => $teacher->id, 'title' => 'حق‌التدریس مهر', 'amount' => 2500000,
            'status' => 'paid', 'reference' => 'PAY-' . Str::random(8),
        ])->assertRedirect(route('owner.teacher-payments.index', $academy));

        TeacherPayment::query()->create([
            'academy_id' => $academy->id, 'teacher_id' => $otherTeacher->id, 'recorded_by' => $owner->id,
            'title' => 'پرداخت مدرس دیگر', 'amount' => 900000, 'currency' => 'IRT',
            'status' => 'paid', 'paid_at' => now(),
        ]);

        $this->actingAs($teacher)->get(route('teacher.payments.index'))
            ->assertOk()->assertSee('حق‌التدریس مهر')->assertDontSee('پرداخت مدرس دیگر');
    }

    private function workspace(bool $withOtherTeacher = false): array
    {
        $owner = User::factory()->create(['name' => 'ادمین / منشی', 'email' => 'owner-' . Str::random(8) . '@test.local', 'status' => 'active']);
        $teacher = User::factory()->create(['name' => 'مدرس فعال', 'email' => 'teacher-' . Str::random(8) . '@test.local', 'status' => 'active']);
        $student = User::factory()->create(['name' => 'دانش‌آموز تست', 'email' => 'student-' . Str::random(8) . '@test.local', 'status' => 'active']);
        $parent = User::factory()->create(['name' => 'ولی تست', 'email' => 'parent-' . Str::random(8) . '@test.local', 'status' => 'active']);

        $owner->roles()->attach($this->role('academy-owner', [
            'dashboard.view','academy.view','academy.manage','courses.view','courses.manage',
            'classrooms.view','classrooms.manage','parents.view','parents.manage','parent_messages.view',
            'exams.view','exams.manage','attendance.view','attendance.manage','live_classes.view','live_classes.manage',
            'teacher_payments.view','teacher_payments.manage',
        ])->id);
        $teacher->roles()->attach($this->role('teacher', [
            'dashboard.view','courses.view','courses.manage','classrooms.view','assignments.view','assignments.manage',
            'exams.view','exams.manage','attendance.view','attendance.manage','live_classes.view','live_classes.manage',
            'parent_messages.view','teacher_payments.view',
        ])->id);
        $student->roles()->attach($this->role('student', ['dashboard.view','courses.view','live_classes.view','media.view'])->id);
        $parent->roles()->attach($this->role('parent', ['dashboard.view'])->id);

        $academy = Academy::query()->create([
            'owner_id' => $owner->id, 'name' => 'آکادمی فاز اول',
            'slug' => 'phase-one-' . Str::lower(Str::random(8)), 'status' => 'active',
        ]);
        foreach ([[$teacher,'teacher'],[$student,'student'],[$parent,'parent']] as [$user,$role]) {
            $academy->users()->attach($user->id, ['role'=>$role,'status'=>'active','joined_at'=>now()]);
        }

        $course = Course::query()->create([
            'academy_id' => $academy->id, 'created_by' => $owner->id,
            'title' => 'دوره فاز اول', 'slug' => 'phase-course-' . Str::lower(Str::random(8)),
            'status' => 'published', 'access_type' => 'free', 'price' => 0, 'published_at' => now(),
        ]);
        $course->teachers()->attach($teacher->id, ['is_primary'=>true]);
        $classroom = Classroom::query()->create([
            'academy_id' => $academy->id, 'course_id' => $course->id,
            'title' => 'کلاس فاز اول', 'code' => 'CLS-' . Str::upper(Str::random(6)),
            'capacity' => 20, 'status' => 'active',
        ]);
        $classroom->teachers()->attach($teacher->id);
        $classroom->students()->attach($student->id, ['status'=>'active','enrolled_at'=>now()]);
        $parent->children()->attach($student->id, ['relation'=>'parent']);

        $otherTeacher = null;
        if ($withOtherTeacher) {
            $otherTeacher = User::factory()->create(['name'=>'مدرس دیگر','email'=>'teacher-other-' . Str::random(8) . '@test.local','status'=>'active']);
            $otherTeacher->roles()->attach($this->role('teacher', ['dashboard.view','teacher_payments.view'])->id);
            $academy->users()->attach($otherTeacher->id, ['role'=>'teacher','status'=>'active','joined_at'=>now()]);
        }

        return [$owner,$academy,$course,$classroom,$teacher,$student,$parent,$otherTeacher];
    }

    private function role(string $slug, array $permissionNames): Role
    {
        $role = Role::query()->firstOrCreate(['slug'=>$slug], ['name'=>$slug, 'description'=>$slug]);
        $ids = [];
        foreach ($permissionNames as $name) {
            $permission = Permission::query()->firstOrCreate(['name'=>$name], ['label'=>$name,'group'=>explode('.',$name)[0]]);
            $ids[] = $permission->id;
        }
        $role->permissions()->syncWithoutDetaching($ids);
        return $role;
    }
}
