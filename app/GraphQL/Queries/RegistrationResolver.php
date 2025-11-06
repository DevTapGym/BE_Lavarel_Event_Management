<?php

namespace App\GraphQL\Queries;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RegistrationResolver
{
    public function byUser($_, array $args)
    {
        $userId = $args['user_id'] ?? null;
        if (!$userId) {
            throw ValidationException::withMessages([
                'user_id' => ['user_id is required']
            ]);
        }

        if (!User::find($userId)) {
            throw ValidationException::withMessages([
                'user_id' => ['User not found']
            ]);
        }

        return Registration::where('user_id', $userId)
            ->orderBy('queue_order', 'asc')
            ->orderBy('registration_at', 'desc')
            ->get();
    }
}
