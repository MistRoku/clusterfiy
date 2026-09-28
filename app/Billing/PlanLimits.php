<?php

namespace App\Billing;

use App\Models\Company;
use App\Models\User;

/**
 * Single place where plan limits are enforced. Controllers, middleware
 * and policies all delegate here so billing is real, not decoration.
 *
 * Free: 1 company per user, 5 members per company, no Excel exports.
 * Team: unlimited companies/members, exports enabled.
 */
class PlanLimits
{
    public static function memberLimit(Company $company): ?int
    {
        if ($company->teamFeaturesActive()) {
            return null;
        }
        return config('plans.free.max_members_per_company');
    }

    public static function canAddMember(Company $company): bool
    {
        $limit = self::memberLimit($company);
        return $limit === null || $company->memberCount() < $limit;
    }

    public static function memberDeficit(Company $company): int
    {
        $limit = self::memberLimit($company);
        if ($limit === null) {
            return 0;
        }
        return max(0, $company->memberCount() - $limit);
    }

    /** Over-limit companies keep their data but cannot add until compliant. */
    public static function isOverLimit(Company $company): bool
    {
        return self::memberDeficit($company) > 0;
    }

    public static function addMemberBlockedMessage(Company $company): string
    {
        $over = self::memberDeficit($company);
        if ($company->onTeamPlan() && $company->subscription_status === 'past_due') {
            return 'Payment failed. Update billing to keep adding members.';
        }
        if ($over > 0) {
            return "This company is {$over} member(s) over the Free plan limit (5). Remove members or upgrade to Team to add more.";
        }
        return 'Member limit for the Free plan reached (5). Upgrade to Team for unlimited members.';
    }

    public static function companyLimitFor(User $user): ?int
    {
        if (self::userHasTeamFeatures($user)) {
            return null;
        }
        return config('plans.free.max_companies_per_user', 1);
    }

    public static function userCompanyCount(User $user): int
    {
        $ids = $user->companies()->pluck('companies.id')
            ->merge($user->memberships()->pluck('company_id'))
            ->merge(Company::withoutGlobalScopes()->where('created_by', $user->id)->pluck('id'));
        if ($user->company_id) {
            $ids->push($user->company_id);
        }
        return $ids->unique()->count();
    }

    public static function canCreateCompany(User $user): bool
    {
        $limit = self::companyLimitFor($user);
        return $limit === null || self::userCompanyCount($user) < $limit;
    }

    public static function userHasTeamFeatures(User $user): bool
    {
        $ids = $user->companies()->pluck('companies.id')
            ->merge($user->memberships()->pluck('company_id'));
        if ($user->company_id) {
            $ids->push($user->company_id);
        }
        foreach ($ids->unique() as $id) {
            $company = Company::withoutGlobalScopes()->find($id);
            if ($company && $company->teamFeaturesActive()) {
                return true;
            }
        }
        return false;
    }

    public static function canExport(Company $company): bool
    {
        return $company->teamFeaturesActive();
    }
}
