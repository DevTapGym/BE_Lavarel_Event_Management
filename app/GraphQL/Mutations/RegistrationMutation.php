<?php

namespace App\GraphQL\Mutations;

use App\Models\Registration;
use App\Models\Event;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Exception;

class RegistrationMutation
{
    public function create($_, array $args)
    {
        try {
            // Kiểm tra user có tồn tại không
            $user = User::find($args['user_id']);
            if (!$user) {
                throw ValidationException::withMessages([
                    'user_id' => ['User không tồn tại.'],
                ]);
            }

            // Kiểm tra event có tồn tại không
            $event = Event::find($args['event_id']);
            if (!$event) {
                throw ValidationException::withMessages([
                    'event_id' => ['Sự kiện không tồn tại.'],
                ]);
            }

            // Kiểm tra xem user đã đăng ký chưa (chưa bị cancel)
            $existingRegistration = Registration::where('user_id', $args['user_id'])
                ->where('event_id', $args['event_id'])
                ->get()
                ->filter(function ($reg) {
                    return $reg->getCurrentStatusAttribute() !== 'CANCELLED';
                })
                ->first();

            if ($existingRegistration) {
                throw ValidationException::withMessages([
                    'user_id' => ['Bạn đã đăng ký sự kiện này rồi.'],
                ]);
            }

            $currentConfirmed = $event->current_confirmed ?? 0;
            $currentWaiting = $event->current_waiting ?? 0;
            $capacity = $event->capacity;
            $waitingCapacity = $event->waiting_capacity ?? 0;

            $status = 'CONFIRMED';
            $queueOrder = null;

            // Kiểm tra capacity
            if ($currentConfirmed < $capacity) {
                // Còn chỗ confirmed
                $status = 'CONFIRMED';
                $event->increment('current_confirmed');
            } elseif ($currentWaiting < $waitingCapacity) {
                // Hết chỗ confirmed, vào waiting list
                $status = 'WAITING';

                // Tính queue_order: Tìm queue_order lớn nhất hiện tại + 1
                $maxQueueOrder = Registration::where('event_id', $args['event_id'])
                    ->whereNotNull('queue_order')
                    ->max('queue_order');

                $queueOrder = ($maxQueueOrder ?? 0) + 1;

                $event->increment('current_waiting');
            } else {
                // Hết chỗ cả confirmed và waiting
                throw ValidationException::withMessages([
                    'event_id' => ['Sự kiện đã đầy. Không thể đăng ký thêm.'],
                ]);
            }

            // Tạo registration
            $registration = new Registration([
                'user_id' => $args['user_id'],
                'event_id' => $args['event_id'],
                'queue_order' => $queueOrder,
                'registration_at' => now(),
            ]);

            // Override status_history với status phù hợp
            $registration->status_history = [[
                'name' => $status,
                'sequence' => 1,
                'changed_at' => now(),
            ]];

            $registration->save();

            return $registration->fresh();
        } catch (ValidationException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new Exception('Failed to create registration: ' . $e->getMessage());
        }
    }

    public function cancel($_, array $args)
    {
        try {
            $registration = Registration::findOrFail($args['id']);
            $currentStatus = $registration->getCurrentStatusAttribute();

            if ($currentStatus === 'CANCELLED') {
                throw new Exception('Đăng ký này đã bị hủy rồi.');
            }

            $event = Event::findOrFail($registration->event_id);
            if ($currentStatus === 'CONFIRMED') {
                $waitingRegistrations = Registration::where('event_id', $registration->event_id)
                    ->orderBy('queue_order', 'asc')
                    ->get()
                    ->filter(function ($reg) {
                        return $reg->getCurrentStatusAttribute() === 'WAITING';
                    });

                if ($waitingRegistrations->isNotEmpty()) {
                    $firstWaiting = $waitingRegistrations->first();
                    $firstWaiting->addStatus('CONFIRMED');
                    $firstWaiting->queue_order = null;
                    $firstWaiting->save();
                    $event->decrement('current_waiting');
                } else {
                    $event->decrement('current_confirmed');
                }
            } elseif ($currentStatus === 'WAITING') {
                $event->decrement('current_waiting');
            }
            $registration->addStatus('CANCELLED');
            $registration->cancel_reason = $args['cancel_reason'] ?? null;
            $registration->save();
            return $registration->fresh();
        } catch (Exception $e) {
            throw new Exception('Failed to cancel registration: ' . $e->getMessage());
        }
    }
}
