<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskStatusChange extends Model
{
    protected $fillable = [
        'task_id',
        'from_status',
        'to_status',
        'changed_by',
        'comment',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Accessor for readable status
    public function getFromStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->from_status ?? 'Start'));
    }

    public function getToStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->to_status));
    }
}
