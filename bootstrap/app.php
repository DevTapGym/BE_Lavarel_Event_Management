<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponse;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Http\Middleware\CheckPermissionByRoute;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\CorsMiddleware;



return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(ForceJsonResponse::class);
        $middleware->use([CorsMiddleware::class]);
        $middleware->alias([
            'jwt.auth' => EnsureTokenIsValid::class,
            'check.permission' => CheckPermissionByRoute::class,
            'active' => EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, $request) {
            $apiResponse = new class {
                use ApiResponse;
            };

            if ($e instanceof ValidationException) {
                return $apiResponse->errorResponse(
                    422,
                    'Validation failed',
                    $e->errors()
                );
            }

            if ($e instanceof AuthenticationException) {
                return $apiResponse->errorResponse(
                    401,
                    'Unauthenticated',
                    'Authentication is required to access this resource',
                );
            }

            return $apiResponse->errorResponse(
                500,
                'Internal server error',
                $e->getMessage() ?: 'Internal server error',
            );
        });
    })->create();
