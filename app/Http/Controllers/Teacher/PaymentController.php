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

        $payments = TeacherPayment::query()
            ->where('teacher_id', $teacher->id)
            ->with(['academy:id,name', 'recorder:id,name'])
            ->orderByDesc('period_end')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'paid' => (int) (clone $payments->getCollection())->where('status', 'paid')->sum('amount'),
            'pending' => TeacherPayment::query()->where('teacher_id', $teacher->id)->where('status', 'pending')->sum('amount'),
        ];

        return view('teacher.payments.index', compact('payments', 'summary'));
    }
}
