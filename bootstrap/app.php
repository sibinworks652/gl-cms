<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\TrackActivityLog;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Spatie\Permission\Exceptions\UnauthorizedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->preventRequestsDuringMaintenance([
            'admin',
            'admin/*',
            'logout',
            'password/*',
            'login',
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'lock' => \App\Http\Middleware\CheckIfLocked::class,
            'vendor.approved' => \App\Http\Middleware\VendorApproved::class,
            'vendor.module' => \App\Http\Middleware\EcommerceVendorModuleEnabled::class,
        ]);

        $middleware->web(append: [
            TrackActivityLog::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UnauthorizedException|AccessDeniedHttpException $exception, $request) {
            $message = $exception->getMessage() ?: 'You do not have permission to access this resource.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 403);
            }

            $previousUrl = url()->previous();
            $currentUrl = $request->fullUrl();
            $fallbackUrl = url('/');

            return redirect()
                ->to($previousUrl && $previousUrl !== $currentUrl ? $previousUrl : $fallbackUrl)
                ->with('error', $message);
        });
    })->create();
