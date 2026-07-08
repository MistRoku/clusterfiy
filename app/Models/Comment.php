<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'user_id',
        'body',
        'parent_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Polymorphic relationship
    public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Parent comment (for nested replies)
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // Child replies
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    // Helper
    public function isReply(): bool
    {
        return !is_null($this->parent_id);
    }

    public function getExcerptAttribute(int $length = 100): string
    {
        return str()->limit($this->body, $length);
    }
}
