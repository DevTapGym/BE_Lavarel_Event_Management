# Hướng dẫn sử dụng CalculateEventPointsJob

## 📋 Mô tả

Job này tự động kiểm tra các sự kiện đã kết thúc và trừ 7 điểm cho những người đăng ký nhưng không tham dự (không điểm danh).

## 🔄 Cách hoạt động

1. **Tìm sự kiện đã kết thúc**: Tìm tất cả sự kiện có `end_date < now()` và status = `ENDED`
2. **Lấy danh sách vắng mặt**: Tìm registrations có:
    - Status = `CONFIRMED`
    - `is_attended = false` (chưa điểm danh)
3. **Trừ điểm**:
    - Trừ 7 điểm từ `reputation_score`
    - Không cho điểm âm (min = 0)
    - Ghi vào `history_points` với action_type = `NO_SHOW`
4. **Tránh trùng lặp**: Kiểm tra đã trừ điểm chưa (dựa vào history_points)

## 🚀 Cách chạy Job

### 1. Chạy thủ công (Manual)

```bash
# Chạy job ngay lập tức
php artisan queue:work --once

# Hoặc dispatch từ code
use App\Jobs\CalculateEventPointsJob;
CalculateEventPointsJob::dispatch();
```

### 2. Chạy tự động với Schedule (Recommended)

Thêm vào file `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Chạy mỗi ngày lúc 2:00 sáng
    $schedule->job(new \App\Jobs\CalculateEventPointsJob)
        ->dailyAt('02:00')
        ->name('calculate-event-points')
        ->withoutOverlapping();

    // Hoặc chạy mỗi giờ
    $schedule->job(new \App\Jobs\CalculateEventPointsJob)
        ->hourly()
        ->name('calculate-event-points')
        ->withoutOverlapping();
}
```

Sau đó chạy scheduler:

```bash
# Development (chạy scheduler)
php artisan schedule:work

# Production (thêm vào cron)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Cấu hình Queue

Trong file `.env`:

```env
QUEUE_CONNECTION=database
# Hoặc
QUEUE_CONNECTION=redis
```

Chạy queue worker:

```bash
# Development
php artisan queue:work

# Production (sử dụng supervisor)
php artisan queue:work --daemon --tries=3
```

## 📊 Logging

Job ghi log chi tiết vào `storage/logs/laravel.log`:

```
[INFO] CalculateEventPointsJob started
[INFO] Found 3 ended events to process
[INFO] Processing event: 673fc8b7fb67e9a1b1053595 - Hội nghị AI 2025
[INFO] Found 5 absent users for event 673fc8b7fb67e9a1b1053595
[INFO] Deducted 7 points from user 673fc8b7fb67e9a1b1053590 (student@huit.edu.vn)
[INFO] Processed 5 users for event 673fc8b7fb67e9a1b1053595
[INFO] CalculateEventPointsJob completed successfully
```

## 📋 Dữ liệu trong History Points

```json
{
    "user_id": "user123",
    "event_id": "event456",
    "old_point": 100,
    "new_point": 93,
    "change_amount": -7,
    "action_type": "NO_SHOW",
    "reason": "Không tham dự sự kiện: Hội nghị AI 2025",
    "created_at": "2025-11-09T02:00:00"
}
```

## ⚠️ Điều kiện trừ điểm

Chỉ trừ điểm khi:

-   ✅ Sự kiện đã kết thúc (`end_date < now`)
-   ✅ Sự kiện có status = `ENDED`
-   ✅ Registration có status = `CONFIRMED`
-   ✅ User chưa điểm danh (`is_attended = false`)
-   ✅ Chưa bị trừ điểm trước đó (kiểm tra history_points)

Không trừ điểm nếu:

-   ❌ Registration có status = `CANCELLED`
-   ❌ Registration có status = `WAITING`
-   ❌ User đã điểm danh (`is_attended = true`)
-   ❌ Đã trừ điểm rồi (có record trong history_points)

## 🔍 Testing

```bash
# Tạo migration cho jobs table (nếu chưa có)
php artisan queue:table
php artisan migrate

# Chạy test
php artisan queue:work --once

# Kiểm tra log
tail -f storage/logs/laravel.log

# Kiểm tra database
# - Xem history_points mới
# - Xem reputation_score đã giảm
```

## 🛠️ Troubleshooting

### Job không chạy?

1. Kiểm tra queue connection: `php artisan config:clear`
2. Kiểm tra queue worker đang chạy: `ps aux | grep queue:work`
3. Xem failed jobs: `php artisan queue:failed`
4. Retry failed jobs: `php artisan queue:retry all`

### Trừ điểm 2 lần?

-   Job có cơ chế kiểm tra `history_points` để tránh trùng lặp
-   Nếu vẫn bị, kiểm tra index database cho query `NO_SHOW`

### Performance với nhiều sự kiện?

-   Job xử lý từng sự kiện tuần tự
-   Có thể optimize bằng cách chunk hoặc dispatch nhiều jobs con
-   Thêm `->onQueue('low')` để chạy ở queue riêng

## 📝 Notes

-   Job sử dụng `ShouldQueue` → chạy async (không blocking)
-   Có logging chi tiết để debug
-   Tránh trừ điểm 2 lần cho cùng 1 user/event
-   Điểm không bao giờ âm (min = 0)
