<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskAssignee extends Model
{
    protected $table = 'task_assignees';

    protected $fillable = [
        'task_id',
        'user_id',
        'assigned_by',
        'assigned_at',
        'notified_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
