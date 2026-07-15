<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function getUsersForCompany($companyId)
    {
        return User::with('roles')
            ->where('company_id', $companyId)
            ->paginate(15);
    }
}
