# Hướng dẫn Setup Scheduler cho CalculateEventPointsJob

## 🎯 Mục đích
Job sẽ tự động chạy **mỗi 2 tiếng** để kiểm tra các sự kiện đã kết thúc và trừ điểm cho người dùng không tham dự.

## ✅ Đã cấu hình
File `routes/console.php` đã được cấu hình để chạy job mỗi 2 tiếng với các tùy chọn:
- `everyTwoHours()`: Chạy mỗi 2 tiếng (00:00, 02:00, 04:00, ...)
- `withoutOverlapping()`: Không chạy instance mới nếu job trước chưa xong
- `onOneServer()`: Chỉ chạy trên 1 server (nếu có nhiều server)

## 📋 Các bước setup

### 1. Kiểm tra cấu hình Queue
Đảm bảo file `.env` có cấu hình queue:
```env
QUEUE_CONNECTION=database
```

### 2. Tạo bảng queue (nếu chưa có)
```bash
php artisan queue:table
php artisan migrate
```

### 3. Setup Cron Job (Linux/Mac)
Mở crontab:
```bash
crontab -e
```

Thêm dòng này (thay đổi đường dẫn phù hợp):
```bash
* * * * * cd /path/to/BE_QuanLySuKien_HoiThao && php artisan schedule:run >> /dev/null 2>&1
```

**Windows:** Sử dụng Task Scheduler để chạy command trên mỗi phút.

### 4. Chạy Queue Worker
Trong môi trường production, chạy queue worker:
```bash
php artisan queue:work --daemon --tries=3
```

## 🧪 Test Scheduler

### Kiểm tra danh sách schedule:
```bash
php artisan schedule:list
```

### Test chạy thủ công:
```bash
# Chạy tất cả scheduled commands
php artisan schedule:run

# Hoặc dispatch job trực tiếp
php artisan tinker
>>> App\Jobs\CalculateEventPointsJob::dispatch();
```

### Test với queue:
```bash
# Terminal 1: Chạy queue worker
php artisan queue:work

# Terminal 2: Dispatch job
php artisan tinker
>>> App\Jobs\CalculateEventPointsJob::dispatch();
```

## 📊 Kiểm tra logs

### Xem log Laravel:
```bash
tail -f storage/logs/laravel.log
```

### Các log messages sẽ xuất hiện:
```
[timestamp] local.INFO: Bắt đầu thực thi CalculateEventPointsJob
[timestamp] local.INFO: Tìm thấy X sự kiện đã kết thúc cần xử lý
[timestamp] local.INFO: Đang xử lý sự kiện: event_id - Event Title
[timestamp] local.INFO: Tìm thấy X người dùng vắng mặt cho sự kiện event_id
[timestamp] local.INFO: Đã trừ 7 điểm từ người dùng user_id (email)
[timestamp] local.INFO: Đã xử lý X người dùng cho sự kiện event_id
[timestamp] local.INFO: CalculateEventPointsJob hoàn thành thành công
```

## 🔧 Production Setup với Supervisor (khuyến nghị)

### Cài đặt Supervisor (Ubuntu/Debian):
```bash
sudo apt-get install supervisor
```

### Tạo config file `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/BE_QuanLySuKien_HoiThao/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/BE_QuanLySuKien_HoiThao/storage/logs/worker.log
stopwaitsecs=3600
```

### Khởi động Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### Kiểm tra status:
```bash
sudo supervisorctl status
```

## ⏰ Thay đổi tần suất chạy (nếu cần)

Trong file `routes/console.php`, bạn có thể thay đổi:

```php
// Các tùy chọn khác:
->hourly()              // Mỗi giờ
->everyThirtyMinutes()  // Mỗi 30 phút
->everyTwoHours()       // Mỗi 2 giờ (hiện tại)
->everyFourHours()      // Mỗi 4 giờ
->daily()               // Mỗi ngày lúc 00:00
->dailyAt('02:00')      // Mỗi ngày lúc 02:00
->twiceDaily(1, 13)     // 2 lần/ngày: 01:00 và 13:00
```

## 🐛 Troubleshooting

### Scheduler không chạy:
1. Kiểm tra cron job đã setup chưa: `crontab -l`
2. Kiểm tra log: `tail -f storage/logs/laravel.log`
3. Chạy thử thủ công: `php artisan schedule:run`

### Queue worker không xử lý:
1. Kiểm tra worker đang chạy: `ps aux | grep queue:work`
2. Restart worker: `php artisan queue:restart`
3. Kiểm tra failed jobs: `php artisan queue:failed`

### Job bị lỗi:
1. Xem failed jobs: `php artisan queue:failed`
2. Retry job: `php artisan queue:retry [job-id]`
3. Retry tất cả: `php artisan queue:retry all`

## 📈 Monitoring

### Xem job đã chạy trong HistoryPoints:
```php
use App\Models\HistoryPoints;

// Lấy tất cả NO_SHOW records hôm nay
HistoryPoints::where('action_type', 'NO_SHOW')
    ->whereDate('created_at', today())
    ->get();
```

### Kiểm tra số lượng users bị trừ điểm:
```php
use App\Models\User;

// Users có điểm thay đổi gần đây
User::whereHas('historyPoints', function($q) {
    $q->where('action_type', 'NO_SHOW')
      ->whereDate('created_at', today());
})->get();
```

## ✨ Lưu ý quan trọng

1. **Cron phải chạy mỗi phút** (`* * * * *`) - Laravel scheduler sẽ tự quyết định job nào chạy
2. **Queue worker** phải luôn chạy trong production (dùng Supervisor)
3. Job sẽ **tự động bỏ qua** users đã bị trừ điểm (check HistoryPoints)
4. Điểm không bao giờ âm (có `max(0, score - 7)`)
5. Logs được ghi bằng tiếng Việt để dễ theo dõi

## 🚀 Quick Start (Development)

```bash
# 1. Chạy queue worker (Terminal 1)
php artisan queue:work

# 2. Test scheduler (Terminal 2)
php artisan schedule:run

# Hoặc dispatch trực tiếp
php artisan tinker
>>> App\Jobs\CalculateEventPointsJob::dispatch();

# 3. Xem logs
tail -f storage/logs/laravel.log
```

Done! 🎉
