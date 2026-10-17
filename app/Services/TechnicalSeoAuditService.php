<?php

namespace App\Services;

use App\Models\SeoAuditRun;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

final class TechnicalSeoAuditService
{
    private const MAX_SITEMAP_BYTES = 2_000_000;
    private const MAX_HTML_BYTES = 2_000_000;

    /**
     * Crawl only the configured site's own robots.txt and sitemap URLs.
     * This deliberately does not accept arbitrary URLs from a request.
     */
    public function run(SeoAuditRun $run): void
    {
        $startedAt = microtime(true);
        $results = ['site_checks' => [], 'pages' => [], 'issues' => []];

        $run->forceFill([
            'status' => 'running',
            'started_at' => now(),
            'error_message' => null,
        ])->save();

        try {
            $baseUrl = rtrim((string) config('app.url'), '/');

            if (! $this->isHttpUrl($baseUrl)) {
                throw new \RuntimeException('APP_URL must be an absolute HTTP or HTTPS URL.');
            }

            $robotsUrl = route('seo.robots');
            $sitemapUrl = route('seo.sitemap');

            $robotsResponse = $this->request($robotsUrl);
            $robotsBody = $robotsResponse->body();
            $this->siteCheck(
                $results,
                'robots_reachable',
                $robotsResponse->successful() ? 'ok' : 'fail',
                $robotsResponse->successful() ? 'robots.txt با پاسخ موفق در دسترس است.' : 'robots.txt پاسخ موفق نمی‌دهد.',
                $robotsUrl,
                $robotsResponse->successful() ? null : 'critical'
            );

            if ($robotsResponse->successful() && ! preg_match('/^\\s*Sitemap:\\s*' . preg_quote($sitemapUrl, '/') . '\\s*$/im', $robotsBody)) {
                $this->issue($results, 'sitemap_not_declared', 'warning', 'آدرس sitemap.xml در robots.txt اعلام نشده یا با آدرس مورد انتظار یکسان نیست.', $robotsUrl);
            }

            $sitemapResponse = $this->request($sitemapUrl);
            $sitemapBody = $sitemapResponse->body();
            $this->siteCheck(
                $results,
                'sitemap_reachable',
                $sitemapResponse->successful() ? 'ok' : 'fail',
                $sitemapResponse->successful() ? 'sitemap.xml با پاسخ موفق در دسترس است.' : 'sitemap.xml پاسخ موفق نمی‌دهد.',
                $sitemapUrl,
                $sitemapResponse->successful() ? null : 'critical'
            );

            $urls = [];
            if (! $sitemapResponse->successful()) {
                $this->issue($results, 'sitemap_http_error', 'critical', 'خزش صفحات انجام نشد؛ sitemap پاسخ HTTP موفق نداد.', $sitemapUrl);
            } elseif (strlen($sitemapBody) > self::MAX_SITEMAP_BYTES) {
                $this->issue($results, 'sitemap_too_large', 'critical', 'sitemap از حد ایمن ممیزی بزرگ‌تر است و برای جلوگیری از مصرف حافظه پردازش نشد.', $sitemapUrl);
            } else {
                $xml = $this->parseSitemap($sitemapBody);

                if (! $xml) {
                    $this->issue($results, 'sitemap_invalid_xml', 'critical', 'محتوای sitemap یک XML معتبر از نوع urlset نیست.', $sitemapUrl);
                } else {
                    $rawUrls = [];
                    foreach (($xml->xpath('//*[local-name()="loc"]') ?: []) as $loc) {
                        $value = trim((string) $loc);
                        if ($value !== '') {
                            $rawUrls[] = $value;
                        }
                    }

                    $urls = array_values(array_unique($rawUrls));
                    $run->pages_found = min(count($rawUrls), 65535);

                    if (count($rawUrls) !== count($urls)) {
                        $this->issue($results, 'duplicate_sitemap_url', 'warning', 'در sitemap آدرس تکراری پیدا شد.', $sitemapUrl);
                    }

                    if ($urls === []) {
                        $this->issue($results, 'sitemap_empty', 'critical', 'sitemap هیچ URL قابل ممیزی ندارد.', $sitemapUrl);
                    }
                }
            }

            $baseParts = parse_url($baseUrl) ?: [];
            $sameSiteUrls = [];
            foreach ($urls as $url) {
                if (! $this->sameOrigin($url, $baseParts)) {
                    $this->issue($results, 'external_sitemap_url', 'warning', 'sitemap شامل URL خارج از مبدأ سایت است؛ این مورد به‌صورت خودکار خزش نمی‌شود.', $url);
                    continue;
                }

                $sameSiteUrls[] = $url;
            }

            $configuredLimit = (int) config('seo.audit_max_pages', 20);
            $limit = max(1, min($configuredLimit, 100));
            $urlsToScan = array_slice($sameSiteUrls, 0, $limit);

            if (count($sameSiteUrls) > count($urlsToScan)) {
                $this->issue(
                    $results,
                    'crawl_limit_reached',
                    'info',
                    'این ممیزی محدود و نمونه‌ای است؛ ' . count($urlsToScan) . ' صفحه از ' . count($sameSiteUrls) . ' آدرس داخلی بررسی شد. برای سایت بزرگ‌تر، ممیزی بخش‌بندی‌شده لازم است.',
                    $sitemapUrl
                );
            }

            $titleCounts = [];
            $descriptionCounts = [];
            $sitemapSet = array_fill_keys($sameSiteUrls, true);

            foreach ($urlsToScan as $url) {
                $pageStartedAt = microtime(true);
                try {
                    $response = $this->request($url);
                    $page = $this->inspectPage($response, $url, microtime(true) - $pageStartedAt, isset($sitemapSet[$url]));
                } catch (Throwable $exception) {
                    $page = [
                        'url' => $url,
                        'http_status' => null,
                        'response_time_ms' => (int) round((microtime(true) - $pageStartedAt) * 1000),
                        'title' => null,
                        'description' => null,
                        'canonical' => null,
                        'robots' => null,
                        'h1_count' => null,
                        'images_missing_alt' => null,
                        'json_ld_blocks' => null,
                        'content_type' => null,
                    ];
                    $this->issue($results, 'page_unreachable', 'critical', 'صفحه از محل اجرای ممیزی قابل دریافت نیست: ' . Str::limit($exception->getMessage(), 180), $url);
                }

                $results['pages'][] = $page;

                if (filled($page['title'] ?? null)) {
                    $key = mb_strtolower(trim($page['title']));
                    $titleCounts[$key] = ($titleCounts[$key] ?? 0) + 1;
                }
                if (filled($page['description'] ?? null)) {
                    $key = mb_strtolower(trim($page['description']));
                    $descriptionCounts[$key] = ($descriptionCounts[$key] ?? 0) + 1;
                }
            }

            foreach ($titleCounts as $title => $count) {
                if ($count > 1) {
                    $this->issue($results, 'duplicate_title', 'warning', 'عنوان تکراری در ' . $count . ' صفحهٔ نمونه دیده شد: ' . Str::limit($title, 120), null);
                }
            }
            foreach ($descriptionCounts as $description => $count) {
                if ($count > 1) {
                    $this->issue($results, 'duplicate_description', 'warning', 'توضیحات متای تکراری در ' . $count . ' صفحهٔ نمونه دیده شد: ' . Str::limit($description, 120), null);
                }
            }

            $issues = $results['issues'];
            $summary = [
                'pages_found' => count($rawUrls ?? []),
                'pages_scanned' => count($results['pages']),
                'urls_skipped' => max(0, count($urls) - count($urlsToScan)),
                'total_issues' => count($issues),
                'critical' => count(array_filter($issues, fn (array $issue): bool => $issue['severity'] === 'critical')),
                'warning' => count(array_filter($issues, fn (array $issue): bool => $issue['severity'] === 'warning')),
                'info' => count(array_filter($issues, fn (array $issue): bool => $issue['severity'] === 'info')),
                'crawl_scope' => 'sitemap_sample',
                'note' => 'این گزارش خروجی بررسی فنی HTTP/HTML است؛ دادهٔ کلیک، نمایش، رتبه یا وضعیت ایندکس Google را ادعا نمی‌کند.',
            ];

            $run->forceFill([
                'status' => 'completed',
                'active_key' => null,
                'pages_found' => min((int) ($summary['pages_found'] ?? 0), 65535),
                'pages_scanned' => min((int) $summary['pages_scanned'], 65535),
                'issues_count' => min((int) $summary['total_issues'], 65535),
                'critical_count' => min((int) $summary['critical'], 65535),
                'warning_count' => min((int) $summary['warning'], 65535),
                'info_count' => min((int) $summary['info'], 65535),
                'duration_ms' => min((int) round((microtime(true) - $startedAt) * 1000), 4294967295),
                'summary' => $summary,
                'results' => $results,
                'finished_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            $run->forceFill([
                'status' => 'failed',
                'active_key' => null,
                'error_message' => Str::limit($exception->getMessage(), 1500),
                'duration_ms' => min((int) round((microtime(true) - $startedAt) * 1000), 4294967295),
                'finished_at' => now(),
            ])->save();

            Log::error('Technical SEO audit failed.', [
                'audit_run_id' => $run->getKey(),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    private function request(string $url): Response
    {
        return Http::connectTimeout(max(1, min((int) config('seo.audit_connect_timeout_seconds', 1), 3)))
            ->timeout(max(1, min((int) config('seo.audit_request_timeout_seconds', 3), 8)))
            ->withoutRedirecting()
            ->withHeaders([
                'User-Agent' => 'SheykhanTechnicalSEOAudit/1.0',
                'Accept' => 'text/html,application/xml,text/plain;q=0.9,*/*;q=0.1',
            ])
            ->get($url);
    }

    private function inspectPage(Response $response, string $url, float $elapsedSeconds, bool $isInSitemap): array
    {
        $status = $response->status();
        $contentType = strtolower((string) $response->header('Content-Type'));
        $elapsedMs = (int) round($elapsedSeconds * 1000);
        $page = [
            'url' => $url,
            'http_status' => $status,
            'response_time_ms' => $elapsedMs,
            'title' => null,
            'description' => null,
            'canonical' => null,
            'robots' => null,
            'h1_count' => null,
            'images_missing_alt' => null,
            'json_ld_blocks' => null,
            'content_type' => $contentType,
        ];

        $results = ['issues' => []];
        if ($status >= 400) {
            $this->issue($results, 'page_http_error', $status >= 500 ? 'critical' : 'warning', 'صفحه با HTTP ' . $status . ' پاسخ داد.', $url);
        } elseif ($status >= 300) {
            $this->issue($results, 'page_redirect', 'warning', 'URL موجود در sitemap ریدایرکت می‌شود؛ مقصد نهایی و canonical باید بررسی شود.', $url);
        }

        if (! str_contains($contentType, 'text/html')) {
            $this->issue($results, 'non_html_sitemap_url', 'warning', 'URL داخل sitemap محتوای HTML برنگرداند؛ مطمئن شوید این URL واقعاً صفحهٔ قابل ایندکس است.', $url);
            $this->publishIssues($results['issues']);
            return $page;
        }

        $body = $response->body();
        if (strlen($body) > self::MAX_HTML_BYTES) {
            $this->issue($results, 'html_too_large', 'info', 'HTML از سقف تحلیل محتوا بزرگ‌تر است؛ تحلیل DOM این صفحه انجام نشد.', $url);
            $this->publishIssues($results['issues']);
            return $page;
        }

        if (! class_exists(\DOMDocument::class)) {
            $this->issue($results, 'dom_extension_missing', 'critical', 'افزونهٔ PHP DOM روی محیط اجرا در دسترس نیست؛ تحلیل HTML انجام نشد.', $url);
            $this->publishIssues($results['issues']);
            return $page;
        }

        $previous = libxml_use_internal_errors(true);
        try {
            $dom = new \DOMDocument();
            $dom->loadHTML($body, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new \DOMXPath($dom);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $titles = $xpath->query('//title');
        $page['title'] = trim((string) ($titles?->item(0)?->textContent ?? ''));

        if ($page['title'] === '') {
            $this->issue($results, 'missing_title', 'critical', 'صفحه عنوان HTML ندارد.', $url);
        } elseif (mb_strlen($page['title']) > 65) {
            $this->issue($results, 'long_title', 'info', 'عنوان بیش از ۶۵ نویسه است؛ نمایش آن ممکن است در نتایج کوتاه شود. این یک راهنمای ویرایشی است، نه معیار رتبه‌بندی گوگل.', $url);
        }

        $descriptions = $xpath->query('//meta[translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="description"]');
        $page['description'] = trim((string) ($descriptions?->item(0)?->getAttribute('content') ?? ''));
        if ($descriptions && $descriptions->length > 1) {
            $this->issue($results, 'multiple_meta_descriptions', 'warning', 'بیش از یک meta description در صفحه وجود دارد.', $url);
        } elseif ($page['description'] === '') {
            $this->issue($results, 'missing_meta_description', 'warning', 'توضیحات متا خالی است؛ موتور جستجو ممکن است snippet را از محتوای صفحه انتخاب کند.', $url);
        } elseif (mb_strlen($page['description']) > 180) {
            $this->issue($results, 'long_meta_description', 'info', 'توضیحات متا بیش از ۱۸۰ نویسه است؛ متن را از نظر وضوح و تناسب بررسی کنید. طول نمایش قطعی نیست.', $url);
        }

        $canonicalNodes = $xpath->query('//link[@rel]');
        $canonicals = [];
        if ($canonicalNodes) {
            foreach ($canonicalNodes as $node) {
                $rel = preg_split('/\\s+/', strtolower(trim($node->getAttribute('rel')))) ?: [];
                if (in_array('canonical', $rel, true)) {
                    $href = trim($node->getAttribute('href'));
                    if ($href !== '') {
                        $canonicals[] = $href;
                    }
                }
            }
        }

        $page['canonical'] = $canonicals[0] ?? null;
        if (count($canonicals) > 1) {
            $this->issue($results, 'multiple_canonicals', 'critical', 'صفحه بیش از یک canonical دارد؛ سیگنال‌های canonical را به یک مقدار روشن محدود کنید.', $url);
        } elseif ($page['canonical'] === null) {
            $this->issue($results, 'missing_canonical', 'warning', 'صفحه canonical صریح ندارد.', $url);
        } else {
            $canonicalParts = parse_url($page['canonical']);
            if (! is_array($canonicalParts) || empty($canonicalParts['scheme']) || empty($canonicalParts['host'])) {
                $this->issue($results, 'invalid_canonical', 'warning', 'canonical باید یک URL مطلق و قابل تفسیر باشد.', $url);
            } elseif (isset($canonicalParts['query']) || isset($canonicalParts['fragment'])) {
                $this->issue($results, 'canonical_query_or_fragment', 'warning', 'canonical شامل query یا fragment است؛ مطمئن شوید URL ترجیحی بدون پارامتر است.', $url);
            } elseif (! $this->sameOrigin($page['canonical'], parse_url((string) config('app.url')) ?: [])) {
                $this->issue($results, 'cross_origin_canonical', 'warning', 'canonical به مبدأ دیگری اشاره می‌کند؛ فقط در صورت قصد آگاهانه برای ادغام URLها نگه داشته شود.', $url);
            } elseif ($this->normalizeComparableUrl($page['canonical']) !== $this->normalizeComparableUrl($url)) {
                $this->issue($results, 'non_self_canonical', 'info', 'canonical با URL این صفحه یکسان نیست؛ مقصد و هدف ادغام را بررسی کنید.', $url);
            }
        }

        $robotsNodes = $xpath->query('//meta[translate(@name,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="robots"]');
        $robots = [];
        if ($robotsNodes) {
            foreach ($robotsNodes as $node) {
                $robots[] = strtolower(trim($node->getAttribute('content')));
            }
        }
        $page['robots'] = $robots === [] ? null : implode(', ', $robots);
        $noindex = (bool) preg_match('/(?:^|[,\\s])noindex(?:$|[,\\s])/i', implode(',', $robots));
        if ($noindex && $isInSitemap) {
            $this->issue($results, 'noindex_in_sitemap', 'warning', 'صفحه هم‌زمان در sitemap است و noindex دارد؛ سیاست انتشار باید یکدست شود.', $url);
        }

        $h1Nodes = $xpath->query('//h1');
        $page['h1_count'] = $h1Nodes?->length ?? 0;
        if ($page['h1_count'] === 0) {
            $this->issue($results, 'missing_h1', 'warning', 'صفحه H1 ندارد؛ ساختار عنوان اصلی را بررسی کنید.');
        } elseif ($page['h1_count'] > 1) {
            $this->issue($results, 'multiple_h1', 'info', 'صفحه بیش از یک H1 دارد؛ از نظر معنا و ساختار محتوا بررسی کنید.');
        }

        $missingAlt = $xpath->query('//img[not(@alt) or normalize-space(@alt)=""]');
        $page['images_missing_alt'] = $missingAlt?->length ?? 0;
        if ($page['images_missing_alt'] > 0) {
            $this->issue($results, 'images_missing_alt', 'warning', 'تعدادی تصویر alt ندارد یا alt آن خالی است؛ تصویرهای تزئینی عمداً می‌توانند alt خالی داشته باشند.', $url);
        }

        $jsonLdNodes = $xpath->query('//script[translate(@type,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz")="application/ld+json"]');
        $page['json_ld_blocks'] = $jsonLdNodes?->length ?? 0;
        if ($jsonLdNodes) {
            foreach ($jsonLdNodes as $block) {
                $json = trim((string) $block->textContent);
                if ($json === '') {
                    $this->issue($results, 'empty_json_ld', 'warning', 'یک بلوک JSON-LD خالی است.', $url);
                    continue;
                }

                json_decode($json, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->issue($results, 'invalid_json_ld', 'critical', 'یک بلوک JSON-LD JSON معتبر نیست: ' . json_last_error_msg(), $url);
                }
            }
        }

        if ($elapsedMs > 2500) {
            $this->issue($results, 'slow_http_response', 'info', 'زمان دریافت پاسخ از محل اجرای ممیزی بیش از ۲٫۵ ثانیه بود؛ این اندازه‌گیری معادل LCP یا Core Web Vitals مرورگر نیست.', $url);
        }

        $this->publishIssues($results['issues']);
        return $page;
    }

    private function parseSitemap(string $body): ?SimpleXMLElement
    {
        if (! function_exists('simplexml_load_string')) {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($body, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        } catch (Throwable) {
            $xml = false;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (! $xml instanceof SimpleXMLElement) {
            return null;
        }

        $name = strtolower($xml->getName());
        if (! in_array($name, ['urlset', 'sitemapindex'], true)) {
            return null;
        }

        return $xml;
    }

    private function siteCheck(array &$results, string $code, string $status, string $message, string $url, ?string $severity = null): void
    {
        $results['site_checks'][] = compact('code', 'status', 'message', 'url');
        if ($severity !== null) {
            $this->issue($results, $code, $severity, $message, $url);
        }
    }

    private function issue(array &$results, string $code, string $severity, string $message, ?string $url = null): void
    {
        $results['issues'][] = [
            'code' => $code,
            'severity' => $severity,
            'message' => $message,
            'url' => $url,
        ];
    }

    private function publishIssues(array $issues): void
    {
        // Page-scoped findings are accumulated by reference on the service instance
        // in a separate request-safe slot; see setRunResults() below.
        foreach ($issues as $issue) {
            $this->currentIssues[] = $issue;
        }
    }

    private array $currentIssues = [];

    private function isHttpUrl(string $url): bool
    {
        $parts = parse_url($url);

        return is_array($parts)
            && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
            && filled($parts['host'] ?? null)
            && ! isset($parts['user'])
            && ! isset($parts['pass']);
    }

    private function sameOrigin(string $url, array $baseParts): bool
    {
        $parts = parse_url($url);
        if (! is_array($parts) || ! in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true) || empty($parts['host'])) {
            return false;
        }

        $baseScheme = strtolower((string) ($baseParts['scheme'] ?? ''));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $basePort = (int) ($baseParts['port'] ?? ($baseScheme === 'https' ? 443 : 80));
        $port = (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80));

        return strtolower((string) $parts['host']) === strtolower((string) ($baseParts['host'] ?? ''))
            && $scheme === $baseScheme
            && $port === $basePort
            && ! isset($parts['user'])
            && ! isset($parts['pass']);
    }

    private function normalizeComparableUrl(string $url): string
    {
        $parts = parse_url($url) ?: [];
        $path = rtrim((string) ($parts['path'] ?? '/'), '/');

        return strtolower((string) ($parts['scheme'] ?? '')) . '://'
            . strtolower((string) ($parts['host'] ?? ''))
            . (isset($parts['port']) ? ':' . $parts['port'] : '')
            . ($path === '' ? '/' : $path);
    }
}