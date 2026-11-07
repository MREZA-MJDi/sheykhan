<?php

namespace App\Services;

use App\Models\HeroChallenge;
use App\Models\HeroChallengeOption;
use App\Support\PersianUi;
use Illuminate\Validation\ValidationException;

final class HeroChallengeService
{
    private const HOME_CHALLENGE_COUNT = 3;

    /**
     * @return array<string, mixed>|null
     */
    public function activeForHome(?int $requestedId = null): ?array
    {
        $challenges = HeroChallenge::query()
            ->where('is_active', true)
            ->with(['options:id,hero_challenge_id,label,content,sort_order'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(self::HOME_CHALLENGE_COUNT)
            ->get(['id', 'title', 'prompt', 'sequence', 'sort_order']);

        if ($challenges->isEmpty()) {
            return null;
        }

        $index = $requestedId
            ? $challenges->search(fn (HeroChallenge $item): bool => (int) $item->id === $requestedId)
            : false;

        $index = $index === false ? 0 : $index;
        $challenge = $challenges->get($index);

        if (! $challenge || $challenge->options->count() !== 4) {
            return null;
        }

        return [
            'id' => (int) $challenge->id,
            'title' => $challenge->title,
            'prompt' => $challenge->prompt,
            'sequence' => collect($challenge->sequence ?? [])
                ->map(fn ($value) => PersianUi::digits((string) $value))
                ->values()
                ->all(),
            'options' => $challenge->options
                ->map(fn (HeroChallengeOption $option): array => [
                    'id' => (int) $option->id,
                    'label' => $option->label,
                    'content' => $option->content,
                ])
                ->values()
                ->all(),
            'position' => $index + 1,
            'total' => $challenges->count(),
            'next_id' => $challenges->get($index + 1)?->id
                ? (int) $challenges->get($index + 1)->id
                : null,
        ];
    }

    /**
     * @return array{id:int,option_id:int,correct:bool,explanation:string,next_id:int|null}
     */
    public function answer(int $challengeId, int $optionId): array
    {
        $challenge = HeroChallenge::query()
            ->whereKey($challengeId)
            ->where('is_active', true)
            ->with('options:id,hero_challenge_id,is_correct')
            ->firstOrFail();

        $option = $challenge->options->firstWhere('id', $optionId);

        if (! $option) {
            throw ValidationException::withMessages([
                'option_id' => 'گزینه انتخاب‌شده متعلق به این معما نیست.',
            ]);
        }

        $rotation = HeroChallenge::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(self::HOME_CHALLENGE_COUNT)
            ->pluck('id');

        $index = $rotation->search((int) $challenge->id);
        $nextId = $index !== false && $rotation->get($index + 1)
            ? (int) $rotation->get($index + 1)
            : null;

        return [
            'id' => (int) $challenge->id,
            'option_id' => (int) $option->id,
            'correct' => (bool) $option->is_correct,
            'explanation' => $challenge->explanation,
            'next_id' => $nextId,
        ];
    }
}
