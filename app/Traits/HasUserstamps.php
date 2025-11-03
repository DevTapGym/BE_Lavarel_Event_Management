<?php


namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

trait HasUserstamps
{
    public static function bootHasUserstamps()
    {
        static::creating(function ($model) {
            if (static::hasUserstampsColumns($model)) {
                $userId = Auth::id();
                if ($userId) {
                    $model->created_by = $userId;
                    $model->updated_by = $userId;
                }
            }
        });

        static::updating(function ($model) {
            if (static::hasUserstampsColumns($model)) {
                $userId = Auth::id();
                if ($userId) {
                    $model->updated_by = $userId;
                }
            }
        });
    }

    private static function hasUserstampsColumns($model): bool
    {
        return Schema::hasColumn($model->getTable(), 'created_by') &&
            Schema::hasColumn($model->getTable(), 'updated_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
