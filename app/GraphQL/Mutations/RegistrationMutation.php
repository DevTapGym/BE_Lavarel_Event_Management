<?php

namespace App\GraphQL\Mutations;

use App\Models\Registration;
use Exception;

class RegistrationMutation
{
    public function create($_, array $args)
    {
        try {
            return Registration::create([
                'user_id' => $args['user_id'],
                'event_id' => $args['event_id'],
                'queue_order' => $args['queue_order'] ?? null,
                'status' => $args['status'] ?? 'WAITING_CONFIRM',
                'registration_at' => now(),
            ]);
        } catch (Exception $e) {
            throw new Exception('Failed to create registration: ' . $e->getMessage());
        }
    }

    public function update($_, array $args)
    {
        try {
            $registration = Registration::findOrFail($args['id']);

            $registration->update(array_filter([
                'user_id' => $args['user_id'] ?? null,
                'event_id' => $args['event_id'] ?? null,
                'registration_at' => $args['registration_at'] ?? null,
                'cancelled_at' => $args['cancelled_at'] ?? null,
                'queue_order' => $args['queue_order'] ?? null,
                'status' => $args['status'] ?? null,
                'cancel_reason' => $args['cancel_reason'] ?? null,
            ], fn($value) => $value !== null));

            return $registration->fresh();
        } catch (Exception $e) {
            throw new Exception('Failed to update registration: ' . $e->getMessage());
        }
    }

    public function cancel($_, array $args)
    {
        try {
            $registration = Registration::findOrFail($args['id']);

            if ($registration->status === 'CANCELLED') {
                throw new Exception('Registration is already cancelled');
            }

            $registration->update([
                'status' => 'CANCELLED',
                'cancelled_at' => now(),
                'cancel_reason' => $args['cancel_reason'] ?? 'User cancelled',
            ]);

            return $registration->fresh();
        } catch (Exception $e) {
            throw new Exception('Failed to cancel registration: ' . $e->getMessage());
        }
    }
}
