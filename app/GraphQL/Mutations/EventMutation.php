<?php

namespace App\GraphQL\Mutations;

use App\Models\Event;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\CreateEventRequest;

class EventMutation
{
    public function create($_, array $args)
    {
        $input = $args['input'];

        $validator = Validator::make($input, (new CreateEventRequest())->rules());
        $validator->validate();

        return Event::create($validator->validated());
    }
}
