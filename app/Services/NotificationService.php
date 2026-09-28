<?php

namespace App\Services;

use App\Models\User;
use App\Support\CompanyContext;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function notifyCompany(?int $companyId, $notification, $except = null): void
    {
        $companyId ??= CompanyContext::get();
        $query = User::where('company_id', $companyId);
        if ($except) {
            $query->where('id', '!=', $except);
        }
        Notification::send($query->get(), $notification);
    }

    public function notifyUsers($userIds, $notification): void
    {
        $users = User::whereIn('id', (array) $userIds)->get();
        Notification::send($users, $notification);
    }
}
