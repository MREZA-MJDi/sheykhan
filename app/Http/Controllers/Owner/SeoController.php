<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Seo\UpdateSeoRequest;
use App\Services\OwnerSeoService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
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

        if (! in_array($type, ['blog', 'content'], true)) {
            $type = '';
        }

        if (! in_array($status, ['index', 'noindex'], true)) {
            $status = '';
        }

        $filtered = $items->filter(function (array $item) use ($search, $type, $status): bool {
            if ($type !== '' && $item['type'] !== $type) {
                return false;
            }

            $noindex = str_starts_with($item['robots'], 'noindex');
            if (($status === 'index' && $noindex) || ($status === 'noindex' && ! $noindex)) {
                return false;
            }

            return $search === '' || mb_stripos(
                implode(' ', [$item['label'], $item['academy'] ?? '', $item['url'] ?? '']),
                $search,
            ) !== false;
        })->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $data['items'] = new LengthAwarePaginator(
            $filtered->forPage($page, 20)->values(),
            $filtered->count(),
            20,
            $page,
            ['path' => request()->url(), 'query' => request()->query()],
        );
        $data['filters'] = compact('search', 'type', 'status');
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
            ->with('success', 'تنظیمات ایندکس صفحه ذخیره شد.');
    }
}
