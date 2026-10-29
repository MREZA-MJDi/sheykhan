<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\LearningResource;
use App\Services\SpotPlayerMediaDeliveryService;
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
        SpotPlayerMediaDeliveryService $spotPlayerDelivery,
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

        abort_if(
            $spotPlayerDelivery->requiresProtectedPlayer($resource->media),
            403,
            'برای جلوگیری از ارائهٔ فایل خام ویدئو، پخش باید از پلیر محافظت‌شده و لایسنس اختصاصی دانش‌آموز انجام شود.'
        );

        return redirect()->route('protected-learning-media.view', [
            'media' => $resource->media_id,
            'resource' => $resource->id,
            'child' => (int) $data['child'],
        ]);
    }
}
