<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\AuditLog;
use App\Models\AcademicGrade;
use App\Models\Role;
use App\Models\StudentOnboarding;
use App\Models\StudentProfile;
use App\Models\User;
use App\Support\NationalIdLookup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LegacyStudentOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.national_id_hmac_key' => 'phase3-test-key']);
    }

    public function test_owner_can_create_legacy_student_with_profile_membership_and_audit_log(): void
    {
        $this->seed();

        $owner = User::where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = Academy::where('slug', 'sheykhan-academy')->firstOrFail();
        $grade = AcademicGrade::where('code', '8')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('owner.people.legacy-students.store', $academy), [
                'name' => 'دانش‌آموز قدیمی تست',
                'national_id' => '1234567890',
                'grade_id' => $grade->id,
                'mobile' => '09121234567',
                'school_name' => 'مدرسه قدیمی شیخان',
                'notes' => 'ورود از بایگانی مدرسه',
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $lookup = NationalIdLookup::make('1234567890');
        $profile = StudentProfile::where('national_id_lookup', $lookup)->firstOrFail();
        $student = $profile->user()->firstOrFail();

        $this->assertSame('دانش‌آموز قدیمی تست', $student->name);
        $this->assertSame('09121234567', $student->mobile);
        $this->assertSame($grade->id, $profile->grade_id);
        $this->assertSame('legacy', $profile->registration_source);
        $this->assertSame('active', $profile->status);

        $this->assertTrue(
            $academy->students()->whereKey($student->id)->wherePivot('status', 'active')->exists()
        );

        $this->assertDatabaseHas('student_onboardings', [
            'academy_id' => $academy->id,
            'student_id' => $student->id,
            'national_id_lookup' => $lookup,
            'status' => 'activated',
            'source' => 'legacy',
        ]);

        $this->assertDatabaseHas('student_identity_locks', [
            'national_id_lookup' => $lookup,
            'user_id' => $student->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $owner->id,
            'action' => 'legacy_student_onboarded',
            'subject_type' => StudentOnboarding::class,
            'subject_id' => StudentOnboarding::where('national_id_lookup', $lookup)->value('id'),
        ]);

        $this->assertNotSame('1234567890', $profile->national_id_lookup);
    }

    public function test_same_idempotency_key_does_not_create_duplicate_legacy_student(): void
    {
        $this->seed();

        $owner = User::where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = Academy::where('slug', 'sheykhan-academy')->firstOrFail();
        $grade = AcademicGrade::where('code', '8')->firstOrFail();
        $key = (string) Str::uuid();

        $payload = [
            'name' => 'تست تکرار امن',
            'national_id' => '1111111111',
            'grade_id' => $grade->id,
            'mobile' => '09120001111',
            'school_name' => null,
            'notes' => null,
            'idempotency_key' => $key,
        ];

        $this->actingAs($owner)->post(route('owner.people.legacy-students.store', $academy), $payload)->assertRedirect();
        $this->actingAs($owner)->post(route('owner.people.legacy-students.store', $academy), $payload)->assertRedirect();

        $lookup = NationalIdLookup::make('1111111111');

        $this->assertSame(1, StudentProfile::where('national_id_lookup', $lookup)->count());
        $this->assertSame(1, StudentOnboarding::where('academy_id', $academy->id)->where('national_id_lookup', $lookup)->count());
    }

    public function test_existing_legacy_student_is_updated_instead_of_duplicated(): void
    {
        $this->seed();

        $owner = User::where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = Academy::where('slug', 'sheykhan-academy')->firstOrFail();
        $gradeSeven = AcademicGrade::where('code', '7')->firstOrFail();
        $gradeNine = AcademicGrade::where('code', '9')->firstOrFail();
        $lookup = NationalIdLookup::make('2222222222');

        $student = User::create([
            'name' => 'قدیمی',
            'email' => 'existing-legacy@example.test',
            'mobile' => '09123334444',
            'password' => 'password',
            'status' => 'active',
        ]);

        $student->roles()->attach(Role::where('slug', 'student')->value('id'));
        StudentProfile::create([
            'user_id' => $student->id,
            'student_number' => 'LEGACY-OLD',
            'grade_id' => $gradeSeven->id,
            'grade' => $gradeSeven->title,
            'national_id_lookup' => $lookup,
            'registration_source' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($owner)
            ->post(route('owner.people.legacy-students.store', $academy), [
                'name' => 'نام به‌روزشده',
                'national_id' => '2222222222',
                'grade_id' => $gradeNine->id,
                'mobile' => '09123334444',
                'school_name' => 'مدرسه جدید',
                'notes' => 'به‌روزرسانی اطلاعات بایگانی',
                'idempotency_key' => (string) Str::uuid(),
            ]);

        $response->assertRedirect();

        $student->refresh();
        $profile = $student->studentProfile()->firstOrFail();

        $this->assertSame(1, StudentProfile::where('national_id_lookup', $lookup)->count());
        $this->assertSame($student->id, $profile->user_id);
        $this->assertSame('نام به‌روزشده', $student->name);
        $this->assertSame($gradeNine->id, $profile->grade_id);
        $this->assertSame('مدرسه جدید', $profile->school_name);
    }

    public function test_duplicate_mobile_is_rejected_without_changing_existing_account(): void
    {
        $this->seed();

        $owner = User::where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = Academy::where('slug', 'sheykhan-academy')->firstOrFail();
        $grade = AcademicGrade::where('code', '8')->firstOrFail();
        $existing = User::where('email', 'student.matin@sheykhan.test')->firstOrFail();

        $response = $this->actingAs($owner)
            ->post(route('owner.people.legacy-students.store', $academy), [
                'name' => 'دانش‌آموز با موبایل تکراری',
                'national_id' => '3333333333',
                'grade_id' => $grade->id,
                'mobile' => $existing->mobile,
                'idempotency_key' => (string) Str::uuid(),
            ]);

        $response->assertSessionHasErrors('mobile');

        $this->assertDatabaseMissing('student_profiles', [
            'national_id_lookup' => NationalIdLookup::make('3333333333'),
        ]);
    }

    public function test_owner_cannot_use_legacy_onboarding_for_another_academy(): void
    {
        $this->seed();

        $owner = User::where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = Academy::where('slug', 'sheykhan-academy')->firstOrFail();
        $otherOwner = User::create([
            'name' => 'مالک دیگر',
            'email' => 'other-owner@example.test',
            'password' => 'password',
            'status' => 'active',
        ]);
        $otherOwner->roles()->attach(Role::where('slug', 'academy-owner')->value('id'));
        $otherAcademy = Academy::create([
            'owner_id' => $otherOwner->id,
            'name' => 'آموزشگاه دوم',
            'slug' => 'academy-two',
            'code' => 'AC-002',
            'status' => 'active',
        ]);

        $grade = AcademicGrade::where('code', '8')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('owner.people.legacy-students.store', $otherAcademy), [
                'name' => 'نباید ثبت شود',
                'national_id' => '4444444444',
                'grade_id' => $grade->id,
                'idempotency_key' => (string) Str::uuid(),
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('student_profiles', [
            'national_id_lookup' => NationalIdLookup::make('4444444444'),
        ]);
    }
}
