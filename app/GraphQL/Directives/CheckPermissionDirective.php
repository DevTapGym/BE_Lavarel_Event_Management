<?php

namespace App\GraphQL\Directives;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Error\Error;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use App\Models\Role;

class CheckPermissionDirective extends BaseDirective implements FieldMiddleware
{
    public static function definition(): string
    {
        return
            /** @lang GraphQL */
            <<<'GRAPHQL'
"""
Kiểm tra quyền của user dựa theo vai trò/permission.
Truyền tham số `permission` để chỉ rõ quyền cần có.
"""
directive @checkPermission(permission: String!) on FIELD_DEFINITION
GRAPHQL;
    }

    public function handleField(FieldValue $fieldValue): void
    {
        $requiredPermission = $this->directiveArgValue('permission');

        $fieldValue->wrapResolver(
            fn(Closure $resolver): Closure =>
            fn($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) =>
            $this->authorize($resolver, $root, $args, $context, $resolveInfo, $requiredPermission)
        );
    }

    protected function authorize(
        Closure $resolver,
        $root,
        array $args,
        GraphQLContext $context,
        ResolveInfo $resolveInfo,
        string $requiredPermission
    ) {
        $user = $context->user();

        if (!$user) {
            throw new Error('Unauthorized: User not authenticated');
        }

        // Nếu là ADMIN thì bỏ qua kiểm tra permission
        if (is_array($user->roles) && in_array('ADMIN', $user->roles)) {
            return $resolver($root, $args, $context, $resolveInfo);
        }

        // Kiểm tra permission
        if (!$this->checkUserPermission($user, $requiredPermission)) {
            throw new Error("Forbidden: You don't have permission to perform this action ($requiredPermission)");
        }

        return $resolver($root, $args, $context, $resolveInfo);
    }

    /**
     * Kiểm tra user có permission không
     */
    protected function checkUserPermission($user, string $permission): bool
    {
        if (empty($user->roles) || !is_array($user->roles)) {
            return false;
        }

        foreach ($user->roles as $roleName) {
            $role = Role::where('name', $roleName)->first();

            if ($role && is_array($role->permissions) && in_array($permission, $role->permissions)) {
                return true;
            }
        }

        return false;
    }
}
