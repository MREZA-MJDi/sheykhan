<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Seo\UpdateSeoRequest;
use App\Services\OwnerSeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class SeoController extends Controller
{
    public function index(OwnerSeoService $seo): View
    {
        return view('owner.seo.index', $seo->overview(request()->user()));
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
