<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'owner_id',
        'start_date',
        'due_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date'   => 'date',
        'deleted_at' => 'datetime',
    ];

    // Relationships

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    // Helper methods

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function archive(): void
    {
        $this->status = 'archived';
        $this->save();
    }

    public function restoreFromArchive(): void
    {
        $this->status = 'active';
        $this->save();
    }

    public function taskCount(): int
    {
        return $this->tasks()->count();
    }

    public function completedTaskCount(): int
    {
        return $this->tasks()->where('status', 'done')->count();
    }

    public function progressPercentage(): float
    {
        $total = $this->taskCount();
        if ($total === 0) return 0;
        return round(($this->completedTaskCount() / $total) * 100, 2);
    }
}
