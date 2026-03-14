<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Đăng ký Middleware Aliases
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'pharmacy' => \App\Http\Middleware\EnsurePharmacySelected::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // ── Custom 403 page ────────────────────────────────────────────────
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
            if (!$request->expectsJson()) {
                return response()->view('errors.403', [
                    'message' => $e->getMessage() ?: 'Bạn không có quyền truy cập trang này.'
                ], 403);
            }
        });
    })->create();
