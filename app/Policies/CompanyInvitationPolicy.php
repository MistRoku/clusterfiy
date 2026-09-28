<?php

namespace App\Policies;

use App\Models\CompanyInvitation;
use App\Models\User;

class CompanyInvitationPolicy
{
    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $user->hasRole(['company_admin', 'manager'])
            || in_array($user->companyRole((int) session('current_company_id')), ['owner', 'manager'], true);
    }

    public function delete(User $user, CompanyInvitation $invitation): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return (int) session('current_company_id') === (int) $invitation->company_id
            && ($user->hasRole(['company_admin', 'manager']));
    }
}
