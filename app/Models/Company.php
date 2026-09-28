<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'subdomain',
        'domain',
        'description',
        'logo',
        'timezone',
        'is_active',
        'plan',
        'stripe_customer_id',
        'stripe_subscription_id',
        'subscription_status',
        'billing_email',
        'grace_until',
        'settings',
        'created_by',
        'trial_ends_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'grace_until' => 'datetime',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withPivot('role', 'invited_by', 'accepted_at')
            ->withTimestamps();
    }

    public function memberships()
    {
        return $this->hasMany(CompanyUser::class, 'company_id');
    }

    public function invitations()
    {
        return $this->hasMany(CompanyInvitation::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function subscriptionActive(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function onTeamPlan(): bool
    {
        return $this->plan === 'team';
    }

    /**
     * Team features stay on while the subscription is active, trialing,
     * or past-due inside its grace window. Everything else falls back
     * to free-plan enforcement.
     */
    public function teamFeaturesActive(): bool
    {
        if (! $this->onTeamPlan()) {
            return false;
        }
        if (in_array($this->subscription_status, ['active', 'trialing'], true)) {
            return true;
        }
        return $this->subscription_status === 'past_due'
            && $this->grace_until
            && $this->grace_until->isFuture();
    }

    /** Distinct member count: direct company_id users + pivot members. */
    public function memberCount(): int
    {
        $direct = $this->users()->pluck('users.id');
        $pivot = $this->memberships()->pluck('user_id');
        return $direct->merge($pivot)->unique()->count();
    }
}
