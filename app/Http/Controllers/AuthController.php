<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;


class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            return $this->errorResponse(
                401,
                'Unauthorized',
                'Incorrect email or password',
            );
        }

        $user = Auth::user();
        $roles = $user->roles ?? [];
        $role = $roles[0] ?? 'USER';

        $customClaims = [
            'role' => $role,
            'is_active' => $user->is_active,
        ];

        $accessToken = JWTAuth::claims($customClaims)->fromUser($user);

        $refreshTokenPayload = [
            'sub' => $user->id,
            'jti' => Str::uuid()->toString(),
            'type' => 'refresh',
        ];

        $refreshToken = JWTAuth::getJWTProvider()->encode(
            array_merge(
                $refreshTokenPayload,
                [
                    'exp' => now()->addDays(14)->timestamp // 14 ngày
                ]
            )
        );

        // Lưu jti của access token vào database
        $accessPayload = JWTAuth::setToken($accessToken)->getPayload();
        $accessJti = $accessPayload->get('jti');
        $user->current_jti = $accessJti;
        $user->save();

        return $this->successResponse(
            200,
            'Login successful',
            $this->formatAuthData($accessToken, $user, $refreshToken),
        )->cookie(
            'refresh_token',
            $refreshToken,
            60 * 24 * 14,
            null,
            null,
            true,
            true
        );
    }

    public function register(RegisterRequest $request)
    {
        // Tạo User
        $user = User::create([
            'name'      => $request->username,
            'password'  => bcrypt($request->password),
            'email'     => $request->email,
            'avatar'    => null,
            'phone'     => null,
        ]);

        // Gán role cho user
        $user->assignRole('ADMIN');

        return $this->successResponse(
            201,
            'Register successful',
            [
                'username'    => $user->name,
                'email'       => $user->email,
                'is_active'    => $user->is_active,
                'created_at'   => $user->created_at,
            ],
        );
    }

    public function me()
    {
        $user = Auth::user();

        return $this->successResponse(
            200,
            'Get user info successful',
            [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'phone'     => $user->phone,
                'avatar'    => $user->avatar,
                'is_active' => $user->is_active,
                'roles'      => $user->roles,
                'reputation_score' => $user->reputation_score ?? 0,
                'alerts' => $user->alerts ?? [],
            ]
        );
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return $this->errorResponse(401, 'Unauthorized', 'User not authenticated');
            }

            $validated = $request->validate([
                'name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
            ]);

            $user->name = $validated['name'] ?? null;
            $user->phone = $validated['phone'] ?? null;
            $user->save();

            return $this->successResponse(
                200,
                'Profile updated successfully',
                [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'is_active' => $user->is_active,
                    'roles' => $user->roles,
                    'reputation_score' => $user->reputation_score ?? 0,
                    'alerts' => $user->alerts ?? [],
                ]
            );
        } catch (Exception $e) {
            return $this->errorResponse(500, 'Internal server error', 'Could not update profile: ' . $e->getMessage());
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->errorResponse(
                    401,
                    'Unauthorized',
                    'User not authenticated'
                );
            }

            $validated = $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
                'new_password_confirmation' => 'required|string',
            ]);

            if (!password_verify($validated['current_password'], $user->password)) {
                return $this->errorResponse(
                    400,
                    'Bad Request',
                    'Current password is incorrect'
                );
            }

            if (password_verify($validated['new_password'], $user->password)) {
                return $this->errorResponse(
                    400,
                    'Bad Request',
                    'New password must be different from current password'
                );
            }

            $user->update([
                'password' => bcrypt($validated['new_password'])
            ]);

            // Vô hiệu hóa tất cả token hiện tại để buộc người dùng đăng nhập lại
            //$user->current_jti = null;
            $user->save();

            return $this->successResponse(
                200,
                'Password changed successfully',
                null
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                500,
                'Internal server error',
                'Could not change password: ' . $e->getMessage()
            );
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if ($token) {
                JWTAuth::invalidate($token);
            }

            $user = Auth::user();
            if ($user) {
                $user->current_jti = null;
                $user->save();
            }

            Auth::logout();

            return $this->successResponse(
                200,
                'Successfully logged out',
                null
            )->cookie(
                'refresh_token',
                '',
                -1,
                null,
                null,
                true,
                true
            );
        } catch (Exception $e) {
            return $this->errorResponse(500, 'Internal server error', 'Could not logout: ' . $e->getMessage());
        }
    }

    public function refreshToken(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return $this->errorResponse(400, 'Unauthorized', 'Refresh token not found');
        }

        try {
            $payload = JWTAuth::getJWTProvider()->decode($refreshToken);

            if (!isset($payload['type']) || $payload['type'] !== 'refresh') {
                return $this->errorResponse(400, 'Unauthorized', 'Invalid refresh token type');
            }

            $user = User::find($payload['sub']);
            if (!$user) {
                return $this->errorResponse(400, 'Unauthorized', 'User not found');
            }

            $roles = $user->roles ?? [];
            $role = $roles[0] ?? 'USER';

            $customClaims = [
                'role' => $role,
                'is_active' => $user->is_active,
            ];

            $accessToken = JWTAuth::claims($customClaims)->fromUser($user);

            $accessPayload = JWTAuth::setToken($accessToken)->getPayload();
            $accessJti = $accessPayload->get('jti');
            $user->current_jti = $accessJti;
            $user->save();

            $newRefreshTokenPayload = [
                'sub' => $user->id,
                'jti' => Str::uuid()->toString(),
                'type' => 'refresh',
            ];
            $newRefreshToken = JWTAuth::getJWTProvider()->encode(
                array_merge(
                    $newRefreshTokenPayload,
                    [
                        'exp' => now()->addDays(14)->timestamp
                    ]
                )
            );

            return $this->successResponse(
                200,
                'Token refreshed',
                $this->formatAuthData($accessToken, $user)
            )->cookie(
                'refresh_token',
                $refreshToken,
                60 * 24 * 14,
                null,
                null,
                true,
                true
            );
        } catch (Exception $e) {
            return $this->errorResponse(400, 'Unauthorized', 'Invalid or expired refresh token: ' . $e->getMessage());
        }
    }

    protected function formatAuthData($token, $user)
    {
        $data = [
            'account' => [
                'email'     => $user->email,
                'name'      => $user->name,
                'avatar'    => $user->avatar,
                'phone'     => $user->customer->phone ?? null,
                'roles'     => $user->roles ?? 'USER',
                'is_active' => $user->is_active,
                'reputation_score' => $user->reputation_score ?? 0,
                'alerts' => $user->alerts ?? [],
            ],
            'access_token' => $token,
        ];

        return $data;
    }
}
