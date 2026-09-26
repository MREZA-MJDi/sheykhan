<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\OwnerReportService;
use App\Services\OwnerWorkspaceService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(
        OwnerReportService $reports,
        OwnerWorkspaceService $workspace
    ): View {
        $data = $reports->build(request()->user());
        $data['academies'] = $workspace->academies(request()->user());

        return view('owner.reports.index', $data);
    }
}
