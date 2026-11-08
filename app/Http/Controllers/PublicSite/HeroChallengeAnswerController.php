<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicSite\AnswerHeroChallengeRequest;
use App\Services\HeroChallengeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

final class HeroChallengeAnswerController extends Controller
{
    public function store(
        AnswerHeroChallengeRequest $request,
        int $heroChallenge,
        HeroChallengeService $challenges,
    ): JsonResponse|RedirectResponse {
        $answer = $challenges->answer(
            $heroChallenge,
            (int) $request->validated('option_id'),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'correct' => (bool) $answer['correct'],
                'explanation' => $answer['correct'] ? $answer['explanation'] : null,
                'next' => $answer['correct'] ? $answer['next_id'] : null,
            ]);
        }

        $result = [
            'id' => $answer['id'],
            'option_id' => $answer['option_id'],
            'correct' => $answer['correct'],
        ];

        if ($answer['correct']) {
            $result['explanation'] = $answer['explanation'];
            $result['next_id'] = $answer['next_id'];
        }

        return redirect()
            ->to(route('home', ['challenge_id' => $heroChallenge]) . '#hero-challenge')
            ->with('hero_challenge_result', $result);
    }
}
