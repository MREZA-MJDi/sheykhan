<?php

namespace App\Console\Commands;

use App\Services\GoogleSearchConsoleSyncService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

final class SyncGoogleSearchConsole extends Command
{
    protected $signature = 'sheykhan:seo-search-console-sync
        {--days=28 : Number of completed days to sync}
        {--start= : Explicit start date (YYYY-MM-DD)}
        {--end= : Explicit end date (YYYY-MM-DD)}';

    protected $description = 'Import real page and query performance from Google Search Console.';

    public function handle(GoogleSearchConsoleSyncService $searchConsole): int
    {
        try {
            if (($this->option('start') && ! $this->option('end')) || (! $this->option('start') && $this->option('end'))) {
                $this->error('Set both --start and --end, or use --days.');

                return self::INVALID;
            }

            if ($this->option('start') && $this->option('end')) {
                $start = $this->parseDate((string) $this->option('start'));
                $end = $this->parseDate((string) $this->option('end'));
            } else {
                $days = filter_var($this->option('days'), FILTER_VALIDATE_INT);
                if ($days === false || $days < 1 || $days > 90) {
                    $this->error('--days must be between 1 and 90.');

                    return self::INVALID;
                }

                // Search Console usually finalizes data after a short reporting delay.
                $end = CarbonImmutable::today()->subDays(3);
                $start = $end->subDays($days - 1);
            }

            if ($start->greaterThan($end)) {
                $this->error('The start date must be before or equal to the end date.');

                return self::INVALID;
            }

            $this->info('Syncing property: ' . $searchConsole->siteUrl());
            $this->line('Date range: ' . $start->toDateString() . ' through ' . $end->toDateString());

            $result = $searchConsole->sync($start, $end);

            $this->newLine();
            $this->info('Google Search Console sync completed.');
            $this->table(
                ['Metric', 'Rows imported / updated'],
                [
                    ['Page performance rows', number_format($result['page_rows'])],
                    ['Page + query rows', number_format($result['query_rows'])],
                    ['Days reaching the API 50,000-row cap', number_format($result['truncated_sets'])],
                ],
            );

            if ($result['truncated_sets'] > 0) {
                $this->warn('Some day/dimension result sets reached the 50,000-row API cap. The imported dataset is not complete for those sets.');
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function parseDate(string $value): CarbonImmutable
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            throw new \InvalidArgumentException('Dates must use YYYY-MM-DD format.');
        }

        $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);
        if (! $date || $date->format('Y-m-d') !== $value) {
            throw new \InvalidArgumentException('A supplied date is not valid.');
        }

        return $date;
    }
}
