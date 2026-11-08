<?php

namespace App\Services;

use App\Models\HeroChallenge;
use App\Models\HeroChallengeOption;
use App\Support\PersianUi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

final class HeroChallengeService
{
    private const HOME_CHALLENGE_COUNT = 3;
    private const CACHE_KEY = 'public.hero-challenges.active.v1';

    /** @return array<string, mixed>|null */
    public function activeForHome(?int $requestedId = null): ?array
    {
        $challenges = $this->activeChallenges();

        if ($challenges->isEmpty()) {
            return null;
        }

        $index = $requestedId
            ? $challenges->search(fn (HeroChallenge $item): bool => (int) $item->id === $requestedId)
            : false;

        return $this->toPublicArray($challenges, $index === false ? 0 : (int) $index);
    }

    /** @return array<string, mixed>|null */
    public function forPublic(int $challengeId): ?array
    {
        $challenges = $this->activeChallenges();
        $index = $challenges->search(
            fn (HeroChallenge $item): bool => (int) $item->id === $challengeId,
        );

        return $index === false ? null : $this->toPublicArray($challenges, (int) $index);
    }

    /** @return array{id:int,option_id:int,correct:bool,explanation:string,next_id:int|null} */
    public function answer(int $challengeId, int $optionId): array
    {
        // The cached rotation includes all four options, so answer validation
        // never repeats the active-challenge query after the cache is warm.
        $challenges = $this->activeChallenges();
        $challenge = $challenges->first(
            fn (HeroChallenge $item): bool => (int) $item->id === $challengeId,
        );

        abort_if(! $challenge, 404);

        if ($challenge->options->count() !== 4) {
            throw ValidationException::withMessages([
                'hero_challenge' => 'این معما هنوز برای انتشار آماده نیست.',
            ]);
        }

        $option = $challenge->options->firstWhere('id', $optionId);

        if (! $option) {
            throw ValidationException::withMessages([
                'option_id' => 'گزینه انتخاب‌شده متعلق به این معما نیست.',
            ]);
        }

        $index = $challenges->search(
            fn (HeroChallenge $item): bool => (int) $item->id === $challengeId,
        );

        return [
            'id' => (int) $challenge->id,
            'option_id' => (int) $option->id,
            'correct' => (bool) $option->is_correct,
            'explanation' => (string) $challenge->explanation,
            'next_id' => $challenges->get($index + 1)?->id
                ? (int) $challenges->get($index + 1)->id
                : null,
        ];
    }

    public function forgetActiveChallenges(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @param Collection<int, HeroChallenge> $challenges
     *  @return array<string, mixed>
     */
    private function toPublicArray(Collection $challenges, int $index): array
    {
        /** @var HeroChallenge $challenge */
        $challenge = $challenges->get($index);

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

    /** @return Collection<int, HeroChallenge> */
    private function activeChallenges(): Collection
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(15), fn (): Collection => HeroChallenge::query()
            ->where('is_active', true)
            ->has('options', '=', 4)
            ->with(['options:id,hero_challenge_id,label,content,is_correct,sort_order'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(self::HOME_CHALLENGE_COUNT)
            ->get(['id', 'title', 'prompt', 'sequence', 'explanation', 'sort_order'])
            ->values());
    }
}
