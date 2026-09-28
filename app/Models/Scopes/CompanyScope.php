<?php

namespace App\Models\Scopes;

use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Automatically scopes tenant-owned models to the active company.
 * Bypass with ->withoutGlobalScope(CompanyScope::class) for
 * super-admin platform views and the company switch endpoint.
 */
class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = CompanyContext::get();

        if ($companyId) {
            $builder->where($model->getTable() . '.company_id', $companyId);
        }
    }
}
