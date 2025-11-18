<?php

namespace App\GraphQL\Mutations;

use App\Models\HistoryPoints;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class UserMutation
{
    /**
     * Reset điểm cho toàn bộ người dùng về 70 điểm
     * Ghi lại lịch sử thay đổi điểm trong history_points
     *
     * @param  mixed  $_
     * @return array
     *
     * @throws Exception
     */
    public function resetAllUserPoints($_, array $args)
    {
        try {
            $newPoints = $args['points'] ?? 70;
            $reason = $args['reason'] ?? 'Reset điểm hệ thống cho tất cả người dùng';

            $users = User::all();
            $updatedCount = 0;
            $historyRecords = [];

            foreach ($users as $user) {
                $oldPoints = $user->reputation_score ?? 0;

                // Chỉ cập nhật nếu điểm khác với điểm mới
                if ($oldPoints != $newPoints) {
                    // Cập nhật điểm cho user
                    $user->reputation_score = $newPoints;
                    $user->save();

                    // Tạo bản ghi lịch sử
                    $historyRecord = HistoryPoints::create([
                        'user_id' => (string) $user->_id,
                        'event_id' => null, // Không liên quan đến event cụ thể
                        'old_point' => $oldPoints,
                        'new_point' => $newPoints,
                        'change_amount' => $newPoints - $oldPoints,
                        'action_type' => 'SYSTEM_RESET',
                        'reason' => $reason,
                        'created_at' => Carbon::now(),
                    ]);

                    $historyRecords[] = $historyRecord;
                    $updatedCount++;
                }
            }

            return [
                'success' => true,
                'message' => "Đã cập nhật điểm cho {$updatedCount} người dùng về {$newPoints} điểm",
                'updated_count' => $updatedCount,
                'total_users' => $users->count(),
                'new_points' => $newPoints,
            ];

        } catch (Exception $e) {
            throw new Exception('Lỗi khi reset điểm người dùng: '.$e->getMessage());
        }
    }

    /**
     * Cập nhật trạng thái đọc hoặc xóa của một alert trong user
     *
     * @param  mixed  $_
     * @return array
     *
     * @throws Exception
     */
    public function updateAlert($_, array $args)
    {
        try {
            $userId = $args['user_id'];
            $alertIndex = $args['alert_index'];

            $user = User::find($userId);
            if (! $user) {
                throw new Exception('Không tìm thấy người dùng');
            }

            $alerts = $user->alerts ?? [];

            if (! isset($alerts[$alertIndex])) {
                throw new Exception('Không tìm thấy thông báo tại vị trí '.$alertIndex);
            }

            // Cập nhật is_read nếu có
            if (isset($args['is_read'])) {
                $alerts[$alertIndex]['is_read'] = $args['is_read'];
            }

            // Cập nhật is_deleted nếu có
            if (isset($args['is_deleted'])) {
                $alerts[$alertIndex]['is_deleted'] = $args['is_deleted'];
            }

            // Lưu lại mảng alerts đã cập nhật
            $user->alerts = $alerts;
            $user->save();

            return [
                'success' => true,
                'message' => 'Cập nhật thông báo thành công',
                'alert' => $alerts[$alertIndex],
            ];

        } catch (Exception $e) {
            throw new Exception('Lỗi khi cập nhật thông báo: '.$e->getMessage());
        }
    }

    /**
     * Kiểm tra và gửi cảnh báo cho người dùng có điểm uy tín thấp
     * 
     * @param mixed $_
     * @param array $args
     * @return array
     * @throws Exception
     */
    public function checkAndSendReputationAlerts($_, array $args)
    {
        try {
            // Lấy tất cả users có điểm dưới 60 và đang active
            $lowReputationUsers = User::where('reputation_score', '<', 60)
                ->where('is_active', true)
                ->get();

            $warningCount = 0;
            $blockedCount = 0;
            $skippedCount = 0;
            $alerts = [];

            foreach ($lowReputationUsers as $user) {
                try {
                    $result = $this->processUserReputation($user);
                    
                    if ($result['alert_sent']) {
                        if ($result['type'] === 'BLOCK_REGISTRATION') {
                            $blockedCount++;
                        } else {
                            $warningCount++;
                        }
                        $alerts[] = $result;
                    } else {
                        $skippedCount++;
                    }
                } catch (Exception $e) {
                    $skippedCount++;
                    continue;
                }
            }

            return [
                'success' => true,
                'message' => "Đã xử lý {$lowReputationUsers->count()} người dùng",
                'warning_count' => $warningCount,
                'blocked_count' => $blockedCount,
                'skipped_count' => $skippedCount,
                'total_processed' => $lowReputationUsers->count(),
            ];

        } catch (Exception $e) {
            throw new Exception('Lỗi khi kiểm tra và gửi cảnh báo: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý reputation của một user cụ thể
     * 
     * @param User $user
     * @return array
     */
    private function processUserReputation(User $user): array
    {
        $score = $user->reputation_score ?? 70;
        $alertSent = false;
        $type = null;

        if ($score < 50) {
            // Điểm dưới 50 - BỊ CHẶN ĐĂNG KÝ
            if ($user->hasRecentReputationAlert('BLOCK_REGISTRATION', 30)) {
                return [
                    'alert_sent' => false,
                    'reason' => 'Already has recent BLOCK alert',
                ];
            }

            // Lấy index của alert mới (số lượng alerts hiện tại)
            $alertIndex = count($user->alerts ?? []);

            $alert = [
                'alert_index' => $alertIndex,
                'title' => 'Bạn đã bị chặn đăng ký sự kiện',
                'message' => "Điểm uy tín của bạn hiện tại là {$score}/100. Bạn không thể đăng ký sự kiện mới vì điểm dưới 50. Vui lòng chờ đến kỳ sau hoặc liên hệ quản trị viên để được hỗ trợ.",
                'type' => 'BLOCK_REGISTRATION',
            ];
            
            $user->addAlert($alert);
            $alertSent = true;
            $type = 'BLOCK_REGISTRATION';

        } elseif ($score < 60) {
            // Điểm từ 50-59 - CẢNH BÁO
            if ($user->hasRecentReputationAlert('WARNING', 30)) {
                return [
                    'alert_sent' => false,
                    'reason' => 'Already has recent WARNING alert',
                ];
            }

            // Lấy index của alert mới (số lượng alerts hiện tại)
            $alertIndex = count($user->alerts ?? []);

            $pointsToBlock = $score - 50;
            $alert = [
                'alert_index' => $alertIndex,
                'title' => 'Cảnh báo điểm uy tín thấp',
                'message' => "Điểm uy tín của bạn hiện tại là {$score}/100. Bạn chỉ còn {$pointsToBlock} điểm nữa là sẽ bị chặn đăng ký sự kiện (dưới 50 điểm). Hãy tham gia sự kiện đầy đủ để tránh bị trừ điểm!",
                'type' => 'WARNING',
            ];
            
            $user->addAlert($alert);
            $alertSent = true;
            $type = 'WARNING';
        }

        return [
            'alert_sent' => $alertSent,
            'type' => $type,
            'user_id' => (string) $user->_id,
            'email' => $user->email,
            'score' => $score,
        ];
    }
}
