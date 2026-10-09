<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class MeasureCriticalRequestTime
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = hrtime(true);
        $response = $next($request);

        $routeName = $request->route()?->getName();
        $timedRoutes = (array) config('performance.timed_routes', []);

        if (!is_string($routeName) || !in_array($routeName, $timedRoutes, true)) {
            return $response;
        }

        $durationMs = (hrtime(true) - $startedAt) / 1_000_000;
        $formattedDuration = number_format($durationMs, 1, '.', '');

        // Visible in browser DevTools -> Network -> response headers.
        $response->headers->set('Server-Timing', 'app;dur=' . $formattedDuration);

        $thresholdMs = (float) config('performance.slow_request_threshold_ms', 1500);

        if ($durationMs >= $thresholdMs) {
            Log::warning('Slow critical application route', [
                'route' => $routeName,
                'method' => $request->method(),
                'duration_ms' => round($durationMs, 1),
                'status_code' => $response->getStatusCode(),
                'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 1),
            ]);
        }

        return $response;
    }
}
