<?php

namespace App\\Http\\Middleware;

use App\\Services\\OwnerCacheService;
use Closure;
use Illuminate\\Http\\Request;
use Symfony\\Component\\HttpFoundation\\Response;

class InvalidateOwnerDashboardCache
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            $request->user()
            && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && $response->getStatusCode() < 400
        ) {
            // Owner metrics are read-through cached. Clear the summary after a
            // successful write so the next page load reflects the persisted DB.
            app(OwnerCacheService::class)->forget($request->user());
        }

        return $response;
    }
}
