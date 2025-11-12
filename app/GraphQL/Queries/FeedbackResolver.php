<?php

namespace App\GraphQL\Queries;

use App\Models\Feedback;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class FeedbackResolver
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

        $registrationIds = Registration::where('user_id', $userId)
            ->get()
            ->pluck('_id')
            ->map(fn($id) => (string) $id)
            ->toArray();

        return Feedback::whereIn('registration_id', $registrationIds)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
