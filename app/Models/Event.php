<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;


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
        'status_history', //'UPCOMING', 'OPEN', 'ONGOING', 'ENDED', 'CANCELLED'
        'image_url',
        'approval_history', //WAITING', 'APPROVED', 'REJECTED'
        'current_confirmed',
        'current_waiting',
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

        $this->addStatus($nextStatus);
        return $nextStatus;
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

        if (!in_array($status, $allowed)) {
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
