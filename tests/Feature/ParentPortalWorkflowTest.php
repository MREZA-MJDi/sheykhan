<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AcademyMeeting;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CounselingRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\LiveClass;
use App\Services\ParentDashboardService;
use App\Models\ParentMessage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ParentPortalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_send_a_scoped_message_and_request_counseling_for_a_linked_child(): void
    {
        $workspace = $this->workspace();
        $parent = $workspace['parent'];
        $student = $workspace['student'];
        $academy = $workspace['academy'];

        $this->actingAs($parent)
            ->post(route('parent.messages.store'), [
                'target' => $student->id . ':' . $academy->id,
                'category' => 'question',
                'subject' => 'پیگیری روند یادگیری',
                'body' => 'لطفاً وضعیت تکالیف این هفته را توضیح دهید.',
            ])
            ->assertRedirect(route('parent.messages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('parent_messages', [
            'academy_id' => $academy->id,
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'subject' => 'پیگیری روند یادگیری',
            'status' => 'open',
        ]);

        $this->actingAs($parent)
            ->post(route('parent.counseling.store'), [
                'target' => $student->id . ':' . $academy->id,
                'subject' => 'جلسه درباره برنامه درسی',
                'details' => 'می‌خواهیم درباره برنامه‌ریزی هفتگی صحبت کنیم.',
                'preferred_at' => now()->addDays(3)->format('Y-m-d\\TH:i'),
            ])
            ->assertRedirect(route('parent.counseling.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('counseling_requests', [
            'academy_id' => $academy->id,
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'subject' => 'جلسه درباره برنامه درسی',
            'status' => 'requested',
        ]);

        $this->actingAs($parent)
            ->get(route('parent.messages.index'))
            ->assertOk()
            ->assertSee('پیگیری روند یادگیری')
            ->assertSee($student->name);

        $this->actingAs($parent)
            ->get(route('parent.counseling.index'))
            ->assertOk()
            ->assertSee('جلسه درباره برنامه درسی');
    }

    public function test_parent_cannot_send_a_message_for_an_unlinked_student_or_unassigned_academy(): void
    {
        $workspace = $this->workspace();
        $parent = $workspace['parent'];
        $unlinkedStudent = $workspace['unlinkedStudent'];
        $academy = $workspace['academy'];

        $this->actingAs($parent)
            ->post(route('parent.messages.store'), [
                'target' => $unlinkedStudent->id . ':' . $academy->id,
                'category' => 'complaint',
                'subject' => 'نباید ثبت شود',
                'body' => 'این پیام نباید ثبت شود چون این حساب والد به دانش‌آموز متصل نیست.',
            ])
            ->assertNotFound();

        $otherAcademy = Academy::query()->create([
            'owner_id' => $workspace['owner']->id,
            'name' => 'آموزشگاه دیگر',
            'slug' => 'other-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);

        $this->actingAs($parent)
            ->post(route('parent.messages.store'), [
                'target' => $workspace['student']->id . ':' . $otherAcademy->id,
                'category' => 'question',
                'subject' => 'نباید ثبت شود',
                'body' => 'این آموزشگاه برای این دانش‌آموز membership فعال ندارد.',
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('parent_messages', 0);
    }

    public function test_parent_can_only_see_linked_children_attendance_and_assignment_or_exam_results(): void
    {
        $workspace = $this->workspace();
        $parent = $workspace['parent'];
        $student = $workspace['student'];
        $otherStudent = $workspace['unlinkedStudent'];
        $course = $workspace['course'];
        $classroom = $workspace['classroom'];
        $teacher = $workspace['teacher'];

        Attendance::query()->create([
            'classroom_id' => $classroom->id,
            'student_id' => $student->id,
            'marked_by' => $teacher->id,
            'attendance_date' => today()->toDateString(),
            'status' => 'present',
            'note' => 'حضور ثبت شد',
        ]);

        Attendance::query()->create([
            'classroom_id' => $classroom->id,
            'student_id' => $otherStudent->id,
            'marked_by' => $teacher->id,
            'attendance_date' => today()->toDateString(),
            'status' => 'absent',
            'note' => 'اطلاعات غیرمرتبط',
        ]);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'title' => 'تمرین تصحیح‌شده والد',
            'instructions' => 'متن تمرین',
            'max_score' => 20,
            'status' => 'published',
        ]);

        AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'content' => 'پاسخ',
            'submitted_at' => now()->subDay(),
            'score' => 18,
            'graded_at' => now(),
            'graded_by' => $teacher->id,
        ]);

        $exam = Exam::query()->create([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'title' => 'آزمون نهایی والد',
            'duration_minutes' => 30,
            'attempts_allowed' => 1,
            'status' => 'published',
        ]);

        ExamAttempt::query()->create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'attempt_number' => 1,
            'started_at' => now()->subHour(),
            'submitted_at' => now()->subMinutes(30),
            'score' => 17,
            'status' => 'graded',
        ]);

        $this->actingAs($parent)
            ->get(route('parent.attendance.index'))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('حاضر')
            ->assertDontSee('اطلاعات غیرمرتبط');

        $this->actingAs($parent)
            ->get(route('parent.results.index'))
            ->assertOk()
            ->assertSee('تمرین تصحیح‌شده والد')
            ->assertSee('آزمون نهایی والد')
            ->assertSee($student->name)
            ->assertDontSee($otherStudent->name);
    }

    public function test_parent_attendance_and_dashboard_are_scoped_to_parent_academies_for_the_same_child(): void
    {
        $workspace = $this->workspace();
        $parent = $workspace['parent'];
        $student = $workspace['student'];
        $owner = $workspace['owner'];
        $teacher = $workspace['teacher'];
        $firstClassroom = $workspace['classroom'];

        Attendance::query()->create([
            'classroom_id' => $firstClassroom->id,
            'student_id' => $student->id,
            'marked_by' => $teacher->id,
            'attendance_date' => today()->toDateString(),
            'status' => 'present',
            'note' => 'حضور آموزشگاه مجاز',
        ]);

        $otherAcademy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آموزشگاه خارج از محدوده والد',
            'slug' => 'other-academy-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);
        $otherAcademy->users()->attach($student->id, [
            'role' => 'student',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $otherCourse = Course::query()->create([
            'academy_id' => $otherAcademy->id,
            'created_by' => $owner->id,
            'title' => 'دوره آموزشگاه دوم',
            'slug' => 'other-course-' . Str::lower(Str::random(8)),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now()->subDay(),
        ]);
        $otherClassroom = Classroom::query()->create([
            'academy_id' => $otherAcademy->id,
            'course_id' => $otherCourse->id,
            'title' => 'کلاس محرمانه آموزشگاه دوم',
            'code' => 'OTHER-' . Str::upper(Str::random(8)),
            'capacity' => 20,
            'status' => 'active',
        ]);
        $otherClassroom->students()->attach($student->id, [
            'status' => 'active',
            'enrolled_at' => now(),
        ]);
        $student->enrollments()->create([
            'course_id' => $otherCourse->id,
            'classroom_id' => $otherClassroom->id,
            'status' => 'active',
            'payment_status' => 'unpaid',
            'paid_amount' => 0,
            'started_at' => now()->subDay(),
        ]);
        Attendance::query()->create([
            'classroom_id' => $otherClassroom->id,
            'student_id' => $student->id,
            'marked_by' => $teacher->id,
            'attendance_date' => today()->subDay()->toDateString(),
            'status' => 'absent',
            'note' => 'این رکورد نباید به والد نشان داده شود',
        ]);
        Assignment::query()->create([
            'course_id' => $otherCourse->id,
            'classroom_id' => $otherClassroom->id,
            'teacher_id' => $teacher->id,
            'title' => 'تکلیف محرمانه آموزشگاه دوم',
            'instructions' => 'این تکلیف خارج از محدودهٔ والد است.',
            'due_at' => now()->addDays(2),
            'max_score' => 20,
            'status' => 'published',
        ]);

        $this->actingAs($parent)
            ->get(route('parent.attendance.index'))
            ->assertOk()
            ->assertSee('حضور آموزشگاه مجاز')
            ->assertDontSee('کلاس محرمانه آموزشگاه دوم')
            ->assertDontSee('این رکورد نباید به والد نشان داده شود');

        $dashboard = app(ParentDashboardService::class)->build($parent);
        $child = $dashboard['children']->firstWhere('id', $student->id);

        $this->assertNotNull($child);
        $this->assertSame(1, $child->dashboard_courses);
        $this->assertSame(1, $child->dashboard_attendance_total);
        $this->assertSame(0, $child->dashboard_pending);
        $this->assertFalse($dashboard['pendingAssignmentItems']->contains(
            fn ($item) => $item->title === 'تکلیف محرمانه آموزشگاه دوم'
        ));
        $this->assertFalse($dashboard['recentAttendance']->contains(
            fn ($row) => $row->classroom_title === 'کلاس محرمانه آموزشگاه دوم'
        ));
    }

    public function test_teacher_proposes_counseling_time_owner_confirms_it_and_parent_sees_final_status(): void
    {
        $workspace = $this->workspace();
        $teacher = $workspace['teacher'];
        $owner = $workspace['owner'];
        $parent = $workspace['parent'];
        $student = $workspace['student'];
        $academy = $workspace['academy'];

        $teacherRole = $teacher->roles()->where('roles.slug', 'teacher')->firstOrFail();
        $permissionIds = [];
        foreach (['live_classes.view', 'live_classes.manage'] as $permissionName) {
            $permission = Permission::query()->firstOrCreate(
                ['name' => $permissionName],
                ['label' => $permissionName, 'group' => 'live_classes'],
            );
            $permissionIds[] = $permission->id;
        }
        $teacherRole->permissions()->syncWithoutDetaching($permissionIds);

        $request = CounselingRequest::query()->create([
            'academy_id' => $academy->id,
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'subject' => 'هماهنگی برنامهٔ مطالعاتی',
            'details' => 'درخواست مشاوره برای برنامهٔ هفتگی',
            'preferred_at' => now()->addDays(4),
            'status' => 'requested',
        ]);

        $proposedAt = now()->addDays(5)->setTime(13, 30);

        $this->actingAs($teacher)
            ->get(route('teacher.counseling-requests.index'))
            ->assertOk()
            ->assertSee('هماهنگی برنامهٔ مطالعاتی');

        $this->actingAs($teacher)
            ->post(route('teacher.counseling-requests.propose', $request->id), [
                'proposed_at' => $proposedAt->format('Y-m-d\TH:i'),
                'proposal_notes' => 'می‌توانیم پس از کلاس آنلاین صحبت کنیم.',
            ])
            ->assertRedirect(route('teacher.counseling-requests.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('counseling_requests', [
            'id' => $request->id,
            'status' => 'proposed',
            'proposed_by' => $teacher->id,
            'proposal_notes' => 'می‌توانیم پس از کلاس آنلاین صحبت کنیم.',
        ]);

        $this->actingAs($parent)
            ->get(route('parent.counseling.index'))
            ->assertOk()
            ->assertSee('پیشنهاد زمان از مدرس')
            ->assertSee('می‌توانیم پس از کلاس آنلاین صحبت کنیم.');

        $this->actingAs($owner)
            ->patch(route('owner.counseling.update', [$academy, $request->id]), [
                'status' => 'scheduled',
                'scheduled_at' => $proposedAt->format('Y-m-d\TH:i'),
                'response_notes' => 'زمان پیشنهادی مدرس تأیید شد.',
            ])
            ->assertRedirect(route('owner.counseling.index', $academy))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('counseling_requests', [
            'id' => $request->id,
            'status' => 'scheduled',
            'scheduled_at' => $proposedAt->format('Y-m-d H:i:s'),
            'handled_by' => $owner->id,
        ]);

        $this->actingAs($parent)
            ->get(route('parent.counseling.index'))
            ->assertOk()
            ->assertSee('زمان جلسه تعیین شده')
            ->assertSee('زمان قطعی')
            ->assertSee('زمان پیشنهادی مدرس')
            ->assertSee('زمان پیشنهادی مدرس تأیید شد.');
    }

    public function test_parent_sees_unsubmitted_and_pending_assignment_statuses_but_not_other_classroom_work(): void
    {
        $workspace = $this->workspace();
        $parent = $workspace['parent'];
        $student = $workspace['student'];
        $otherStudent = $workspace['unlinkedStudent'];
        $course = $workspace['course'];
        $classroom = $workspace['classroom'];
        $teacher = $workspace['teacher'];

        $unsubmitted = Assignment::query()->create([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'title' => 'تکلیف هنوز ارسال‌نشده والد',
            'instructions' => 'ارسال یک پاسخ کوتاه',
            'due_at' => now()->addDays(2),
            'max_score' => 20,
            'status' => 'published',
        ]);

        $pending = Assignment::query()->create([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'title' => 'تکلیف ارسال‌شده منتظر تصحیح والد',
            'instructions' => 'منتظر بررسی مدرس',
            'due_at' => now()->addDay(),
            'max_score' => 20,
            'status' => 'published',
        ]);

        AssignmentSubmission::query()->create([
            'assignment_id' => $pending->id,
            'student_id' => $student->id,
            'content' => 'پاسخ دانش‌آموز',
            'submitted_at' => now()->subHours(3),
        ]);

        $otherClassroom = Classroom::query()->create([
            'academy_id' => $workspace['academy']->id,
            'course_id' => $course->id,
            'title' => 'کلاس دیگر بدون این دانش‌آموز',
            'code' => 'P-' . strtoupper(Str::random(6)),
            'capacity' => 20,
            'status' => 'active',
        ]);
        $otherClassroom->teachers()->attach($teacher->id);
        $otherClassroom->students()->attach($otherStudent->id, [
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        Assignment::query()->create([
            'course_id' => $course->id,
            'classroom_id' => $otherClassroom->id,
            'teacher_id' => $teacher->id,
            'title' => 'تکلیف کلاس دیگر که نباید دیده شود',
            'instructions' => 'فقط برای گروه دیگر',
            'due_at' => now()->addDay(),
            'max_score' => 20,
            'status' => 'published',
        ]);

        $this->actingAs($parent)
            ->get(route('parent.results.index'))
            ->assertOk()
            ->assertSee('تکلیف هنوز ارسال‌نشده والد')
            ->assertSee('ارسال نشده')
            ->assertSee('تکلیف ارسال‌شده منتظر تصحیح والد')
            ->assertSee('ارسال‌شده · در انتظار تصحیح')
            ->assertDontSee('تکلیف کلاس دیگر که نباید دیده شود');
    }

    public function test_teacher_can_reply_only_to_a_message_from_a_student_they_teach(): void
    {
        $workspace = $this->workspace();
        $teacher = $workspace['teacher'];
        $academy = $workspace['academy'];
        $student = $workspace['student'];

        $message = ParentMessage::query()->create([
            'academy_id' => $academy->id,
            'parent_id' => $workspace['parent']->id,
            'student_id' => $student->id,
            'category' => 'question',
            'subject' => 'سؤال درباره تکلیف',
            'body' => 'لطفاً درباره تکلیف راهنمایی کنید.',
            'status' => 'open',
        ]);

        $this->actingAs($teacher)
            ->patch(route('teacher.messages.reply', $message), [
                'response' => 'تمرین‌های یک تا سه را دوباره مرور کنید.',
            ])
            ->assertRedirect(route('teacher.messages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('parent_messages', [
            'id' => $message->id,
            'status' => 'responded',
            'responded_by' => $teacher->id,
            'response' => 'تمرین‌های یک تا سه را دوباره مرور کنید.',
        ]);

        $this->actingAs($workspace['parent'])
            ->get(route('parent.messages.index'))
            ->assertOk()
            ->assertSee('تمرین‌های یک تا سه را دوباره مرور کنید.');
    }

    public function test_owner_can_create_parent_account_with_forced_password_change_and_link_child(): void
    {
        $workspace = $this->workspace();

        $this->actingAs($workspace['owner'])
            ->from(route('owner.people.index', $workspace['academy']))
            ->post(route('owner.people.store-parent', $workspace['academy']), [
                'student_id' => $workspace['unlinkedStudent']->id,
                'name' => 'والد تازه',
                'email' => 'new-parent-' . Str::lower(Str::random(8)) . '@test.local',
                'mobile' => '09120000000',
                'relation' => 'مادر',
            ])
            ->assertRedirect(route('owner.people.index', $workspace['academy']))
            ->assertSessionHas('temporary_parent_password');

        $parent = User::query()->where('email', 'like', 'new-parent-%@test.local')->firstOrFail();

        $this->assertTrue((bool) $parent->must_change_password);
        $this->assertTrue($parent->hasRole('parent'));
        $this->assertTrue($parent->children()->whereKey($workspace['unlinkedStudent']->id)->exists());
        $this->assertDatabaseHas('academy_user', [
            'academy_id' => $workspace['academy']->id,
            'user_id' => $parent->id,
            'role' => 'parent',
            'status' => 'active',
        ]);
    }


    public function test_parent_dashboard_excludes_future_courses_and_partially_paid_learning_work(): void
    {
        $workspace = $this->workspace();
        $student = $workspace['student'];
        $owner = $workspace['owner'];
        $teacher = $workspace['teacher'];
        $academy = $workspace['academy'];

        $futureCourse = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره‌ای که هنوز منتشر نشده',
            'slug' => 'parent-future-' . Str::lower(Str::random(8)),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now()->addDay(),
        ]);

        $student->enrollments()->create([
            'course_id' => $futureCourse->id,
            'classroom_id' => null,
            'status' => 'active',
            'payment_status' => 'unpaid',
            'paid_amount' => 0,
            'started_at' => now(),
        ]);

        LiveClass::query()->create([
            'course_id' => $futureCourse->id,
            'teacher_id' => $teacher->id,
            'title' => 'جلسهٔ دورهٔ آینده',
            'provider' => 'Jitsi',
            'meeting_url' => 'https://meet.jit.si/parent-future-course-test',
            'scheduled_at' => now()->addDays(2),
            'scheduled_end_at' => now()->addDays(2)->addMinutes(45),
            'duration_minutes' => 45,
            'status' => 'scheduled',
        ]);

        $partiallyPaidCourse = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره با پرداخت ناقص',
            'slug' => 'parent-partial-' . Str::lower(Str::random(8)),
            'status' => 'published',
            'access_type' => 'paid',
            'price' => 20000,
            'published_at' => now()->subDay(),
        ]);

        $student->enrollments()->create([
            'course_id' => $partiallyPaidCourse->id,
            'classroom_id' => null,
            'status' => 'active',
            'payment_status' => 'pending',
            'price_amount' => 20000,
            'paid_amount' => 1000,
            'started_at' => now(),
        ]);

        Assignment::query()->create([
            'course_id' => $partiallyPaidCourse->id,
            'teacher_id' => $teacher->id,
            'title' => 'تکلیف دوره با پرداخت ناقص',
            'instructions' => 'این تکلیف نباید در پنل والد نمایش داده شود.',
            'due_at' => now()->addDays(3),
            'max_score' => 100,
            'status' => 'published',
        ]);

        LiveClass::query()->create([
            'course_id' => $partiallyPaidCourse->id,
            'teacher_id' => $teacher->id,
            'title' => 'جلسهٔ دوره با پرداخت ناقص',
            'provider' => 'Jitsi',
            'meeting_url' => 'https://meet.jit.si/parent-partial-payment-test',
            'scheduled_at' => now()->addDays(3),
            'scheduled_end_at' => now()->addDays(3)->addMinutes(45),
            'duration_minutes' => 45,
            'status' => 'scheduled',
        ]);

        $payload = app(ParentDashboardService::class)->build($workspace['parent']);
        $child = $payload['children']->firstWhere('id', $student->id);

        $this->assertNotNull($child);
        $this->assertSame(1, $child->dashboard_courses);
        $this->assertSame(0, $child->dashboard_pending);
        $this->assertFalse($payload['pendingAssignmentItems']->contains(
            fn ($item) => $item->title === 'تکلیف دوره با پرداخت ناقص'
        ));
        $this->assertFalse($payload['upcomingLiveClasses']->contains(
            fn ($item) => in_array($item->title, [
                'جلسهٔ دورهٔ آینده',
                'جلسهٔ دوره با پرداخت ناقص',
            ], true)
        ));
    }

    public function test_parent_can_only_see_planned_meetings_for_their_linked_child(): void
    {
        $workspace = $this->workspace();
        $parent = $workspace['parent'];
        $student = $workspace['student'];
        $academy = $workspace['academy'];
        $course = $workspace['course'];
        $classroom = $workspace['classroom'];
        $teacher = $workspace['teacher'];
        $otherParent = User::factory()->create(['status' => 'active']);

        AcademyMeeting::query()->create([
            'academy_id' => $academy->id,
            'classroom_id' => $classroom->id,
            'course_id' => $course->id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'created_by' => $teacher->id,
            'title' => 'جلسه پیگیری پیشرفت فرزند',
            'message' => 'زمان جلسه تأیید شده است.',
            'scheduled_at' => now()->addDays(2),
            'scheduled_end_at' => now()->addDays(2)->addMinutes(30),
            'status' => 'scheduled',
        ]);

        AcademyMeeting::query()->create([
            'academy_id' => $academy->id,
            'classroom_id' => $classroom->id,
            'course_id' => $course->id,
            'student_id' => $student->id,
            'parent_id' => $otherParent->id,
            'created_by' => $teacher->id,
            'title' => 'جلسه خصوصی والد دیگر',
            'message' => 'این رکورد نباید برای والد جاری نمایش داده شود.',
            'scheduled_at' => now()->addDays(3),
            'scheduled_end_at' => now()->addDays(3)->addMinutes(30),
            'status' => 'scheduled',
        ]);

        $this->actingAs($parent)
            ->get(route('parent.meetings.index'))
            ->assertOk()
            ->assertSee('جلسه پیگیری پیشرفت فرزند')
            ->assertSee('زمان تأیید و نهایی شده')
            ->assertDontSee('جلسه خصوصی والد دیگر');

        $this->actingAs($workspace['unlinkedStudent'])
            ->get(route('parent.meetings.index'))
            ->assertForbidden();
    }

    private function workspace(): array
    {
        $owner = User::factory()->create(['status' => 'active']);
        $teacher = User::factory()->create(['status' => 'active']);
        $parent = User::factory()->create(['status' => 'active']);
        $student = User::factory()->create(['status' => 'active']);
        $unlinkedStudent = User::factory()->create(['status' => 'active']);

        $ownerRole = $this->role('academy-owner', ['dashboard.view', 'parents.view', 'parents.manage']);
        $teacherRole = $this->role('teacher', ['dashboard.view', 'parent_messages.view', 'parent_messages.manage']);
        $parentRole = $this->role('parent', ['dashboard.view']);
        $studentRole = $this->role('student', ['dashboard.view']);

        $owner->roles()->attach($ownerRole->id);
        $teacher->roles()->attach($teacherRole->id);
        $parent->roles()->attach($parentRole->id);
        $student->roles()->attach($studentRole->id);
        $unlinkedStudent->roles()->attach($studentRole->id);

        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی تست والد',
            'slug' => 'parent-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);

        foreach ([[$teacher, 'teacher'], [$parent, 'parent'], [$student, 'student'], [$unlinkedStudent, 'student']] as [$user, $role]) {
            $academy->users()->attach($user->id, [
                'role' => $role,
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        $course = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره تست والد',
            'slug' => 'parent-course-' . Str::lower(Str::random(8)),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now()->subDay(),
        ]);

        $classroom = Classroom::query()->create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس تست والد',
            'code' => 'P-' . strtoupper(Str::random(6)),
            'capacity' => 20,
            'status' => 'active',
        ]);

        $classroom->teachers()->attach($teacher->id);
        foreach ([$student, $unlinkedStudent] as $classStudent) {
            $classroom->students()->attach($classStudent->id, [
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
            $classStudent->enrollments()->create([
                'course_id' => $course->id,
                'classroom_id' => $classroom->id,
                'status' => 'active',
                'paid_amount' => 0,
                'started_at' => now(),
            ]);
        }

        $parent->children()->attach($student->id, ['relation' => 'مادر']);

        return compact('owner', 'teacher', 'parent', 'student', 'unlinkedStudent', 'academy', 'course', 'classroom');
    }

    private function role(string $slug, array $permissionNames): Role
    {
        $role = Role::query()->create([
            'name' => $slug,
            'slug' => $slug,
            'description' => $slug,
        ]);

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
