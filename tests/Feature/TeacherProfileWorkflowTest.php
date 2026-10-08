<?php

namespace TestsFeature;

use AppModelsMedia;
use AppModelsTeacherProfile;
use AppModelsUser;
use IlluminateFoundationTestingRefreshDatabase;
use IlluminateHttpUploadedFile;
use IlluminateSupportFacadesStorage;
use TestsTestCase;

class TeacherProfileWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_update_public_profile_and_avatar(): void
    {
        $this->seed();
        Storage::fake('local');

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        $this->actingAs($teacher)
            ->get(route('teacher.profile.edit'))
            ->assertOk()
            ->assertSee('پروفایل استاد');

        $this->actingAs($teacher)
            ->patch(route('teacher.profile.update'), [
                'name' => 'سمیه احمدی',
                'email' => $teacher->email,
                'bio' => 'مدرس ریاضی با تمرکز بر آموزش مفهومی.',
                'specialization' => 'ریاضی و حل مسئله',
                'education' => 'کارشناسی ارشد ریاضی',
                'experience_years' => 9,
                'is_public' => 1,
                'avatar' => UploadedFile::fake()->image('teacher-avatar.webp', 640, 640),
            ])
            ->assertRedirect(route('teacher.profile.edit'));

        $this->assertDatabaseHas('teacher_profiles', [
            'user_id' => $teacher->id,
            'specialization' => 'ریاضی و حل مسئله',
            'experience_years' => 9,
            'is_public' => 1,
        ]);

        $profile = TeacherProfile::where('user_id', $teacher->id)->firstOrFail();

        $this->assertDatabaseHas('media_attachments', [
            'mediable_type' => TeacherProfile::class,
            'mediable_id' => $profile->id,
            'collection' => 'teacher-avatar',
        ]);

        $this->assertTrue(
            Media::where('visibility', 'public')
                ->where('collection', 'teacher-avatar')
                ->where('uploaded_by', $teacher->id)
                ->exists()
        );
    }

    public function test_public_teacher_directory_links_to_a_complete_profile(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        $this->actingAs($teacher)
            ->patch(route('teacher.profile.update'), [
                'name' => $teacher->name,
                'email' => $teacher->email,
                'bio' => 'معرفی عمومی مدرس',
                'specialization' => 'ریاضی',
                'education' => 'کارشناسی ارشد',
                'experience_years' => 7,
                'is_public' => 1,
            ])
            ->assertRedirect();

        $this->get(route('teachers.index'))
            ->assertOk()
            ->assertSee($teacher->name)
            ->assertSee(route('teachers.show', $teacher), false);

        $this->get(route('teachers.show', $teacher))
            ->assertOk()
            ->assertSee($teacher->name)
            ->assertSee('درباره مدرس');
    }

    public function test_private_teacher_profile_is_not_publicly_discoverable(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        $this->actingAs($teacher)
            ->patch(route('teacher.profile.update'), [
                'name' => $teacher->name,
                'email' => $teacher->email,
                'is_public' => 0,
            ])
            ->assertRedirect();

        $this->get(route('teachers.show', $teacher))
            ->assertNotFound();
    }
}
