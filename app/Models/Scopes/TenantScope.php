<?php

namespace App\Models\Scopes;

/**
 * Backwards-compatibility alias. Existing models reference TenantScope;
 * it now extends the canonical CompanyScope so both names work.
 */
class TenantScope extends CompanyScope
{
}
