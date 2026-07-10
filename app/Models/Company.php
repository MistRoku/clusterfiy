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
        'settings',
        'created_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

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

    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'subdomain' => 'required|string|max:255|unique:companies,subdomain',
        'domain' => 'nullable|string|max:255|unique:companies,domain',
        'description' => 'nullable|string',
        'logo' => 'nullable|image|max:2048',
        'timezone' => 'required|string|max:255',
        'is_active' => 'boolean',
        'settings' => 'nullable|array',
    ]);
    $validated['created_by'] = Auth::id();
    $company = Company::create($validated);

    // Create a company admin user
    $adminEmail = 'admin@' . $company->subdomain . '.com';
    $admin = User::create([
        'name' => $company->name . ' Admin',
        'email' => $adminEmail,
        'password' => bcrypt('password'),
        'company_id' => $company->id,
        'is_master_admin' => true,
    ]);
    $admin->assignRole('company_admin');

    // Optionally, assign the current user as a member too? Not needed.

    return redirect()->route('companies.index')->with('success', 'Company created. Company admin: ' . $adminEmail . ' / password');
}
}
