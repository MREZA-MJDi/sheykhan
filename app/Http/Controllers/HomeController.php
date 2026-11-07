<?php

namespace App\Http\Controllers;

use App\Services\HomeService;
use App\Services\HeroChallengeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(
        Request $request,
        HomeService $homeService,
        HeroChallengeService $heroChallenges,
    ): View {
        $data = $homeService->getData();
        $data['heroChallenge'] = $heroChallenges->activeForHome(
            $request->integer('challenge_id') ?: null,
        );

        return view('pages.home', $data);
    }
}
