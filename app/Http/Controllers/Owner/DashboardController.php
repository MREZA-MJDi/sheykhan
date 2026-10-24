<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\OwnerCacheService;
use App\Services\OwnerDashboardService;
use App\Services\OwnerWorkspaceService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        OwnerDashboardService $dashboard,
        OwnerWorkspaceService $workspace,
        OwnerCacheService $cache,
    ): View {
        $owner = request()->user();

        $data = $cache->rememberDashboard(
            $owner,
            fn () => $dashboard->build($owner)
        );

        $data['academies'] = $workspace->academies($owner);

        // Do not cache recent login events with the KPI aggregate; owners need this to refresh promptly.
        $academyIds = $data['academies']->pluck('id')->map(fn ($id) => (int) $id)->all();
        $data['recentStudentLogins'] = collect();

        if ($academyIds !== [] && $owner->hasPermission('academy.view') && Schema::hasTable('audit_logs')) {
            $activeStudentIds = DB::table('academy_user')
                ->whereIn('academy_id', $academyIds)
                ->where('role', 'student')
                ->where('status', 'active')
                ->select('user_id')
                ->distinct();

            $data['recentStudentLogins'] = AuditLog::query()
                ->where('action', 'user_login')
                ->where('subject_type', User::class)
                ->whereIn('subject_id', $activeStudentIds)
                ->with('subject:id,name,email')
                ->orderByDesc('created_at')
                ->limit(8)
                ->get();
        }

        return view('owner.dashboard', $data);
    }
}
