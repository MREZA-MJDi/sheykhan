<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Content\StoreContentRequest;
use App\Http\Requests\Owner\Content\UpdateContentRequest;
use App\Models\AcademyContent;
use App\Services\OwnerContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class ContentController extends Controller
{
    public function index(OwnerContentService $content): View
    {
        return view('owner.content.index', $content->index(request()->user()));
    }

    public function create(OwnerContentService $content): View
    {
        return view('owner.content.form', [
            'content' => new AcademyContent(['status' => 'draft', 'type' => 'article']),
            ...$content->formData(request()->user()),
        ]);
    }

    public function store(StoreContentRequest $request, OwnerContentService $content): RedirectResponse
    {
        $item = $content->create($request->user(), $request->validated());

        return redirect()->route('owner.content.edit', $item)->with('success', 'محتوا با موفقیت ساخته شد.');
    }

    public function edit(AcademyContent $content, OwnerContentService $service): View
    {
        $item = $service->owned(request()->user(), $content)->load(['academy:id,name','category:id,title,academy_id']);

        return view('owner.content.form', [
            'content' => $item,
            ...$service->formData(request()->user(), $item),
        ]);
    }

    public function update(UpdateContentRequest $request, AcademyContent $content, OwnerContentService $service): RedirectResponse
    {
        $item = $service->owned($request->user(), $content);
        $service->update($request->user(), $item, $request->validated());

        return redirect()->route('owner.content.edit', $item)->with('success', 'محتوا به‌روزرسانی شد.');
    }
}
