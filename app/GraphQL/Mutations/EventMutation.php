<?php

namespace App\GraphQL\Mutations;

use App\Models\Event;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\CreateEventRequest;
use App\Http\Requests\UpdateEventRequest;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Exception;

class EventMutation
{
    public function create($_, array $args)
    {
        $request = new CreateEventRequest();
        $validator = Validator::make($args, $request->rules(), $request->messages());
        $validator->validate();

        $start = Carbon::parse($args['start_date']);
        $end = Carbon::parse($args['end_date']);

        $conflict = Event::where('location_id', $args['location_id'])
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($query2) use ($start, $end) {
                        $query2->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'location_id' => ['Tại địa điểm này đã có sự kiện trùng thời gian. Vui lòng chọn khung giờ khác.'],
            ]);
        }

        $event = Event::create($args);
        return $event;
    }

    public function update($_, array $args)
    {
        $request = new UpdateEventRequest();
        $validator = Validator::make($args, $request->rules(), $request->messages());
        $validator->validate();

        $event = Event::findOrFail($args['id']);

        // Nếu có thay đổi thời gian hoặc địa điểm thì mới cần kiểm tra trùng
        $locationId = $args['location_id'] ?? $event->location_id;
        $start = isset($args['start_date']) ? Carbon::parse($args['start_date']) : $event->start_date;
        $end = isset($args['end_date']) ? Carbon::parse($args['end_date']) : $event->end_date;

        // Kiểm tra trùng sự kiện khác trong cùng địa điểm
        $conflict = Event::where('location_id', $locationId)
            ->where('id', '!=', $event->id) // bỏ qua chính sự kiện đang cập nhật
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($query2) use ($start, $end) {
                        $query2->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'location_id' => ['Tại địa điểm này đã có sự kiện trùng thời gian. Vui lòng chọn khung giờ khác.'],
            ]);
        }

        foreach ($args as $key => $value) {
            if ($key === 'id') continue;
            $event->$key = $value;
        }

        $event->save();
        return $event->fresh();
    }


    public function advanceStatus($_, array $args)
    {
        $event = Event::findOrFail($args['id']);
        $nextStatus = $event->advanceStatus();

        if (!$nextStatus) {
            throw new Exception('Không thể tiến trạng thái tiếp theo');
        }

        return $event->fresh();
    }

    public function cancelEvent($_, array $args)
    {
        $event = Event::findOrFail($args['id']);
        $event->addStatus('CANCELLED');
        return $event->fresh();
    }

    public function updateApprovalStatus($_, array $args)
    {
        $event = Event::findOrFail($args['id']);

        // Chỉ cho phép 2 giá trị
        $status = strtoupper($args['status'] ?? '');
        if (!in_array($status, ['APPROVED', 'REJECTED'])) {
            throw ValidationException::withMessages([
                'status' => ['Trạng thái phê duyệt không hợp lệ. Chỉ APPROVED hoặc REJECTED.'],
            ]);
        }

        return $event->addApprovalStatus($status);
    }
}
