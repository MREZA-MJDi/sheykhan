<?php

namespace App\Http\Controllers;

use App\Services\DashboardRedirector;
use Illuminate\Http\RedirectResponse;

class DashboardRedirectController extends Controller
{
    public function __invoke(DashboardRedirector $dashboard): RedirectResponse
    {
        return $dashboard->redirect(request()->user());
    }
}
