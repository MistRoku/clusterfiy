<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\TenantScope;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
        'is_master_admin',
        'company_id',
        'timezone',
        'avatar',
        'last_login_at',
        'last_login_ip',
        'last_login_device',
        'failed_login_attempts',
        'locked_until',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_super_admin' => 'boolean',
        'is_master_admin' => 'boolean',
        'locked_until' => 'datetime',
        'last_login_at' => 'datetime',
        'two_factor_secret' => 'encrypted',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function tasksCreated()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function tasksAssigned()
    {
        return $this->belongsToMany(Task::class, 'task_assignees');
    }

    public function departmentsManaged()
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function loginHistory()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function isMasterAdmin(): bool
    {
        return (bool) $this->is_master_admin;
    }

    public function isCompanyAdmin(): bool
    {
        return $this->hasRole('company_admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    public function getCurrentCompanyAttribute()
    {
        if ($this->isSuperAdmin() && session('current_company_id')) {
            return Company::whereKey(session('current_company_id'))->first();
        }
        return $this->company;
    }

    public function defaultCompany()
    {
        return $this->company ?: $this->companies()->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
