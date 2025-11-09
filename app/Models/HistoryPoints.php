<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HistoryPoints extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'history_points';

    protected $fillable = [
        'user_id',
        'event_id',
        'old_point',
        'new_point',
        'change_amount',
        'action_type',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'user_id' => 'string',
        'event_id' => 'string',
        'old_point' => 'integer',
        'new_point' => 'integer',
        'change_amount' => 'integer',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, '_id', 'user_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, '_id', 'event_id');
    }

    public static function logChange(
        string $userId,
        string $eventId,
        int $oldPoint,
        int $newPoint,
        string $actionType,
        ?string $reason = null,
    ): self {
        return self::create([
            'user_id' => $userId,
            'event_id' => $eventId,
            'old_point' => $oldPoint,
            'new_point' => $newPoint,
            'change_amount' => $newPoint - $oldPoint,
            'action_type' => $actionType,
            'reason' => $reason,
            'created_at' => now(),
        ]);
    }
}
