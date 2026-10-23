<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\LearningResource;
use App\Services\MediaService;
use App\Services\ParentLearningResourceService;
use App\Services\StudentLearningResourceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ResourceController extends Controller
{
    public function index(
        Request $request,
        ParentLearningResourceService $parentResources,
        StudentLearningResourceService $studentResources,
    ): View {
        $data = $request->validate([
            'child' => ['nullable', 'integer', 'min:1'],
        ]);

        $parent = $request->user();
        $children = $parentResources->children($parent);
        $selectedChild = null;
        $resources = null;

        if ($children->isNotEmpty()) {
            $selectedChild = isset($data['child'])
                ? $children->firstWhere('id', (int) $data['child'])
                : $children->first();

            abort_if(isset($data['child']) && !$selectedChild, 404);

            if ($selectedChild) {
                $resources = $studentResources->paginate($selectedChild);
            }
        }

        return view('parent.resources.index', compact('children', 'selectedChild', 'resources'));
    }

    public function view(
        Request $request,
        LearningResource $resource,
        ParentLearningResourceService $parentResources,
        MediaService $media,
    ) {
        $data = $request->validate([
            'child' => ['required', 'integer', 'min:1'],
        ]);

        abort_unless(
            $parentResources->canAccess($request->user(), (int) $data['child'], $resource),
            404
        );

        $resource->loadMissing('media:id,disk,path,original_name,mime_type,size,status');
        abort_unless($resource->media && $resource->media->status === 'active', 404);

        return $media->inline($resource->media);
    }
}
