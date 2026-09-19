<?php

namespace AppHttpControllers;

use AppHttpRequestsAuthLoginRequest;
use AppHttpRequestsAuthRegisterRequest;
use AppServicesAuthService;
use AppServicesDashboardRedirector;
use IlluminateHttpRedirectResponse;
use IlluminateSupportFacadesAuth;
use IlluminateViewView;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly DashboardRedirector $dashboard,
    ) {
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $user = $this->auth->login(
            $request->validatedOnly(['email', 'password']),
            $request->boolean('remember'),
        );

        return $this->dashboard->redirect($user);
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = $this->auth->register($request->validated());

        return $this->dashboard->redirect($user)
            ->with('success', 'حساب شما با موفقیت ساخته شد.');
    }

    public function logout(): RedirectResponse
    {
        $this->auth->logout();

        return redirect()->route('home')
            ->with('success', 'با موفقیت از حساب کاربری خارج شدید.');
    }
}
