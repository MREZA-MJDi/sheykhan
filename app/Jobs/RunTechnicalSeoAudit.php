<?php

namespace App\Jobs;

use App\Models\SeoAuditRun;
use App\Services\TechnicalSeoAuditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

final class RunTechnicalSeoAudit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    // Keep this below the default database queue retry_after (90 seconds).
    public int $timeout = 75;

    public function __construct(public readonly int $auditRunId)
    {
    }

    public function handle(TechnicalSeoAuditService $auditor): void
    {
        $run = SeoAuditRun::query()->find($this->auditRunId);

        if (! $run || ! $run->isActive()) {
            return;
        }

        $auditor->run($run);
    }

    public function failed(Throwable $exception): void
    {
        SeoAuditRun::query()
            ->whereKey($this->auditRunId)
            ->whereIn('status', ['queued', 'running'])
            ->update([
                'status' => 'failed',
                'active_key' => null,
                'error_message' => Str::limit($exception->getMessage(), 1500),
                'finished_at' => now(),
                'updated_at' => now(),
            ]);
    }
}