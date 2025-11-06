<?php

namespace App\GraphQL\Mutations;

use App\Models\Feedback;
use App\Models\Registration;
use App\Models\Event;
use Illuminate\Validation\ValidationException;
use GraphQL\Error\Error;

class FeedbackMutation
{
    public function create($rootValue, array $args)
    {
        try {
            $input = $args['input'];

            // Kiểm tra registration có tồn tại không
            $registration = Registration::find($input['registration_id']);
            if (!$registration) {
                throw ValidationException::withMessages([
                    'registration_id' => ['Registration không tồn tại.'],
                ]);
            }

            // Kiểm tra event có tồn tại không
            $event = Event::find($input['event_id']);
            if (!$event) {
                throw ValidationException::withMessages([
                    'event_id' => ['Sự kiện không tồn tại.'],
                ]);
            }

            // Kiểm tra registration có phải của event này không
            if ($registration->event_id !== $input['event_id']) {
                throw ValidationException::withMessages([
                    'registration_id' => ['Registration này không thuộc về sự kiện này.'],
                ]);
            }

            // Kiểm tra xem registration này đã feedback chưa
            $existingFeedback = Feedback::where('registration_id', $input['registration_id'])
                ->where('event_id', $input['event_id'])
                ->first();

            if ($existingFeedback) {
                throw ValidationException::withMessages([
                    'registration_id' => ['Bạn đã feedback cho sự kiện này rồi. Mỗi registration chỉ có thể feedback 1 lần.'],
                ]);
            }

            // Validate rating range (1-5)
            if (isset($input['rating']) && ($input['rating'] < 1 || $input['rating'] > 5)) {
                throw ValidationException::withMessages([
                    'rating' => ['Rating phải từ 1 đến 5.'],
                ]);
            }

            $feedback = Feedback::create([
                'registration_id' => $input['registration_id'],
                'event_id' => $input['event_id'],
                'rating' => $input['rating'],
                'comments' => $input['comments'] ?? null,
                'is_hidden' => false,
            ]);

            return $feedback;
        } catch (ValidationException $e) {
            throw $e;
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
