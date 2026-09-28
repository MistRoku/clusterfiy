<?php

namespace App\Notifications;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MemberAddedToCompany extends Notification
{
    use Queueable;

    public function __construct(public Company $company, public string $role)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'company' => $this->company->name,
            'role' => $this->role,
            'message' => 'You were added to ' . $this->company->name . ' as ' . $this->role,
            'url' => route('dashboard'),
            'icon' => 'users',
        ];
    }

    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
