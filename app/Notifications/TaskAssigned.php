<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Task $task)
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
            ->subject('New Task Assigned: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line('You have been assigned a new task:')
            ->line('**' . $this->task->title . '**')
            ->line('Priority: ' . ucfirst($this->task->priority))
            ->line('Due: ' . ($this->task->due_date ? $this->task->due_date->format('Y-m-d') : 'No due date'))
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
            'message' => 'You have been assigned: ' . $this->task->title,
            'url' => route('tasks.show', $this->task),
            'icon' => 'tasks',
        ];
    }
}
