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
}
