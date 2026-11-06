<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherPayment;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user();

        $query = TeacherPayment::query()
            ->where('teacher_id', $teacher->id)
            ->with(['academy:id,name', 'recorder:id,name']);

        if ($request->filled('status') && in_array($request->status, ['paid', 'pending'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%");
            });
        }

        $payments = $query
            ->orderByDesc('period_end')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'paid' => TeacherPayment::query()->where('teacher_id', $teacher->id)->where('status', 'paid')->sum('amount'),
            'pending' => TeacherPayment::query()->where('teacher_id', $teacher->id)->where('status', 'pending')->sum('amount'),
            'total' => TeacherPayment::query()->where('teacher_id', $teacher->id)->count(),
        ];

        return view('teacher.payments.index', compact('payments', 'summary'));
    }
}
