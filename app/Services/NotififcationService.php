<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function notifyCompany($companyId, $notification, $except = null)
    {
        $query = User::where('company_id', $companyId);
        if ($except) {
            $query->where('id', '!=', $except);
        }
        Notification::send($query->get(), $notification, [], null);
    }

    public function notifyUsers($userIds, $notification)
    {
        $users = User::whereIn('id', $userIds)->get();
        Notification::send($users, $notification, [], null);
    }
}
