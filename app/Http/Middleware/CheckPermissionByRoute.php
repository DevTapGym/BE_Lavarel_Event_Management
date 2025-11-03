<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\Role;

class CheckPermissionByRoute
{
    use ApiResponse;

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse(
                401,
                'Unauthorized',
                'User not authenticated'
            );
        }

        $permissionName = str_replace('.', ' ', $request->route()->getName());
        $roles = $user->roles ?? [];

        if (in_array('ADMIN', $roles)) {
            return $next($request);
        }

        $hasPermission = false;
        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role && in_array($permissionName, $role->permissions ?? [])) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            return $this->errorResponse(
                403,
                'Forbidden',
                "You don't have permission to access this endpoint."
            );
        }

        return $next($request);
    }
}
