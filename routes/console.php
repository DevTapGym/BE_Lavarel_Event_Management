<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\CalculateEventPointsJob;
use App\Jobs\CheckUserReputationJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
//php artisan schedule:work chạy lịch trình đã định nghĩa bên dưới

//php artisan schedule:run chạy tất cả các công việc đã lên lịch ngay lập tức

// 🧩 Chạy CalculateEventPointsJob mỗi phút
Schedule::job(new CalculateEventPointsJob())
    ->everyMinute()
    ->name('calculate-event-points')
    ->withoutOverlapping()
    ->onOneServer();

// 🧩 Chạy CheckUserReputationJob mỗi phút
Schedule::job(new CheckUserReputationJob())
    ->everyMinute()
    ->name('check-user-reputation')
    ->withoutOverlapping()
    ->onOneServer();

// // Lên lịch chạy CalculateEventPointsJob mỗi 2 tiếng
// Schedule::job(new CalculateEventPointsJob())
//     ->everyTwoHours()
//     ->name('calculate-event-points')
//     ->withoutOverlapping()
//     ->onOneServer();

// // Lên lịch chạy CheckUserReputationJob mỗi ngày lúc 8h sáng
// Schedule::job(new CheckUserReputationJob())
//     ->dailyAt('08:00')
//     ->name('check-user-reputation')
//     ->withoutOverlapping()
//     ->onOneServer();
