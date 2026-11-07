<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ProtectedMediaEventRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_retention_command_is_a_dry_run_by_default_and_deletes_only_after_confirmation(): void
    {
        $tables = [
            'protected_file_views',
            'protected_learning_media_views',
            'spot_player_playback_views',
        ];

        $old = now()->subDays(60)->toDateTimeString();
        $recent = now()->subDays(5)->toDateTimeString();

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                DB::table($table)->insert([
                    [
                        'user_id' => 999001,
                        'viewed_at' => $old,
                        'created_at' => $old,
                        'updated_at' => $old,
                    ] + $this->eventContext($table),
                    [
                        'user_id' => 999002,
                        'viewed_at' => $recent,
                        'created_at' => $recent,
                        'updated_at' => $recent,
                    ] + $this->eventContext($table),
                ]);
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->artisan('protected-media-events:prune', ['--days' => 30])
            ->expectsOutputToContain('پیش‌نمایش')
            ->assertExitCode(0);

        foreach ($tables as $table) {
            $this->assertSame(2, DB::table($table)->count(), $table . ' must remain untouched during dry run');
        }

        $this->artisan('protected-media-events:prune', ['--days' => 30, '--force' => true])
            ->expectsOutputToContain('حذف شد')
            ->assertExitCode(0);

        foreach ($tables as $table) {
            $this->assertSame(1, DB::table($table)->count(), $table . ' should keep recent history');
            $this->assertSame(999002, (int) DB::table($table)->value('user_id'));
        }
    }

    public function test_retention_command_rejects_short_or_missing_periods(): void
    {
        $this->artisan('protected-media-events:prune')
            ->expectsOutputToContain('--days')
            ->assertExitCode(2);

        $this->artisan('protected-media-events:prune', ['--days' => 14, '--force' => true])
            ->expectsOutputToContain('حداقل ۳۰')
            ->assertExitCode(2);
    }

    private function eventContext(string $table): array
    {
        return match ($table) {
            'protected_file_views' => [
                'order_id' => 999001,
                'product_file_id' => 999001,
                'entitlement_id' => 999001,
                'event_type' => 'open',
            ],
            'protected_learning_media_views' => [
                'media_id' => 999001,
                'academy_id' => null,
                'event_type' => 'open',
            ],
            default => [
                'course_id' => 999001,
                'spot_player_license_id' => 999001,
                'event_type' => 'player_open',
            ],
        };
    }
}
