<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicSite\AnswerHeroChallengeRequest;
use App\Models\HeroChallenge;
use App\Services\HeroChallengeService;
use Illuminate\Http\RedirectResponse;

final class HeroChallengeAnswerController extends Controller
{
    public function store(
        AnswerHeroChallengeRequest $request,
        HeroChallenge $heroChallenge,
        HeroChallengeService $challenges,
    ): RedirectResponse {
        $answer = $challenges->answer(
            (int) $heroChallenge->id,
            (int) $request->validated('option_id'),
        );

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
            ->to(route('home', ['challenge_id' => $heroChallenge->id]) . '#hero-challenge')
            ->with('hero_challenge_result', $result);
    }
}
