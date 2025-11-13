<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Paper extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'papers';

    protected $fillable = [
        'title',
        'abstract',
        'author',
        'event_id',
        'file_url',
        'view',
        'download',
        'category',
        'language',
        'keywords',
    ];

    protected $casts = [
        'view' => 'integer',
        'download' => 'integer',
    ];

    protected $attributes = [
        'view' => 0,
        'download' => 0,
        'author' => []
    ];

    public $timestamps = true;

    /**
     * Get the event that owns the paper
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', '_id');
    }
}
