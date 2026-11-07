<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Seo\UpdateSeoRequest;
use App\Services\OwnerSeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

final class SeoController extends Controller
{
    public function index(OwnerSeoService $seo): View
    {
        $data = $seo->overview(request()->user());
        $items = collect($data['items']);

        $search = trim((string) request()->query('q', ''));
        $type = (string) request()->query('type', '');
        $status = (string) request()->query('status', '');

        $allowedTypes = ['course', 'content', 'blog', 'product', 'teacher'];
        $allowedStatuses = ['ready', 'partial', 'empty', 'noindex', 'findings'];

        if (! in_array($type, $allowedTypes, true)) {
            $type = '';
        }

        if (! in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $filtered = $items->filter(function (array $item) use ($search, $type, $status): bool {
            if ($type !== '' && $item['type'] !== $type) {
                return false;
            }

            $matchesStatus = match ($status) {
                'ready' => $item['score'] >= 80,
                'partial' => $item['score'] > 0 && $item['score'] < 80,
                'empty' => $item['score'] === 0,
                'noindex' => str_contains((string) ($item['robots'] ?? ''), 'noindex'),
                'findings' => $item['issue_count'] > 0,
                default => true,
            };

            if (! $matchesStatus) {
                return false;
            }

            if ($search === '') {
                return true;
            }

            $haystack = implode(' ', [
                $item['label'],
                $item['academy'] ?? '',
                $item['audit_title'] ?? '',
                $item['audit_description'] ?? '',
                $item['url'] ?? '',
            ]);

            return mb_stripos($haystack, $search) !== false;
        })->values();

        $perPage = 25;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $data['items'] = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
        $data['filters'] = [
            'q' => $search,
            'type' => $type,
            'status' => $status,
        ];
        $data['filteredTotal'] = $filtered->count();

        return view('owner.seo.index', $data);
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
