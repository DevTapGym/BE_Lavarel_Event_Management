<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\Models\HistoryPoints;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class CalculateEventPointsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * Kiểm tra các sự kiện đã kết thúc và trừ điểm cho những người không tham dự
     */
    public function handle(): void
    {
        try {
            Log::info('Bắt đầu thực thi CalculateEventPointsJob');

            // Lấy tất cả các sự kiện đã kết thúc (end_date < now)
            $now = Carbon::now();
            $endedEvents = Event::where('end_date', '<', $now)
                ->get()
                ->filter(function ($event) {
                    // Chỉ xử lý sự kiện có status ENDED
                    return $event->getCurrentStatusAttribute() === 'ENDED';
                });

            Log::info('Tìm thấy ' . $endedEvents->count() . ' sự kiện đã kết thúc cần xử lý');

            foreach ($endedEvents as $event) {
                $this->processEvent($event);
            }

            Log::info('CalculateEventPointsJob hoàn thành thành công');
        } catch (\Exception $e) {
            Log::error('CalculateEventPointsJob thất bại: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Xử lý từng sự kiện: trừ điểm cho người không tham dự
     */
    private function processEvent(Event $event): void
    {
        try {
            Log::info("Đang xử lý sự kiện: {$event->_id} - {$event->title}");

            // Lấy tất cả registrations của sự kiện này
            // Chỉ xử lý những registration có status CONFIRMED và chưa điểm danh
            $absentRegistrations = Registration::where('event_id', (string) $event->_id)
                ->where('is_attended', false) // Chưa điểm danh
                ->get()
                ->filter(function ($registration) {
                    // Chỉ trừ điểm cho những registration CONFIRMED
                    return $registration->getCurrentStatusAttribute() === 'CONFIRMED';
                });

            if ($absentRegistrations->isEmpty()) {
                Log::info("Không có người dùng vắng mặt cho sự kiện {$event->_id}");
                return;
            }

            Log::info("Tìm thấy {$absentRegistrations->count()} người dùng vắng mặt cho sự kiện {$event->_id}");

            $pointsToDeduct = 7; // Trừ 7 điểm
            $processedCount = 0;

            foreach ($absentRegistrations as $registration) {
                try {
                    $user = User::find($registration->user_id);

                    if (!$user) {
                        Log::warning("Không tìm thấy người dùng: {$registration->user_id}");
                        continue;
                    }

                    // Kiểm tra xem đã trừ điểm cho registration này chưa
                    $existingHistory = HistoryPoints::where('user_id', (string) $user->_id)
                        ->where('event_id', (string) $event->_id)
                        ->where('action_type', 'NO_SHOW')
                        ->first();

                    if ($existingHistory) {
                        Log::info("Đã trừ điểm cho người dùng {$user->_id} tại sự kiện {$event->_id}");
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
                        eventId: (string) $event->_id,
                        oldPoint: $oldPoint,
                        newPoint: $newPoint,
                        actionType: 'NO_SHOW',
                        reason: "Không tham dự sự kiện: {$event->title}"
                    );

                    $processedCount++;
                    Log::info("Đã trừ {$pointsToDeduct} điểm từ người dùng {$user->_id} ({$user->email})");
                } catch (Exception $e) {
                    Log::error("Không thể xử lý đăng ký {$registration->_id}: " . $e->getMessage());
                    continue;
                }
            }

            Log::info("Đã xử lý {$processedCount} người dùng cho sự kiện {$event->_id}");
        } catch (Exception $e) {
            Log::error("Không thể xử lý sự kiện {$event->_id}: " . $e->getMessage());
            throw $e;
        }
    }
}
