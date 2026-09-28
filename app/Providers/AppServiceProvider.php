<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Task;
use App\Policies\TaskPolicy;
use App\Models\Company;
use App\Policies\CompanyPolicy;
use App\Models\Department;
use App\Policies\DepartmentPolicy;
use App\Models\CompanyInvitation;
use App\Policies\CompanyInvitationPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(CompanyInvitation::class, CompanyInvitationPolicy::class);
    }
}
