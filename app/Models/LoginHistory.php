<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
        protected $table = 'login_history';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device',
        'successful',
        'login_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'login_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('successful', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('successful', false);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('login_at', 'desc')->limit($limit);
    }

    // Helper: get device without the full user agent
    public function getShortDeviceAttribute(): string
    {
        return $this->device ?? 'Unknown';
    }
}
