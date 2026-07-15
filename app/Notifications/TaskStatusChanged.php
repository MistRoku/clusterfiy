<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Task;

class TaskStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Task $task, protected string $oldStatus)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Task Status Updated: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line("Task '{$this->task->title}' status changed from {$this->oldStatus} to {$this->task->status}")
            ->action('View Task', route('tasks.show', $this->task))
            ->line('Thank you for using Clusterfiy!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

        public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'message' => "Status changed from {$this->oldStatus} to {$this->task->status}",
            'url' => route('tasks.show', $this->task),
            'icon' => 'refresh',
        ];
    }
}
