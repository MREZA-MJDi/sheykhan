<?php

namespace AppHttpControllers;

use AppServicesDashboardRedirector;
use IlluminateHttpRedirectResponse;

class DashboardRedirectController extends Controller
{
    public function __invoke(DashboardRedirector $dashboard): RedirectResponse
    {
        return $dashboard->redirect(request()->user());
    }
}
