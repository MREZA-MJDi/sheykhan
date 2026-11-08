<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Services\HeroChallengeService;
use Illuminate\Http\JsonResponse;

final class HeroChallengeController extends Controller
{
    public function show(int $heroChallenge, HeroChallengeService $challenges): JsonResponse
    {
        $challenge = $challenges->forPublic($heroChallenge);

        abort_if($challenge === null, 404);

        return response()->json($challenge);
    }
}
