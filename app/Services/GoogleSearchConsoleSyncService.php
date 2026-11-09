<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

final class GoogleSearchConsoleSyncService
{
    private const ROW_LIMIT = 25000;
    private const MAX_ROWS_PER_DAY_AND_DIMENSION = 50000;

    public function isConfigured(): bool
    {
        return filled(config('services.google_search_console.site_url'))
            && filled(config('services.google_search_console.client_id'))
            && filled(config('services.google_search_console.client_secret'))
            && filled(config('services.google_search_console.refresh_token'));
    }

    public function siteUrl(): ?string
    {
        $siteUrl = trim((string) config('services.google_search_console.site_url'));

        return $siteUrl !== '' ? $siteUrl : null;
    }

    /**
     * Query one day at a time, as recommended by Google's Search Analytics API.
     * Page aggregates and page/query rows are stored separately because their
     * aggregation and completeness characteristics are not interchangeable.
     *
     * @return array{page_rows:int,query_rows:int,truncated_days:int}
     */
    public function sync(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'Google Search Console is not configured. Set the four GOOGLE_SEARCH_CONSOLE_* values in the private environment.'
            );
        }

        if ($startDate->greaterThan($endDate)) {
            throw new RuntimeException('The Search Console start date must be before or equal to the end date.');
        }

        $accessToken = $this->refreshAccessToken();
        $property = rawurlencode((string) $this->siteUrl());
        $endpoint = 'https://searchconsole.googleapis.com/webmasters/v3/sites/' . $property . '/searchAnalytics/query';
        $totals = ['page_rows' => 0, 'query_rows' => 0, 'truncated_days' => 0];

        for ($day = $startDate; $day->lessThanOrEqualTo($endDate); $day = $day->addDay()) {
            $date = $day->toDateString();

            foreach ([
                'page' => ['page', 'date'],
                'query' => ['page', 'query', 'date'],
            ] as $dimension => $dimensions) {
                $startRow = 0;
                $rowsForDimension = 0;

                do {
                    $payload = [
                        'startDate' => $date,
                        'endDate' => $date,
                        'dimensions' => $dimensions,
                        'type' => 'web',
                        'rowLimit' => self::ROW_LIMIT,
                        'startRow' => $startRow,
                    ];

                    if ($dimension === 'page') {
                        $payload['aggregationType'] = 'byPage';
                    }

                    $response = Http::withToken($accessToken)
                        ->acceptJson()
                        ->connectTimeout(10)
                        ->timeout(45)
                        ->post($endpoint, $payload);

                    if (! $response->successful()) {
                        throw new RuntimeException(
                            'Google Search Console query failed with HTTP ' . $response->status() . '. Check the property and granted read-only scope.'
                        );
                    }

                    $rows = $response->json('rows', []);
                    if (! is_array($rows) || $rows === []) {
                        break;
                    }

                    $now = now();
                    $records = [];

                    foreach ($rows as $row) {
                        $keys = $row['keys'] ?? [];
                        if (! is_array($keys)) {
                            continue;
                        }

                        $pageUrl = trim((string) ($keys[0] ?? ''));
                        $query = $dimension === 'query'
                            ? mb_substr(trim((string) ($keys[1] ?? '')), 0, 1024)
                            : null;
                        $rowDate = $dimension === 'query'
                            ? (string) ($keys[2] ?? '')
                            : (string) ($keys[1] ?? '');

                        if ($pageUrl === '' || filter_var($pageUrl, FILTER_VALIDATE_URL) === false) {
                            continue;
                        }

                        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $rowDate)) {
                            continue;
                        }

                        $records[] = [
                            'date' => $rowDate,
                            'dimension' => $dimension,
                            'page_hash' => hash('sha256', $this->normalizePageUrl($pageUrl)),
                            'page_url' => $pageUrl,
                            'query_hash' => hash('sha256', $dimension === 'query' ? $this->normalizeQuery($query ?? '') : '__page_aggregate__'),
                            'query' => $query,
                            'clicks' => max(0, (int) round((float) ($row['clicks'] ?? 0))),
                            'impressions' => max(0, (int) round((float) ($row['impressions'] ?? 0))),
                            'ctr' => isset($row['ctr']) ? max(0, min(1, (float) $row['ctr'])) : null,
                            'position' => isset($row['position']) ? max(0, (float) $row['position']) : null,
                            'synced_at' => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    foreach (array_chunk($records, 500) as $chunk) {
                        DB::table('seo_performance_snapshots')->upsert(
                            $chunk,
                            ['date', 'dimension', 'page_hash', 'query_hash'],
                            ['page_url', 'query', 'clicks', 'impressions', 'ctr', 'position', 'synced_at', 'updated_at'],
                        );
                    }

                    $count = count($rows);
                    $rowsForDimension += $count;
                    $totals[$dimension . '_rows'] += count($records);

                    if ($count < self::ROW_LIMIT) {
                        break;
                    }

                    $startRow += $count;
                    if ($startRow >= self::MAX_ROWS_PER_DAY_AND_DIMENSION) {
                        $totals['truncated_days']++;
                        break;
                    }
                } while (true);
            }
        }

        return $totals;
    }

    private function refreshAccessToken(): string
    {
        $response = Http::asForm()
            ->connectTimeout(10)
            ->timeout(20)
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google_search_console.client_id'),
                'client_secret' => config('services.google_search_console.client_secret'),
                'refresh_token' => config('services.google_search_console.refresh_token'),
                'grant_type' => 'refresh_token',
            ]);

        $accessToken = $response->json('access_token');

        if (! $response->successful() || ! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException(
                'Google OAuth token refresh failed. Verify the OAuth client, refresh token and Search Console read-only consent.'
            );
        }

        return $accessToken;
    }

    private function normalizePageUrl(string $url): string
    {
        $parts = parse_url($url);
        if (! is_array($parts) || empty($parts['host'])) {
            return $url;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        $host = strtolower((string) $parts['host']);
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = rtrim((string) ($parts['path'] ?? '/'), '/') ?: '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        return $scheme . '://' . $host . $port . $path . $query;
    }

    private function normalizeQuery(string $query): string
    {
        $query = Str::lower(trim($query));
        $query = preg_replace('/\s+/u', ' ', $query) ?? $query;

        return $query;
    }
}
