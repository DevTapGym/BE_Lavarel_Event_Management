<?php

namespace App\GraphQL\Mutations;

use App\Models\Event;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\CreateEventRequest;

class EventMutation
{
    public function create($_, array $args)
    {
        $request = new CreateEventRequest();
        $validator = Validator::make($args, $request->rules(), $request->messages());
        $validator->validate();
        $event = Event::create($args);
        return $event;
    }
}
