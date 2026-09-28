<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyInvitation extends Model
{
    protected $fillable = [
        'company_id', 'email', 'role', 'token', 'invited_by', 'expires_at', 'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return ! is_null($this->accepted_at);
    }

    public static function validRoles(): array
    {
        return ['owner', 'manager', 'member'];
    }
}
