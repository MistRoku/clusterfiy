<?php

namespace App\Repositories;

use App\Models\Company;

class CompanyRepository
{
    public function adminList()
    {
        return Company::withCount(['users', 'tasks'])
            ->with('creator')
            ->paginate(15);
    }
}