<?php

use AppHttpMiddlewareEnsurePermission;
use AppHttpMiddlewareEnsureActiveUser;
use AppHttpMiddlewareEnsureRole;
use AppHttpMiddlewareEnsureActiveTeacherMembership;
use IlluminateDatabaseEloquentModelNotFoundException;
use IlluminateFoundationApplication;
use IlluminateFoundationConfigurationExceptions;
use IlluminateFoundationConfigurationMiddleware;
use IlluminateHttpRequest;
use SymfonyComponentHttpKernelExceptionNotFoundHttpException;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'permission' => EnsurePermission::class,
            'active' => EnsureActiveUser::class,
            'active-teacher' => EnsureActiveTeacherMembership::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // A missing route or implicitly bound model is a normal 404, not a server error.
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'رکورد موردنظر پیدا نشد.',
                ], 404);
            }

            return response()->view('errors.404', [
                'exception' => $e,
            ], 404);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'صفحه یا رکورد موردنظر پیدا نشد.',
                ], 404);
            }

            return response()->view('errors.404', [
                'exception' => $e,
            ], 404);
        });

        // Keep JSON endpoints machine-readable while web pages use the branded error UI.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e): bool => $request->expectsJson()
        );
    })->create();
