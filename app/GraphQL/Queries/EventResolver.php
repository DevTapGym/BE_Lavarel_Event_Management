<?php

namespace App\GraphQL\Queries;

use App\Models\Event;
use App\Traits\ApiResponse;
use Exception;

class EventResolver
{
    use ApiResponse;

    public function all($_, array $args)
    {
        return Event::all();
    }
}
