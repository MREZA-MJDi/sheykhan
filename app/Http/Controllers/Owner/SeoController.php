<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Seo\UpdateSeoRequest;
use App\Services\OwnerSeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

final class SeoController extends Controller
{
    public function index(Request $request, OwnerSeoService $seo): View
    {
        $overview = $seo->overview($request->user());
        $allItems = collect($overview['items']);

        $query = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', '');
        $status = (string) $request->query('status', '');

        $allowedTypes = ['course', 'content', 'blog', 'product', 'teacher'];
        $allowedStatuses = ['attention', 'ready', 'unconfigured', 'noindex'];

        if (! in_array($type, $allowedTypes, true)) {
            $type = '';
        }

        if (! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $filtered = $allItems
            ->filter(function (array $item) use ($query, $type, $status): bool {
                if ($type !== '' && $item['type'] !== $type) {
                    return false;
                }

                if ($status === 'attention' && (int) ($item['issue_count'] ?? 0) === 0) {
                    return false;
                }

                if ($status === 'ready' && ($item['status'] ?? '') !== 'ready') {
                    return false;
                }

                if ($status === 'unconfigured' && (int) ($item['score'] ?? 0) > 0) {
                    return false;
                }

                if ($status === 'noindex' && ! str_starts_with((string) ($item['robots'] ?? 'index,follow'), 'noindex')) {
                    return false;
                }

                if ($query === '') {
                    return true;
                }

                $issueText = collect($item['issues'] ?? [])
                    ->map(fn (array $issue): string => ($issue['title'] ?? '') . ' ' . ($issue['detail'] ?? ''))
                    ->implode(' ');

                $searchable = implode(' ', [
                    (string) ($item['label'] ?? ''),
                    (string) ($item['academy'] ?? ''),
                    (string) ($item['url'] ?? ''),
                    $issueText,
                ]);

                return mb_stripos($searchable, $query) !== false;
            })
            ->sortByDesc(fn (array $item): int => ((int) ($item['issue_count'] ?? 0) * 100) + (100 - (int) ($item['score'] ?? 0)))
            ->values();

        $perPage = 15;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $overview['items'] = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        $topQueries = $seo->topQueriesForPages(
            $request->user(),
            $overview['items']->getCollection(),
        );

        $pageItems = $overview['items']->getCollection()->map(
            function (array $item) use ($topQueries): array {
                $pageHash = hash('sha256', $this->normalizePageUrlForHash((string) ($item['url'] ?? '')));
                $item['top_queries'] = $topQueries[$pageHash] ?? [];

                return $item;
            }
        );

        $overview['items']->setCollection($pageItems);

        $overview['filters'] = [
            'q' => $query,
            'type' => $type,
            'status' => $status,
        ];

        $overview['counts'] = [
            'all' => $allItems->count(),
            'attention' => $allItems->where('issue_count', '>', 0)->count(),
            'ready' => $allItems->where('status', 'ready')->count(),
            'unconfigured' => $allItems->where('score', 0)->count(),
            'noindex' => $allItems->filter(
                fn (array $item): bool => str_starts_with((string) ($item['robots'] ?? 'index,follow'), 'noindex')
            )->count(),
        ];

        return view('owner.seo.index', $overview);
    }

    private function normalizePageUrlForHash(string $url): string
    {
        if ($url === '') {
            return '';
        }

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

    public function edit(string $type, int $id, OwnerSeoService $seo): View
    {
        $item = $seo->resolveOwned(request()->user(), $type, $id);
        $item->load('seoMeta');

        return view('owner.seo.form', [
            'type' => $type,
            'item' => $item,
            'seoMeta' => $item->seoMeta,
        ]);
    }

    public function update(
        UpdateSeoRequest $request,
        string $type,
        int $id,
        OwnerSeoService $seo,
    ): RedirectResponse {
        $seo->update($request->user(), $type, $id, $request->validated());

        return redirect()
            ->route('owner.seo.index')
            ->with('success', 'تنظیمات SEO با موفقیت ذخیره شد.');
    }
}
