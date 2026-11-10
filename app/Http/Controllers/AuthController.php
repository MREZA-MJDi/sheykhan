<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\DashboardRedirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly DashboardRedirector $dashboard,
    ) {
    }

    public function showLogin(): View
    {
        $testPassword = config('role_access.test_password');
        $showTestAccounts = (bool) config('role_access.show_test_accounts') && filled($testPassword);
        $configuredAccounts = collect(config('role_access.test_accounts', []));
        $testAccounts = collect();

        if ($showTestAccounts && $configuredAccounts->isNotEmpty()) {
            $usersByEmail = User::query()
                ->whereIn('email', $configuredAccounts->pluck('email')->filter()->all())
                ->where('status', 'active')
                ->whereNotNull('mobile')
                ->with('roles:id,slug')
                ->get(['id', 'name', 'email', 'mobile', 'password'])
                ->keyBy('email');

            $testAccounts = $configuredAccounts
                ->filter(function (array $account) use ($usersByEmail, $testPassword): bool {
                    $user = $usersByEmail->get($account['email'] ?? '');

                    if (
                        $user === null
                        || blank($user->mobile)
                        || ! $user->roles->contains('slug', $account['role_key'])
                    ) {
                        return false;
                    }

                    // Never advertise credentials that do not authenticate this persisted account.
                    try {
                        return Hash::check((string) $testPassword, (string) $user->getAuthPassword());
                    } catch (\Throwable) {
                        return false;
                    }
                })
                ->map(function (array $account) use ($usersByEmail): array {
                    $user = $usersByEmail->get($account['email']);
                    $account['username'] = (string) $user->mobile;
                    $account['name'] = $user->name;

                    return $account;
                })
                ->values();
        }

        return view('auth.login', [
            'testAccounts' => $testAccounts,
            'testPassword' => $testPassword,
            'dashboardRoutes' => config('role_access.dashboard_routes', []),
            'showTestAccounts' => $showTestAccounts,
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $user = $this->auth->login(
            $request->safe()->only(['mobile', 'password']),
            $request->boolean('remember'),
        );

        if ($user->must_change_password) {
            return redirect()->route('password.change.show');
        }

        return $this->dashboard->redirect($user);
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = $this->auth->register($request->validated());

        return redirect()->route('password.change.show')
            ->with('success', 'حساب ساخته شد؛ رمز اولیه را در جای امن نگه دار و رمز شخصی خودت را تعیین کن.');
    }

    public function logout(): RedirectResponse
    {
        $this->auth->logout();

        return redirect()->route('home')
            ->with('success', 'با موفقیت از حساب کاربری خارج شدید.');
    }
}
