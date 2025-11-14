<?php

namespace App\GraphQL\Queries;

use App\Models\HistoryPoints;
use App\Models\User;
use App\Models\Event;
use GraphQL\Error\Error;

class HistoryPointsQuery
{
    public function byUser($_, array $args)
    {
        $user = User::find($args['user_id']);
        if (!$user) {
            throw new Error("User with ID {$args['user_id']} does not exist.");
        }

        return HistoryPoints::where('user_id', $args['user_id'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function byEvent($_, array $args)
    {
        $event = Event::find($args['event_id']);
        if (!$event) {
            throw new Error("Event with ID {$args['event_id']} does not exist.");
        }

        return HistoryPoints::where('event_id', $args['event_id'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
