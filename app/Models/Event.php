<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use MongoDB\Laravel\Eloquent\Model;

class Event extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'events';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            // Khởi tạo status_history mặc định
            if (empty($event->status_history)) {
                $event->status_history = [[
                    'name' => 'UPCOMING',
                    'sequence' => 1,
                    'changed_at' => Carbon::now(),
                ]];
            }

            // Khởi tạo approval_history mặc định
            if (empty($event->approval_history)) {
                $event->approval_history = [[
                    'name' => 'WAITING',
                    'sequence' => 1,
                    'changed_at' => Carbon::now(),
                ]];
            }
        });
    }

    protected $fillable = [
        'title',
        'description',
        'location_id',
        'start_date',
        'end_date',
        'organizer',
        'topic',
        'capacity',
        'waiting_capacity',
        'status_history', // 'UPCOMING', 'OPEN', 'ONGOING', 'ENDED', 'CANCELLED'
        'image_url',
        'approval_history', // WAITING', 'APPROVED', 'REJECTED'
        'current_confirmed',
        'current_waiting',
        'speakers', // Mảng các đối tượng diễn giả
    ];

    public $timestamps = true;

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'capacity' => 'integer',
    ];

    protected $attributes = [
        'current_confirmed' => 0,
        'current_waiting' => 0,
        'speakers' => [],
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /* ---------------- STATUS METHODS ---------------- */

    public function advanceStatus()
    {
        $workflow = ['UPCOMING', 'OPEN', 'ONGOING', 'ENDED'];

        $current = $this->getCurrentStatusAttribute();

        if ($current === 'UPCOMING') {
            $nextStatus = 'OPEN';
        } else {
            $index = array_search($current, $workflow);
            if ($index === false || $index >= count($workflow) - 1) {
                return false;
            }
            $nextStatus = $workflow[$index + 1];
        }

        // Nếu chuyển sang ENDED, xử lý trừ điểm cho người không tham dự
        if ($nextStatus === 'ENDED') {
            $this->processNoShowPenalties();
        }

        $this->addStatus($nextStatus);

        return $nextStatus;
    }

    /**
     * Xử lý trừ điểm cho những người đăng ký CONFIRMED nhưng không điểm danh
     */
    private function processNoShowPenalties()
    {
        try {
            // Lấy tất cả registrations của event này
            $registrations = Registration::where('event_id', (string) $this->_id)->get();

            $pointsToDeduct = 7; // Trừ 7 điểm
            $processedCount = 0;

            foreach ($registrations as $registration) {
                // Chỉ xử lý những registration CONFIRMED và chưa điểm danh
                if ($registration->getCurrentStatusAttribute() !== 'CONFIRMED') {
                    continue;
                }

                if ($registration->is_attended) {
                    continue; // Đã điểm danh, bỏ qua
                }

                // Kiểm tra đã trừ điểm cho registration này chưa
                $existingHistory = HistoryPoints::where('user_id', $registration->user_id)
                    ->where('event_id', (string) $this->_id)
                    ->where('action_type', 'NO_SHOW')
                    ->first();

                if ($existingHistory) {
                    continue; // Đã trừ điểm rồi, bỏ qua
                }

                // Lấy user
                $user = User::find($registration->user_id);
                if (! $user) {
                    Log::warning("Không tìm thấy user {$registration->user_id} khi xử lý NO_SHOW");

                    continue;
                }

                // Trừ điểm
                $oldPoint = $user->reputation_score ?? 0;
                $newPoint = max(0, $oldPoint - $pointsToDeduct); // Không cho điểm âm

                // Cập nhật điểm cho user
                $user->reputation_score = $newPoint;
                $user->save();

                // Ghi vào lịch sử điểm
                HistoryPoints::logChange(
                    userId: (string) $user->_id,
                    eventId: (string) $this->_id,
                    oldPoint: $oldPoint,
                    newPoint: $newPoint,
                    actionType: 'NO_SHOW',
                    reason: "Không tham dự sự kiện: {$this->title}"
                );

                $processedCount++;
                Log::info("Đã trừ {$pointsToDeduct} điểm từ user {$user->_id} ({$user->email}) vì không tham dự event {$this->_id}");
            }

            if ($processedCount > 0) {
                Log::info("Đã xử lý trừ điểm cho {$processedCount} người dùng không tham dự event {$this->_id}");
            }
        } catch (\Exception $e) {
            Log::error("Lỗi khi xử lý NO_SHOW penalties cho event {$this->_id}: ".$e->getMessage());
            // Không throw exception để không làm gián đoạn việc chuyển trạng thái
        }
    }

    public function cancel()
    {
        $this->addStatus('CANCELLED');
    }

    /**
     * Thêm trạng thái mới vào lịch sử trạng thái
     */
    public function addStatus(string $name)
    {
        $sequence = count($this->status_history ?? []) + 1;

        $this->push('status_history', [
            'name' => $name,
            'sequence' => $sequence,
            'changed_at' => Carbon::now(),
        ]);

        $this->save();
    }

    /**
     * Lấy trạng thái hiện tại
     */
    public function getCurrentStatusAttribute()
    {
        $history = $this->status_history ?? [];
        $last = end($history);

        return $last['name'] ?? null;
    }

    /* ---------------- APPROVAL METHODS ---------------- */
    public function addApprovalStatus(string $status)
    {
        $allowed = ['APPROVED', 'REJECTED'];

        if (! in_array($status, $allowed)) {
            throw ValidationException::withMessages([
                'approval_status' => ['Trạng thái phê duyệt không hợp lệ. Chỉ được APPROVED hoặc REJECTED.'],
            ]);
        }

        $history = $this->approval_history ?? [];
        $sequence = count($history) + 1;

        $history[] = [
            'name' => $status,
            'sequence' => $sequence,
            'changed_at' => Carbon::now(),
        ];

        $this->approval_history = $history;
        $this->save();

        return $this;
    }

    /**
     * Lấy trạng thái phê duyệt hiện tại
     */
    public function getCurrentApprovalStatusAttribute()
    {
        $history = $this->approval_history ?? [];
        $last = end($history);

        return $last['name'] ?? null;
    }
}
