<?php

namespace App\Http\Controllers;

use App\Models\LearningResource;
use App\Models\Lesson;
use App\Models\Media;
use App\Services\ParentLearningResourceService;
use App\Services\ProductDeliveryService;
use App\Services\StudentAccessService;
use App\Services\StudentLearningResourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class ProtectedLearningMediaController extends Controller
{
    public function view(
        Request $request,
        Media $media,
        ProductDeliveryService $viewer,
        StudentLearningResourceService $studentResources,
        ParentLearningResourceService $parentResources,
    ): Response {
        [$resource, $childId, $backUrl] = $this->authorizeContext(
            $request,
            $media,
            $studentResources,
            $parentResources,
        );

        return $viewer->viewLearningMedia(
            $request->user(),
            $media,
            $request,
            $resource,
            $childId,
            $backUrl,
        );
    }

    public function page(
        Request $request,
        Media $media,
        int $page,
        ProductDeliveryService $viewer,
        StudentLearningResourceService $studentResources,
        ParentLearningResourceService $parentResources,
    ): Response {
        [$resource, $childId] = $this->authorizeContext(
            $request,
            $media,
            $studentResources,
            $parentResources,
        );

        return $viewer->viewLearningMediaPage(
            $request->user(),
            $media,
            $page,
            $request,
            $resource,
            $childId,
        );
    }

    /**
     * The signed-in viewer is never treated as an entitlement. Re-check the
     * learning resource or media policy for the initial page and every raster
     * page request.
     *
     * @return array{0: ?LearningResource, 1: ?int, 2: string}
     */
    private function authorizeContext(
        Request $request,
        Media $media,
        StudentLearningResourceService $studentResources,
        ParentLearningResourceService $parentResources,
    ): array {
        $user = $request->user();
        abort_unless($media->status === 'active', 404);
        abort_unless($user, 404);

        $data = $request->validate([
            'resource' => ['nullable', 'integer', 'min:1'],
            'child' => ['nullable', 'integer', 'min:1'],
        ]);

        $resource = null;
        $childId = isset($data['child']) ? (int) $data['child'] : null;

        if (isset($data['resource'])) {
            $resource = LearningResource::query()->findOrFail((int) $data['resource']);
            abort_unless((int) $resource->media_id === (int) $media->id, 404);

            if ($user->hasRole('student')) {
                abort_unless($studentResources->canAccess($user, $resource), 404);
                $childId = null;
            } elseif ($user->hasRole('parent')) {
                abort_unless($childId, 404);
                abort_unless($parentResources->canAccess($user, $childId, $resource), 404);
            } else {
                Gate::forUser($user)->authorize('view', $media);
                $resource = null;
                $childId = null;
            }
        } elseif ($user->hasRole('student')) {
            $resource = LearningResource::query()
                ->where('media_id', $media->id)
                ->orderBy('id')
                ->get()
                ->first(fn (LearningResource $item): bool => $studentResources->canAccess($user, $item));

            if ($resource) {
                $childId = null;
            } else {
                Gate::forUser($user)->authorize('view', $media);
            }
        } elseif ($user->hasRole('parent')) {
            $resources = LearningResource::query()
                ->where('media_id', $media->id)
                ->orderBy('id')
                ->get();

            $selected = null;
            if ($childId) {
                foreach ($resources as $item) {
                    if ($parentResources->canAccess($user, $childId, $item)) {
                        $selected = $item;
                        break;
                    }
                }
            } else {
                foreach ($parentResources->children($user) as $child) {
                    foreach ($resources as $item) {
                        if ($parentResources->canAccess($user, (int) $child->id, $item)) {
                            $selected = $item;
                            $childId = (int) $child->id;
                            break 2;
                        }
                    }
                }
            }

            if ($selected) {
                $resource = $selected;
            } else {
                Gate::forUser($user)->authorize('view', $media);
            }
        } else {
            Gate::forUser($user)->authorize('view', $media);
        }

        $backUrl = $user->hasRole('parent')
            ? route('parent.resources.index', $childId ? ['child' => $childId] : [])
            : ($user->hasRole('student') ? route('student.resources.index') : route('dashboard'));

        if ($resource) {
            return [$resource, $childId, $backUrl];
        }

        $lessonAttachment = $media->attachments()
            ->where('mediable_type', Lesson::class)
            ->first();

        if ($lessonAttachment && $user->hasRole('student')) {
            $lesson = Lesson::query()->with('section.course')->find($lessonAttachment->mediable_id);
            if ($lesson && app(StudentAccessService::class)->lesson($user, $lesson)) {
                $backUrl = route('student.lessons.show', $lesson);
            }
        }

        return [null, $childId, $backUrl];
    }
}
