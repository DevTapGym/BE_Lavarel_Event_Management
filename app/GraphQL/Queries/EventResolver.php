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
            throw new Exception('Internal server error: '.$e->getMessage());
        }
    }

    public function find($_, array $args)
    {
        try {
            $event = Event::find($args['id']);
            if (! $event) {
                throw new Exception('Event not found');
            }

            return $event;
        } catch (Exception $e) {
            throw new Exception('Internal server error: '.$e->getMessage());
        }
    }

    public function byCreator($_, array $args)
    {
        try {
            $email = $args['email'];

            // Kiểm tra email có tồn tại không
            $events = Event::where('created_by', $email)->get();

            if ($events->isEmpty()) {
                throw new Exception('Không tìm thấy sự kiện nào được tạo bởi email: '.$email);
            }

            return $events;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
