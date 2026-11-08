<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\HeroChallenge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class HeroChallengeController extends Controller
{
    public function index(): View
    {
        $this->authorizeEditor(request()->user());

        return view('owner.website.hero-challenges.index', [
            'challenges' => HeroChallenge::query()
                ->withCount('options')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate(20),
        ]);
    }

    public function edit(HeroChallenge $challenge): View
    {
        $this->authorizeEditor(request()->user());

        return view('owner.website.hero-challenge', [
            'challenge' => $challenge->load('options'),
        ]);
    }

    public function update(Request $request, HeroChallenge $challenge): RedirectResponse
    {
        $this->authorizeEditor($request->user());

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'prompt' => ['required', 'string', 'max:1000'],
            'sequence_text' => ['nullable', 'string', 'max:240'],
            'explanation' => ['required', 'string', 'max:2000'],
            'options' => ['required', 'array', 'size:4'],
            'options.A' => ['required', 'string', 'max:160'],
            'options.B' => ['required', 'string', 'max:160'],
            'options.C' => ['required', 'string', 'max:160'],
            'options.D' => ['required', 'string', 'max:160'],
            'correct_option' => ['required', 'string', 'in:A,B,C,D'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $sequence = collect(explode(',', (string) ($data['sequence_text'] ?? '')))
            ->map(fn (string $value): string => trim($this->normalizeDigits($value)))
            ->filter()
            ->take(12)
            ->values()
            ->all();

        DB::transaction(function () use ($challenge, $data, $sequence, $request): void {
            $challenge->update([
                'title' => trim($data['title']),
                'prompt' => trim($data['prompt']),
                'sequence' => $sequence,
                'explanation' => trim($data['explanation']),
                'sort_order' => (int) $data['sort_order'],
                'is_active' => $request->boolean('is_active'),
                'updated_by' => $request->user()->id,
            ]);

            $challenge->options()->whereNotIn('label', ['A', 'B', 'C', 'D'])->delete();

            foreach (['A', 'B', 'C', 'D'] as $index => $label) {
                $challenge->options()->updateOrCreate(
                    ['label' => $label],
                    [
                        'content' => trim($data['options'][$label]),
                        'is_correct' => $label === $data['correct_option'],
                        'sort_order' => $index + 1,
                    ],
                );
            }
        });

        return redirect()
            ->route('owner.website.hero-challenges.index')
            ->with('success', 'معما با موفقیت ذخیره شد.');
    }

    private function authorizeEditor($user): void
    {
        abort_unless(
            $user
                && $user->hasRole('seo-specialist')
                && ! $user->hasRole('academy-owner')
                && $user->hasPermission('seo.manage'),
            403,
        );
    }

    private function normalizeDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
    }
}
