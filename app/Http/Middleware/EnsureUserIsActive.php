<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class EnsureUserIsActive
{
    use ApiResponse;

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->is_active) {
            return $this->errorResponse(
                403,
                'Forbidden',
                'Account not activated. Please verify email.',
            );
        }

        return $next($request);
    }
}
