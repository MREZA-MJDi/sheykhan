<?php

namespace Tests\Feature;

use Database\Seeders\AcademyFoundationSeeder;
use Database\Seeders\AcademicReferenceSeeder;
use Database\Seeders\PublicContentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicContentSeederDemoModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_content_seeder_handles_the_single_student_demo_foundation(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $this->seed(RolePermissionSeeder::class);
        $this->seed(AcademicReferenceSeeder::class);

        $originalEnvironment = app()->environment();

        try {
            app()->detectEnvironment(fn () => 'local');

            $this->seed(AcademyFoundationSeeder::class);
            $this->seed(PublicContentSeeder::class);

            $this->assertDatabaseHas('achievements', [
                'title' => 'افتخارآفرین شماره 1',
                'student_id' => \App\Models\User::where('email', 'student.armin@sheykhan.test')->value('id'),
            ]);

            $this->assertDatabaseMissing('achievements', [
                'title' => 'افتخارآفرین شماره 2',
            ]);

            $this->assertDatabaseMissing('achievements', [
                'title' => 'افتخارآفرین شماره 3',
            ]);
        } finally {
            app()->detectEnvironment(fn () => $originalEnvironment);
        }
    }
}
