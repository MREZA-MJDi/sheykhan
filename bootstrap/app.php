<?php

use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureActiveUser;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureActiveTeacherMembership;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;\nuse Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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

        $exceptions->render(function (Throwable $e, Request $request) {
            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : null;

            if ($status !== 403) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'شما اجازه دسترسی به این بخش را ندارید.',
                ], 403);
            }

            return response()->view('errors.403', [
                'exception' => $e,
            ], 403);
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e): bool => $request->expectsJson()
        );
    })->create();
