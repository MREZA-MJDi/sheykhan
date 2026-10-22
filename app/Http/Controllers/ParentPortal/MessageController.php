<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentMessage;
use App\Models\User;
use App\Services\ParentPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

final class MessageController extends Controller
{
    public function index(Request $request, ParentPortalService $portal): View
    {
        $messages = ParentMessage::query()
            ->where('parent_id', $request->user()->id)
            ->with(['student:id,name', 'academy:id,name', 'responder:id,name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('parent.messages.index', [
            'messages' => $messages,
            'contactableChildren' => $portal->contactableChildren($request->user()),
        ]);
    }

    public function store(Request $request, ParentPortalService $portal): RedirectResponse
    {
        $data = $request->validate([
            'target' => ['required', 'string', 'regex:/^\d+:\d+$/'],
            'category' => ['required', 'string', 'in:question,suggestion,complaint,other'],
            'subject' => ['required', 'string', 'min:3', 'max:180'],
            'body' => ['required', 'string', 'min:5', 'max:10000'],
        ], [
            'target.required' => 'فرزند و آموزشگاه مرتبط را انتخاب کنید.',
            'target.regex' => 'انتخاب فرزند یا آموزشگاه معتبر نیست.',
            'subject.required' => 'موضوع پیام را وارد کنید.',
            'body.required' => 'متن پیام را وارد کنید.',
        ]);

        [$studentId, $academyId] = array_map('intval', explode(':', $data['target'], 2));
        [$student, $academy] = $portal->resolveTarget($request->user(), $studentId, $academyId);

        ParentMessage::query()->create([
            'academy_id' => $academy->id,
            'parent_id' => $request->user()->id,
            'student_id' => $student->id,
            'category' => $data['category'],
            'subject' => trim($data['subject']),
            'body' => trim($data['body']),
            'status' => 'open',
        ]);

        return redirect()->route('parent.messages.index')
            ->with('success', 'پیام شما ثبت شد و برای پیگیری به آموزشگاه ارسال شد.');
    }
}
