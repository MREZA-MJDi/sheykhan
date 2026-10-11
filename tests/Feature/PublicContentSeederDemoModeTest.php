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

    public function test_public_content_seeder_links_demo_achievements_to_correct_students(): void
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

            foreach ([
                1 => 'student.armin@sheykhan.test',
                2 => 'student.nika@sheykhan.test',
                3 => 'student.parsa@sheykhan.test',
            ] as $number => $email) {
                $this->assertDatabaseHas('achievements', [
                    'title' => 'افتخارآفرین شماره ' . $number,
                    'student_id' => \\App\\Models\\User::where('email', $email)->value('id'),
                ]);
            }

        } finally {
            app()->detectEnvironment(fn () => $originalEnvironment);
        }
    }
}
