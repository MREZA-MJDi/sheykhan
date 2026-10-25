<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\User;
use App\Support\NationalIdLookup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerStudentAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_student_account_and_recent_login_appears_on_dashboard(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = Academy::query()->where('slug', 'sheykhan-academy')->firstOrFail();
        $gradeId = DB::table('academic_grades')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->value('id');

        $this->assertNotNull($gradeId);

        $this->actingAs($owner)
            ->get(route('owner.people.index', $academy))
            ->assertOk()
            ->assertSee('ایجاد دانش‌آموز', false)
            ->assertSee('ساخت حساب دانش‌آموز', false)
            ->assertSee('owner-people-tabs', false);

        $response = $this->actingAs($owner)
            ->post(route('owner.people.store-student', $academy) . '?tab=create-student', [
                'name' => 'آوا تستی',
                'national_id' => '0087456321',
                'email' => 'ava.created@student-test.example',
                'mobile' => '09120001122',
                'grade_id' => $gradeId,
                'birth_date' => '2012-03-05',
                'school_name' => 'مدرسه آزمایشی',
                'notes' => 'یادداشت تستی',
            ])
            ->assertRedirect(route('owner.people.index', $academy) . '?tab=create-student')
            ->assertSessionHasNoErrors()
            ->assertSessionHas('temporary_student_password')
            ->assertSessionHas('temporary_student_email', 'ava.created@student-test.example');

        $temporaryPassword = $response->getSession()->get('temporary_student_password');
        $this->assertIsString($temporaryPassword);
        $this->assertGreaterThanOrEqual(16, strlen($temporaryPassword));

        $student = User::query()->where('email', 'ava.created@student-test.example')->firstOrFail();
        $this->assertTrue(Hash::check($temporaryPassword, $student->password));
        $this->assertTrue((bool) $student->must_change_password);
        $this->assertTrue($student->hasRole('student'));

        $this->assertDatabaseHas('academy_user', [
            'academy_id' => $academy->id,
            'user_id' => $student->id,
            'role' => 'student',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $student->id,
            'grade_id' => $gradeId,
            'school_name' => 'مدرسه آزمایشی',
            'national_id_lookup' => NationalIdLookup::make('0087456321'),
        ]);
        $this->assertDatabaseHas('student_onboardings', [
            'academy_id' => $academy->id,
            'student_id' => $student->id,
            'national_id_lookup' => NationalIdLookup::make('0087456321'),
            'source' => 'admin',
            'status' => 'activated',
            'notes' => 'یادداشت تستی',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'student_account_created',
            'subject_type' => User::class,
            'subject_id' => $student->id,
        ]);

        $this->post(route('login.store'), [
            'identifier' => 'ava.created@student-test.example',
            'password' => $temporaryPassword,
        ])->assertRedirect(route('password.change.show'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user_login',
            'subject_type' => User::class,
            'subject_id' => $student->id,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertSee('ورود اخیر دانش‌آموزان', false)
            ->assertSee('آوا تستی', false);
    }

    public function test_student_account_creation_rejects_duplicate_email_and_mobile(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = Academy::query()->where('slug', 'sheykhan-academy')->firstOrFail();
        $gradeId = DB::table('academic_grades')->where('is_active', true)->orderBy('sort_order')->value('id');
        $existing = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $existing->forceFill(['mobile' => '09120001122'])->save();
        $userCountBefore = User::query()->count();

        $this->actingAs($owner)
            ->post(route('owner.people.store-student', $academy), [
                'name' => 'دانش‌آموز تکراری',
                'national_id' => '0011122233',
                'email' => $existing->email,
                'mobile' => '09120001122',
                'grade_id' => $gradeId,
            ])
            ->assertSessionHasErrors(['email', 'mobile']);

        $this->assertSame($userCountBefore, User::query()->count(), 'A rejected duplicate account must not create a user row.');
    }


    public function test_student_account_creation_rejects_reused_national_id(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = Academy::query()->where('slug', 'sheykhan-academy')->firstOrFail();
        $gradeId = DB::table('academic_grades')->where('is_active', true)->orderBy('sort_order')->value('id');

        $this->actingAs($owner)
            ->post(route('owner.people.store-student', $academy), [
                'name' => 'دانش‌آموز اول',
                'national_id' => '0098765432',
                'email' => 'first.national.id@student-test.example',
                'mobile' => '09125550111',
                'grade_id' => $gradeId,
            ])
            ->assertRedirect(route('owner.people.index', $academy) . '?tab=create-student')
            ->assertSessionHasNoErrors();

        $usersBefore = User::query()->count();

        $this->actingAs($owner)
            ->post(route('owner.people.store-student', $academy), [
                'name' => 'دانش‌آموز دوم',
                'national_id' => '۰۰۹۸۷۶۵۴۳۲',
                'email' => 'second.national.id@student-test.example',
                'mobile' => '09125550112',
                'grade_id' => $gradeId,
            ])
            ->assertSessionHasErrors('national_id');

        $this->assertSame($usersBefore, User::query()->count());
        $this->assertDatabaseHas('student_profiles', [
            'national_id_lookup' => \App\Support\NationalIdLookup::make('0098765432'),
        ]);
    }

}
