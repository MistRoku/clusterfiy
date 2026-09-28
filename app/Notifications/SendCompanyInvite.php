<?php

namespace App\Notifications;

use App\Models\CompanyInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendCompanyInvite extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public CompanyInvitation $invitation)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You are invited to join ' . $this->invitation->company->name . ' on Clusterfiy')
            ->greeting('Hello')
            ->line('You have been invited to join ' . $this->invitation->company->name . ' as ' . $this->invitation->role . '.')
            ->line('This invitation expires on ' . $this->invitation->expires_at->format('Y-m-d H:i') . '.')
            ->action('Accept invitation', url('/invitations/accept/' . $this->invitation->token))
            ->line('If you do not have an account yet, register first with this email address, then accept.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'company' => $this->invitation->company->name,
            'message' => 'Invitation to join ' . $this->invitation->company->name,
            'url' => url('/invitations/accept/' . $this->invitation->token),
            'icon' => 'mail',
        ];
    }

    public function toDatabase($notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
