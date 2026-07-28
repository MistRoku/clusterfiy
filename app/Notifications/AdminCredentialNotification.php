<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminCredentialsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $plainPassword,
        public string $loginUrl
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Clusterfiy Admin Account')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your company has been set up on Clusterfiy.')
            ->line('Your temporary password is: **' . $this->plainPassword . '**')
            ->action('Login to Clusterfiy', $this->loginUrl)
            ->line('Please change your password after your first login.')
            ->line('This password will expire in 24 hours if not used.');
    }
}
