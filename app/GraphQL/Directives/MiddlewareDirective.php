<?php

namespace App\GraphQL\Directives;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Pipeline\Pipeline;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

final class MiddlewareDirective extends BaseDirective implements FieldMiddleware
{
    public static function definition(): string
    {
        return
        /** @lang GraphQL */
        <<<'GRAPHQL'
directive @middleware(checks: [String!]!) repeatable on FIELD_DEFINITION
GRAPHQL;
    }

    public function handleField(FieldValue $fieldValue): void
    {
        $middlewares = $this->directiveArgValue('checks', []);

        $fieldValue->wrapResolver(
            fn(Closure $resolver): Closure =>
            fn($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) =>
            app(Pipeline::class)
                ->send($context->request())
                ->through(collect($middlewares)->map(
                    fn($name) =>
                    app('router')->getMiddleware()[$name] ?? $name
                )->toArray())
                ->then(fn() => $resolver($root, $args, $context, $resolveInfo))
        );
    }
}
