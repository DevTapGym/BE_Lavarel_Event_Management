<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Event;
use Illuminate\Http\Request;
use Exception;

use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function getAllNotification()
    {
        $notifications = Notification::all();

        return $this->successResponse(200, 'Lấy tất cả thông báo thành công.', $notifications);
    }

    public function notificationsByEvent($eventId)
    {
        return response()->stream(function () use ($eventId) {
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            $this->sendSSENotifications($eventId);

            $lastCheckTime = now();
            $maxDuration = 300;
            $startTime = time();

            while (true) {
                if ((time() - $startTime) > $maxDuration) {
                    echo "event: timeout\n";
                    echo "data: {\"message\": \"Connection timeout\"}\n\n";
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                    break;
                }

                $newNotifications = Notification::where('event_id', (string) $eventId)
                    ->where('created_at', '>', $lastCheckTime)
                    ->get();

                if ($newNotifications->isNotEmpty()) {
                    foreach ($newNotifications as $notification) {
                        $this->sendSSEMessage('notification', $notification);
                    }
                    $lastCheckTime = now();
                }

                echo ": heartbeat\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

                if (connection_aborted()) {
                    break;
                }

                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function sendSSENotifications($eventId)
    {
        $notifications = Notification::where('event_id', (string) $eventId)
            ->orderBy('created_at', 'desc')
            ->get();

        $this->sendSSEMessage('initial', [
            'count' => $notifications->count(),
            'notifications' => $notifications
        ]);
    }

    private function sendSSEMessage($event, $data)
    {
        echo "event: {$event}\n";
        echo "data: " . json_encode($data) . "\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|string',
            'organizer_id' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(422, $validator->errors(), 'Dữ liệu không hợp lệ');
        }

        $data = $validator->validated();

        // Kiểm tra event tồn tại (nếu muốn)
        if (!empty($data['event_id']) && !Event::find($data['event_id'])) {
            return $this->errorResponse(404, null, 'Sự kiện không tồn tại');
        }

        try {
            $notification = Notification::create($data);
            return $this->successResponse(201, 'Tạo thông báo thành công.', $notification);
        } catch (Exception $e) {
            return $this->errorResponse(500, null, 'Tạo thông báo thất bại');
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(422, $validator->errors(), 'Dữ liệu không hợp lệ');
        }

        $notification = Notification::find($id);
        if (!$notification) {
            return $this->errorResponse(404, null, 'Thông báo không tìm thấy');
        }

        $notification->fill($validator->validated());

        try {
            $notification->save();
            return $this->successResponse(200, 'Cập nhật thông báo thành công.', $notification);
        } catch (Exception $e) {
            return $this->errorResponse(500, null, 'Cập nhật thất bại');
        }
    }

    public function delete($id)
    {
        $notification = Notification::find($id);
        if (!$notification) {
            return $this->errorResponse(404, null, 'Thông báo không tìm thấy');
        }

        try {
            $notification->delete();
            return $this->successResponse(200, 'Xóa thông báo thành công.', null);
        } catch (Exception $e) {
            return $this->errorResponse(500, null, 'Xóa thông báo thất bại');
        }
    }
}
