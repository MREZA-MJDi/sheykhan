<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\AssignTeacherCourseRequest;
use App\Http\Requests\Owner\EnrollStudentRequest;
use App\Http\Requests\Owner\LegacyStudentOnboardingRequest;
use App\Http\Requests\Owner\StoreTeacherRequest;
use App\Http\Requests\Owner\StoreParentRequest;
use App\Http\Requests\Owner\StoreStudentAccountRequest;
use App\Http\Requests\Owner\UpdateTeacherVisibilityRequest;
use App\Models\Academy;
use App\Models\User;
use App\Services\OwnerWorkspaceService;
use App\Services\ParentOnboardingService;
use App\Services\StudentOnboardingService;
use App\Services\StudentAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeopleController extends Controller
{
    public function index(Academy $academy, OwnerWorkspaceService $workspace): View
    {
        $people = $workspace->people(request()->user(), $academy);
        $options = $workspace->courseTeacherOptions(request()->user(), $academy);

        return view('owner.people.index', [
            'academy' => $academy,
            ...$people,
            ...$options,
        ]);
    }

    public function studentOptions(Request $request, Academy $academy, OwnerWorkspaceService $workspace): JsonResponse
    {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 403);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $term = trim((string) ($filters['q'] ?? ''));

        if (mb_strlen($term) < 2) {
            return response()->json([
                'data' => [],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'next_page' => null, 'total' => 0],
            ], 200, ['Cache-Control' => 'no-store']);
        }

        $students = $academy->users()
            ->wherePivot('role', 'student')
            ->wherePivot('status', 'active')
            ->where('users.status', 'active')
            ->where(function ($query) use ($term): void {
                $query->where('users.name', 'like', '%' . $term . '%')
                    ->orWhere('users.email', 'like', '%' . $term . '%');
            })
            ->select('users.id', 'users.name')
            ->orderBy('users.name')
            ->orderBy('users.id')
            ->paginate(20, ['users.id', 'users.name'], 'page');

        return response()->json([
            'data' => $students->getCollection()->map(fn (User $student): array => [
                'id' => (int) $student->id,
                'name' => $student->name,
            ])->values(),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'next_page' => $students->hasMorePages() ? $students->currentPage() + 1 : null,
                'total' => $students->total(),
            ],
        ], 200, ['Cache-Control' => 'no-store']);
    }

    public function onboardLegacyStudent(
        LegacyStudentOnboardingRequest $request,
        Academy $academy,
        StudentOnboardingService $onboarding,
        OwnerWorkspaceService $workspace,
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 403);

        $onboarding->onboard(
            $request->user(),
            $academy,
            $request->validated(),
        );

        return back()->with(
            'success',
            'دانش‌آموز قدیمی با موفقیت ثبت/به‌روزرسانی شد و به این آموزشگاه متصل شد.'
        );
    }


    public function storeStudentAccount(
        StoreStudentAccountRequest $request,
        Academy $academy,
        StudentAccountService $students,
        OwnerWorkspaceService $workspace,
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 403);

        $result = $students->create($request->user(), $academy, $request->validated());

        return redirect()
            ->to(route('owner.people.index', $academy) . '?tab=create-student')
            ->with('success', 'حساب دانش‌آموز ساخته شد. نام کاربری و رمز اولیه را فقط همین یک‌بار تحویل بده.')
            ->with('temporary_student_name', $result['student']->name)
            ->with('temporary_student_email', $result['student']->email)
            ->with('temporary_student_number', $result['student']->studentProfile?->student_number)
            ->with('temporary_student_password', $result['temporary_password']);
    }

    public function storeTeacher(
        StoreTeacherRequest $request,
        Academy $academy,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->createTeacher(
            $request->user(),
            $academy,
            $request->validated()
        );

        return back()->with('success', 'حساب مدرس ساخته شد و به آموزشگاه اضافه شد.');
    }

    public function storeParent(
        StoreParentRequest $request,
        Academy $academy,
        ParentOnboardingService $onboarding,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 403);

        $result = $onboarding->createOrLink($academy, $request->validated());

        $response = back()->with(
            'success',
            $result['created']
                ? 'حساب والد ساخته شد و به دانش‌آموز متصل شد. رمز موقت فقط همین یک‌بار نمایش داده می‌شود.'
                : 'حساب والد موجود به دانش‌آموز متصل شد؛ رمز عبور حساب تغییر نکرد.'
        );

        if ($result['temporary_password']) {
            $response
                ->with('temporary_parent_password', $result['temporary_password'])
                ->with('temporary_parent_email', $result['parent']->email);
        }

        return $response;
    }

    public function archiveTeacher(
        Academy $academy,
        User $teacher,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->archiveTeacher(request()->user(), $academy, $teacher);

        return back()->with('success', 'عضویت مدرس آرشیو شد؛ سابقه و پروفایل او حفظ شد.');
    }

    public function restoreTeacher(
        Academy $academy,
        User $teacher,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->restoreTeacher(request()->user(), $academy, $teacher);

        return back()->with('success', 'عضویت مدرس دوباره فعال شد.');
    }

    public function updateTeacherVisibility(
        UpdateTeacherVisibilityRequest $request,
        Academy $academy,
        User $teacher,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->updateTeacherPublicVisibility(
            $request->user(),
            $academy,
            $teacher,
            $request->boolean('is_public')
        );

        return back()->with(
            'success',
            $request->boolean('is_public')
                ? 'مدرس دوباره در لیست عمومی نمایش داده می‌شود.'
                : 'مدرس از لیست عمومی مدرس‌ها مخفی شد.'
        );
    }

    public function assignTeacher(
        AssignTeacherCourseRequest $request,
        Academy $academy,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->assignTeacher(
            $request->user(),
            $academy,
            (int) $request->validated('teacher_id'),
            (int) $request->validated('course_id'),
        );

        return back()->with('success', 'مدرس به دوره اختصاص داده شد.');
    }

    public function assignClassTeacher(
        \App\Http\Requests\Owner\AssignClassTeacherRequest $request,
        Academy $academy,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->assignTeacherToClass(
            $request->user(),
            $academy,
            (int) $request->validated('teacher_id'),
            (int) $request->validated('classroom_id'),
        );

        return back()->with('success', 'مدرس فعال به کلاس اختصاص داده شد؛ اتصال مدرس به دوره نیز تکمیل شد.');
    }
}
