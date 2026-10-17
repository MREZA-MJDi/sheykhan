<?php

namespace App\Http\Controllers\SeoSpecialist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seo\UpdateSeoRequest;
use App\Services\SeoSpecialistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SeoController extends Controller
{
    public function index(Request $request, SeoSpecialistService $seo): View
    {
        return view('owner.seo.index', $seo->overview($request->user()) + [
            'isGlobal' => true,
        ]);
    }

    public function edit(Request $request, string $type, int $id, SeoSpecialistService $seo): View
    {
        $item = $seo->resolvePublic($request->user(), $type, $id);
        $item->load('seoMeta');

        return view('owner.seo.form', [
            'type' => $type,
            'item' => $item,
            'seoMeta' => $item->seoMeta,
            'isGlobal' => true,
        ]);
    }

    public function update(
        UpdateSeoRequest $request,
        string $type,
        int $id,
        SeoSpecialistService $seo,
    ): RedirectResponse {
        $seo->update($request->user(), $type, $id, $request->validated());

        return redirect()->route('seo-specialist.index')
            ->with('success', 'تنظیمات SEO با موفقیت ذخیره شد.');
    }
}
