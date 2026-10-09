<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningResource;
use App\Services\MediaService;
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
    ) {
        abort_unless($resources->canAccess(request()->user(), $resource), 404);
        abort_unless($resource->media && $resource->media->status === 'active', 404);

        return $media->inline($resource->media);
    }

    public function download(
        LearningResource $resource,
        StudentLearningResourceService $resources,
    ) {
        abort_unless($resources->canAccess(request()->user(), $resource), 404);

        abort(403, 'فایل‌های آموزشی محافظت‌شده برای دانش‌آموز فقط قابل مشاهده هستند و دانلود مستقیم ندارند.');
    }
}
