<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningResource;
use App\Services\MediaService;
use App\Services\SpotPlayerMediaDeliveryService;
use App\Services\StudentLearningResourceService;
use Illuminate\View\View;

class ResourceController extends Controller
{
    public function index(StudentLearningResourceService $resources): View
    {
        return view('student.resources.index', [
            'resources' => $resources->paginate(request()->user()),
        ]);
    }

    public function view(
        LearningResource $resource,
        StudentLearningResourceService $resources,
        MediaService $media,
        SpotPlayerMediaDeliveryService $spotPlayerDelivery,
    ) {
        abort_unless($resources->canAccess(request()->user(), $resource), 404);
        abort_unless($resource->media && $resource->media->status === 'active', 404);

        if ($spotPlayerDelivery->requiresProtectedPlayer($resource->media)) {
            $course = $spotPlayerDelivery->courseForResource($resource);

            if ($course && filled($course->spotplayer_course_id)) {
                return redirect()
                    ->to(route('student.courses.show', $course) . '#spotplayer-access')
                    ->with('success', 'ویدئوی آموزشی از پلیر محافظت‌شدهٔ دوره باز می‌شود؛ فایل خام مستقیماً ارائه نمی‌شود.');
            }

            abort(409, 'برای حفظ حقوق محتوای آموزشی، فایل ویدئویی خام ارائه نمی‌شود. مدیر آموزشگاه باید دوره را به شناسهٔ رسمی SpotPlayer متصل کند.');
        }

        return $media->inline($resource->media);
    }

    public function download(
        LearningResource $resource,
        StudentLearningResourceService $resources,
    ) {
        abort_unless($resources->canAccess(request()->user(), $resource), 404);

        return response()->view('errors.resource-download', compact('resource'), 403);
    }
}
