<?php

namespace App\GraphQL;

use App\Traits\ApiResponse;
use Closure;
use GraphQL\Error\Error;

class GraphQLExceptionHandler
{
    use ApiResponse;

    /**
     * Handle GraphQL errors
     *
     * @param Error $error
     * @param Closure $next
     * @return array|null
     */
    public function __invoke(Error $error, Closure $next): ?array
    {
        $exception = $error->getPrevious();

        // Format error using ApiResponse trait
        $errorDetails = [
            'message' => $error->getMessage(),
        ];

        if ($error->getLocations()) {
            $errorDetails['locations'] = array_map(function ($location) {
                return [
                    'line' => $location->line,
                    'column' => $location->column,
                ];
            }, $error->getLocations());
        }

        if ($error->getPath()) {
            $errorDetails['path'] = $error->getPath();
        }

        // Add exception details in debug mode
        if (config('app.debug') && $exception) {
            $errorDetails['debug'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
            ];
        }

        // Return formatted error using trait
        return [
            'status' => 400,
            'error' => 'GRAPHQL_ERROR',
            'details' => $errorDetails
        ];
    }
}
