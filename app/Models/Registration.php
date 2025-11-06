<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

class Registration extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'registrations';

    protected $fillable = [
        'user_id',
        'event_id',
        'registration_at',
        'cancelled_at',
        'queue_order',
        'status_history',
        'cancel_reason',
    ];

    protected $casts = [
        'registration_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $appends = [
        'current_status',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /* ---------------- STATUS METHODS ---------------- */

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
}
