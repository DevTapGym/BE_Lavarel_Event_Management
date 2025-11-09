<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\CalculateEventPointsJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Lên lịch chạy CalculateEventPointsJob mỗi 2 tiếng
Schedule::job(new CalculateEventPointsJob())
    ->everyTwoHours()
    ->name('calculate-event-points')
    ->withoutOverlapping()
    ->onOneServer();
