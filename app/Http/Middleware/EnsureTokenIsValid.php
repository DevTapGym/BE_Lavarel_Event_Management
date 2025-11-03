<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Traits\ApiResponse;
use App\Models\User;

class EnsureTokenIsValid
{
    use ApiResponse;

    public function handle(Request $request, Closure $next)
    {
        try {
            // Lấy token từ header Authorization
            $token = JWTAuth::parseToken();
            $payload = $token->getPayload();

            // Lấy user id & jti từ payload
            $userId = $payload->get('sub');
            $jti = $payload->get('jti');

            // Tìm user trong DB
            $user = User::find($userId);

            if (!$user || !$user->current_jti) {
                return $this->errorResponse(
                    401,
                    'Unauthorized',
                    'Token revoked or user not found'
                );
            }

            if ($user->current_jti !== $jti) {
                return $this->errorResponse(
                    401,
                    'Unauthorized',
                    'Token is invalid or has been revoked'
                );
            }
        } catch (JWTException $e) {
            return $this->errorResponse(
                401,
                'Unauthorized',
                'Token is invalid or not transmitted'
            );
        }

        return $next($request);
    }
}
