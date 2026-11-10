<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\QuestionBankItem;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

final class QuestionBankController extends Controller
{
    public function index(Academy $academy, Request $request, OwnerWorkspaceService $workspace): View
    {
        $workspace->authorizeAcademy($request->user(), $academy, 'exams.view');
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in(['text', 'single', 'multiple', 'checkbox'])],
            'topic' => ['nullable', 'string', 'max:120'],
            'difficulty' => ['nullable', Rule::in(['easy', 'medium', 'hard'])],
            'sort' => ['nullable', Rule::in(['recent', 'oldest', 'question'])],
        ]);

        $query = $academy->questionBankItems()
            ->where('status', 'active')
            ->when(filled($filters['q'] ?? null), fn ($q) => $q->where('question', 'like', '%' . trim($filters['q']) . '%'))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when(filled($filters['topic'] ?? null), fn ($q) => $q->where('topic', trim($filters['topic'])))
            ->when($filters['difficulty'] ?? null, fn ($q, $difficulty) => $q->where('difficulty', $difficulty));

        match ($filters['sort'] ?? 'recent') {
            'oldest' => $query->orderBy('created_at')->orderBy('id'),
            'question' => $query->orderBy('question')->orderByDesc('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        $questions = $query->paginate(20)->withQueryString();
        $topics = $academy->questionBankItems()->where('status', 'active')
            ->whereNotNull('topic')->distinct()->orderBy('topic')->pluck('topic');

        return view('owner.question-bank.index', compact('academy', 'questions', 'filters', 'topics'));
    }

    public function store(Academy $academy, Request $request, OwnerWorkspaceService $workspace): RedirectResponse
    {
        $workspace->authorizeAcademy($request->user(), $academy, 'exams.manage');
        $data = $this->validated($request);
        $data['academy_id'] = $academy->id;
        $data['created_by'] = $request->user()->id;
        $academy->questionBankItems()->create($data);

        return redirect()->route('owner.question-bank.index', $academy)
            ->with('success', 'سؤال به بانک سؤال آموزشگاه اضافه شد.');
    }

    public function update(Academy $academy, int $question, Request $request, OwnerWorkspaceService $workspace): RedirectResponse
    {
        $workspace->authorizeAcademy($request->user(), $academy, 'exams.manage');
        $record = $academy->questionBankItems()->whereKey($question)->firstOrFail();
        $record->update($this->validated($request));

        return redirect()->route('owner.question-bank.index', $academy)
            ->with('success', 'سؤال بانک به‌روزرسانی شد.');
    }

    public function destroy(Academy $academy, int $question, Request $request, OwnerWorkspaceService $workspace): RedirectResponse
    {
        $workspace->authorizeAcademy($request->user(), $academy, 'exams.manage');
        $record = $academy->questionBankItems()->whereKey($question)->firstOrFail();
        $record->forceFill(['status' => 'archived'])->save();

        return redirect()->route('owner.question-bank.index', $academy)
            ->with('success', 'سؤال از فهرست فعال بانک خارج شد؛ نسخه‌های استفاده‌شده در آزمون‌ها بدون تغییر باقی ماندند.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['text', 'single', 'multiple', 'checkbox'])],
            'question' => ['required', 'string', 'max:10000'],
            'options_text' => ['nullable', 'string', 'max:15000'],
            'correct_answers_text' => ['nullable', 'string', 'max:15000'],
            'expected_answer' => ['nullable', 'string', 'max:15000'],
            'default_score' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'topic' => ['nullable', 'string', 'max:120'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
        ]);

        $options = array_values(array_filter(
            array_map('trim', preg_split('/\\R/u', (string) ($data['options_text'] ?? '')) ?: []),
            static fn ($value) => $value !== ''
        ));
        $answers = array_values(array_filter(
            array_map('trim', preg_split('/\\R/u', (string) ($data['correct_answers_text'] ?? '')) ?: []),
            static fn ($value) => $value !== ''
        ));
        if (count($options) !== count(array_unique($options))) {
            throw \Illuminate\Validation\ValidationException::withMessages(['options_text' => 'گزینه‌های تکراری مجاز نیستند.']);
        }
        if (count($options) > 30 || count($answers) > 30
            || collect($options)->contains(fn ($option) => mb_strlen($option) > 500)
            || collect($answers)->contains(fn ($answer) => mb_strlen($answer) > 500)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['options_text' => 'حداکثر ۳۰ گزینه یا پاسخ با طول ۵۰۰ نویسه مجاز است.']);
        }
        unset($data['options_text'], $data['correct_answers_text']);
        $data['question'] = trim($data['question']);
        $data['topic'] = filled($data['topic'] ?? null) ? trim($data['topic']) : null;
        $data['expected_answer'] = filled($data['expected_answer'] ?? null) ? trim($data['expected_answer']) : null;

        if ($data['type'] === 'text') {
            $data['options'] = null;
            $data['correct_answers'] = null;
        } else {
            validator([], [])->after(function ($validator) use ($data, $options, $answers): void {
                if (count($options) < 2) {
                    $validator->errors()->add('options_text', 'برای سؤال گزینه‌ای حداقل دو گزینه لازم است.');
                }
                if ($data['type'] === 'single' && count($options) !== 4) {
                    $validator->errors()->add('options_text', 'برای سؤال تک‌گزینه‌ای دقیقاً چهار گزینه لازم است.');
                }
                if ($answers === [] || array_diff($answers, $options) !== []) {
                    $validator->errors()->add('correct_answers', 'پاسخ صحیح باید از بین گزینه‌های همین سؤال انتخاب شود.');
                }
                if ($data['type'] === 'single' && count($answers) !== 1) {
                    $validator->errors()->add('correct_answers', 'برای سؤال تک‌گزینه‌ای دقیقاً یک پاسخ صحیح لازم است.');
                }
            })->validate();
            $data['options'] = $options;
            $data['correct_answers'] = $answers;
        }

        return $data;
    }
}
