<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class PruneProtectedMediaEvents extends Command
{
    protected $signature = 'protected-media-events:prune
        {--days= : Delete events older than this many days (minimum 30)}
        {--force : Perform deletion; without this option the command only reports counts}';

    protected $description = 'Safely inspect or prune old protected-media access events.';

    /** @var list<string> */
    private const TABLES = [
        'protected_file_views',
        'protected_learning_media_views',
        'spot_player_playback_views',
    ];

    public function handle(): int
    {
        $daysOption = $this->option('days');
        if ($daysOption === null || filter_var($daysOption, FILTER_VALIDATE_INT) === false || (int) $daysOption < 30) {
            $this->error('برای جلوگیری از حذف ناخواسته، --days باید یک عدد صحیح و حداقل ۳۰ باشد.');
            return self::INVALID;
        }

        $days = (int) $daysOption;
        $cutoff = CarbonImmutable::now()->subDays($days);
        $execute = (bool) $this->option('force');
        $total = 0;

        $this->components->info(
            ($execute ? 'حذف رویدادهای قدیمی‌تر از ' : 'پیش‌نمایش رویدادهای قدیمی‌تر از ')
            . $cutoff->toDateTimeString()
        );

        foreach (self::TABLES as $table) {
            $query = DB::table($table)->where('viewed_at', '<', $cutoff);
            $count = (clone $query)->count();
            $deleted = 0;

            if ($execute && $count > 0) {
                $query->orderBy('id')->chunkById(500, function ($rows) use ($table, &$deleted): void {
                    $deleted += DB::table($table)
                        ->whereIn('id', $rows->pluck('id'))
                        ->delete();
                });
            }

            $total += $execute ? $deleted : $count;
            $this->line(sprintf(
                ' - %s: %s رکورد %s',
                $table,
                number_format($execute ? $deleted : $count),
                $execute ? 'حذف شد' : 'در انتظار حذف'
            ));
        }

        $this->newLine();
        $this->components->info(
            $execute
                ? number_format($total) . ' رویداد حذف شد.'
                : number_format($total) . ' رویداد پیدا شد؛ برای حذف واقعی، --force را اضافه کن.'
        );

        return self::SUCCESS;
    }
}
