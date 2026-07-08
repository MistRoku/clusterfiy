<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
        protected $fillable = [
        'task_id',
        'user_id',
        'started_at',
        'ended_at',
        'duration_hours',
        'description',
    ];

    protected $casts = [
        'started_at'    => 'datetime',
        'ended_at'      => 'datetime',
        'duration_hours' => 'decimal:2',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper: calculate duration if not set
    public function calculateDuration()
    {
        if ($this->started_at && $this->ended_at) {
            $this->duration_hours = $this->started_at->diffInHours($this->ended_at, true);
            $this->saveQuietly();
        }
    }

    // Scope: currently running entries
    public function scopeRunning($query)
    {
        return $query->whereNull('ended_at');
    }

    // Scope: entries for a given user
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
