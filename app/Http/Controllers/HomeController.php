<?php

namespace App\Http\Controllers;

use App\Services\HomeService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(HomeService $homeService): View
    {
        return view('pages.home', $homeService->getData());
    }
}
