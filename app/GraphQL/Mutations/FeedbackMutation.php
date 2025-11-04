<?php

namespace App\GraphQL\Mutations;

use App\Models\Feedback;
use GraphQL\Error\Error;

class FeedbackMutation
{
    public function create($rootValue, array $args)
    {
        try {
            $input = $args['input'];

            // Validate rating range (1-5)
            if (isset($input['rating']) && ($input['rating'] < 1 || $input['rating'] > 5)) {
                throw new Error('Rating must be between 1 and 5');
            }

            $feedback = Feedback::create([
                'registration_id' => $input['registration_id'],
                'event_id' => $input['event_id'],
                'rating' => $input['rating'],
                'comments' => $input['comments'] ?? null,
            ]);

            return $feedback;
        } catch (\Exception $e) {
            throw new Error('Failed to create feedback: ' . $e->getMessage());
        }
    }

    public function update($rootValue, array $args)
    {
        try {
            $feedback = Feedback::findOrFail($args['id']);
            $input = $args['input'];

            // Validate rating range if provided
            if (isset($input['rating']) && ($input['rating'] < 1 || $input['rating'] > 5)) {
                throw new Error('Rating must be between 1 and 5');
            }

            // Filter out null values
            $updateData = array_filter([
                'registration_id' => $input['registration_id'] ?? null,
                'event_id' => $input['event_id'] ?? null,
                'rating' => $input['rating'] ?? null,
                'comments' => $input['comments'] ?? null,
            ], function ($value) {
                return $value !== null;
            });

            $feedback->update($updateData);

            return $feedback->fresh();
        } catch (\Exception $e) {
            throw new Error('Failed to update feedback: ' . $e->getMessage());
        }
    }

    public function delete($rootValue, array $args)
    {
        try {
            $feedback = Feedback::findOrFail($args['id']);
            $feedback->delete();

            return $feedback;
        } catch (\Exception $e) {
            throw new Error('Failed to delete feedback: ' . $e->getMessage());
        }
    }
}
