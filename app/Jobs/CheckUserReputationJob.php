<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class CheckUserReputationJob implements ShouldQueue
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
     * Kiểm tra điểm reputation của tất cả users và gửi cảnh báo
     * 
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        try {
            echo "Bắt đầu thực thi CheckUserReputationJob tại " . Carbon::now()->toDateTimeString() . "\n";

            $lowReputationUsers = User::where('reputation_score', '<', 60)
                ->where('is_active', true)
                ->get();

            $warningCount = 0;
            $blockedCount = 0;

            foreach ($lowReputationUsers as $user) {
                try {
                    $this->checkAndNotifyUser($user, $warningCount, $blockedCount);
                } catch (Exception $e) {
                    continue;
                }
            }

            echo "CheckUserReputationJob hoàn thành: {$warningCount} cảnh báo, {$blockedCount} bị chặn\n";
        } catch (Exception $e) {
            echo "CheckUserReputationJob thất bại: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    private function checkAndNotifyUser(User $user, int &$warningCount, int &$blockedCount): void
    {
        /** @var User $user */
        /** @var int $warningCount */
        /** @var int $blockedCount */
        $score = $user->reputation_score ?? 70;

        if ($score < 50) {
            // Điểm dưới 50 - BỊ CHẶN ĐĂNG KÝ

            if ($user->hasRecentReputationAlert('BLOCK_REGISTRATION', 30)) {
                echo "  → User {$user->email} đã có cảnh báo BLOCK trong 30 ngày, bỏ qua\n";
                return;
            }

            $alert = [
                'title' => 'Bạn đã bị chặn đăng ký sự kiện',
                'message' => "Điểm uy tín của bạn hiện tại là {$score}/100. Bạn không thể đăng ký sự kiện mới vì điểm dưới 50. Vui lòng chờ đến kỳ sau hoặc liên hệ quản trị viên để được hỗ trợ.",
                'type' => 'BLOCK_REGISTRATION',
            ];
            $blockedCount++;
        } elseif ($score < 60) {
            // Điểm từ 50-59 - CẢNH BÁO

            if ($user->hasRecentReputationAlert('WARNING', 30)) {
                echo "  → User {$user->email} đã có cảnh báo WARNING trong 30 ngày, bỏ qua\n";
                return;
            }

            $pointsToBlock = $score - 50;
            $alert = [
                'title' => 'Cảnh báo điểm uy tín thấp',
                'message' => "Điểm uy tín của bạn hiện tại là {$score}/100. Bạn chỉ còn {$pointsToBlock} điểm nữa là sẽ bị chặn đăng ký sự kiện (dưới 50 điểm). Hãy tham gia sự kiện đầy đủ để tránh bị trừ điểm!",
                'type' => 'WARNING',
            ];
            $warningCount++;
        } else {
            return;
        }

        $user->addAlert($alert);
        echo "Đã thêm alert cho user {$user->_id}: {$alert['title']}\n";
    }
}
