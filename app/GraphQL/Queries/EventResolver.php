<?php

namespace App\GraphQL\Queries;

use App\Models\Event;
use Exception;

class EventResolver
{
    public function all($_, array $args)
    {
        try {
            return Event::all();
        } catch (Exception $e) {
            throw new Exception('Internal server error: ' . $e->getMessage());
        }
    }

    public function find($_, array $args)
    {
        try {
            $event = Event::find($args['id']);
            if (!$event) {
                throw new Exception('Event not found');
            }
            return $event;
        } catch (Exception $e) {
            throw new Exception('Internal server error: ' . $e->getMessage());
        }
    }
}
