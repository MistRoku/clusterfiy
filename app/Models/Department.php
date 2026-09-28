<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\CompanyScope;
use App\Support\CompanyContext;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = ['company_id', 'name', 'description', 'manager_id', 'is_active'];

    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function (Department $dept) {
            if (! $dept->company_id) {
                $dept->company_id = CompanyContext::get();
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
