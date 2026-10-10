<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\Order;
use App\Models\ProductEntitlement;
use App\Services\StudentAccessService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class LibraryController extends Controller
{
    public function index(Request $request, StudentAccessService $studentAccess): View
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['student', 'parent']), 403);

        $products = ProductEntitlement::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->whereHas('orderItem.order', fn ($order) => $order
                ->where('buyer_id', $user->id)
                ->where('status', 'paid')
                ->whereNotNull('paid_at')
                ->where('legal_consent_completed', true)
                ->whereHas('payments', fn ($payment) => $payment
                    ->where('status', 'successful')
                    ->whereNotNull('paid_at')
                    ->whereColumn('payments.amount', 'orders.total')))
            ->with([
                'product.category:id,name,slug',
                'product.media' => fn ($media) => $media->where('visibility', 'public')->where('status', 'active')->orderByPivot('sort_order'),
                'product.files' => fn ($files) => $files->where('is_preview', false)->whereHas('media', fn ($media) => $media->where('visibility', 'private')->where('status', 'active')),
                'product.files.media',
                'orderItem.order',
            ])
            ->latest('granted_at')
            ->get();

        $pendingOrders = Order::query()
            ->where('buyer_id', $user->id)
            ->where('status', 'pending')
            ->whereHas('payments', fn ($payment) => $payment->whereIn('status', ['pending', 'rejected']))
            ->with([
                'items.course:id,title,slug',
                'items.product:id,title,slug',
                'payments' => fn ($payment) => $payment->latest('id'),
            ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $myCourses = collect();
        if ($user->hasRole('student')) {
            $courseIds = $studentAccess->enrolledCourseIds($user);
            $myCourses = CourseEnrollment::query()
                ->where('student_id', $user->id)
                ->whereIn('course_id', $courseIds)
                ->with([
                    'course.academy:id,name',
                    'course.teachers:id,name',
                    'course.media' => fn ($media) => $media->where('visibility', 'public')->where('status', 'active')->orderByPivot('sort_order'),
                ])
                ->orderByDesc('started_at')
                ->get();
        }

        $childCourses = collect();
        if ($user->hasRole('parent')) {
            $childIds = $user->children()->whereHas('roles', fn ($roles) => $roles->where('slug', 'student'))->pluck('users.id');
            $childCourses = CourseEnrollment::query()
                ->whereIn('student_id', $childIds)
                ->active()
                ->fullyPaid()
                ->whereHas('course', fn ($course) => $course->published()
                    ->whereHas('academy', fn ($academy) => $academy->where('status', 'active'))
                    ->whereExists(function ($membership): void {
                        $membership->selectRaw('1')->from('academy_user')
                            ->whereColumn('academy_user.academy_id', 'courses.academy_id')
                            ->whereColumn('academy_user.user_id', 'course_enrollments.student_id')
                            ->where('academy_user.role', 'student')
                            ->where('academy_user.status', 'active');
                    }))
                ->with([
                    'student:id,name',
                    'course.academy:id,name',
                    'course.teachers:id,name',
                    'course.media' => fn ($media) => $media->where('visibility', 'public')->where('status', 'active')->orderByPivot('sort_order'),
                ])
                ->orderByDesc('started_at')
                ->get();
        }

        return view('commerce.library.index', [
            'products' => $products,
            'pendingOrders' => $pendingOrders,
            'myCourses' => $myCourses,
            'childCourses' => $childCourses,
            'isParent' => $user->hasRole('parent'),
            'isStudent' => $user->hasRole('student'),
        ]);
    }
}
