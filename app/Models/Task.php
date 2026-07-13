<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Task extends Model
{
    use SoftDeletes;

    const STATUSES = ['todo', 'in_progress', 'in_review', 'blocked', 'done'];
    const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    protected $fillable = [
        'company_id',
        'department_id',
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'created_by',
        'assigned_to',
        'due_date',
        'due_time',
        'estimated_hours',
        'actual_hours',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'due_time' => 'datetime:H:i',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);

        static::updated(function ($task) {
            if ($task->isDirty('status')) {
                TaskStatusChange::create([
                    'task_id' => $task->id,
                    'from_status' => $task->getOriginal('status'),
                    'to_status' => $task->status,
                    'changed_by' => auth()->id(),
                ]);
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_assignees')
            ->withPivot('assigned_by', 'assigned_at', 'notified_at')
            ->withTimestamps();
    }

    public function statusChanges()
    {
        return $this->hasMany(TaskStatusChange::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'done';
    }

    public function totalLoggedHours(): float
    {
        return $this->timeEntries()->sum('duration_hours') ?? 0;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'todo' => 'neutral',
            'in_progress' => 'primary',
            'in_review' => 'secondary',
            'blocked' => 'error',
            'done' => 'success',
            default => 'neutral',
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'error',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'ghost',
            default => 'ghost',
        };
    }
}
