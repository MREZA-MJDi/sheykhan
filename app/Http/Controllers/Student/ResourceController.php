<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LearningResource;
use App\Services\MediaService;
use App\Services\StudentLearningResourceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResourceController extends Controller
{
    public function index(StudentLearningResourceService $resources): View
    {
        return view('student.resources.index', [
            'resources' => $resources->paginate(request()->user()),
        ]);
    }

    public function download(
        LearningResource $resource,
        StudentLearningResourceService $resources,
        MediaService $media,
    ) {
        abort_unless($resources->canAccess(request()->user(), $resource), 404);
        abort_if(!$resource->downloadable, 403, 'این محتوا فقط برای مشاهده ارائه شده و امکان دانلود آن فعال نیست.');
        abort_unless($resource->media && $resource->media->status === 'active', 404);

        return $media->download($resource->media);
    }
}
