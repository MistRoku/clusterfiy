<?php

namespace App\Support;

/**
 * Holds the active company id for the current request.
 * Populated by SetCurrentCompany middleware from session / membership.
 */
class CompanyContext
{
    protected static ?int $companyId = null;

    public static function set(?int $id): void
    {
        static::$companyId = $id;
    }

    public static function get(): ?int
    {
        if (static::$companyId !== null) {
            return static::$companyId;
        }

        try {
            $id = session('current_company_id');
            return $id ? (int) $id : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function forget(): void
    {
        static::$companyId = null;
    }
}
